<?php
/**
 * @FileName    :   Report.php
 * @QQ          :   224156865
 * @date        :   2014/02/27 16:11:36
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Model_Report extends Db_Model {
    /**
      * 获得给定adver的指定日期内的income数据
      * params fdate, edate, advId
      * return array
      * Mingshi
      * 2014-02-27
     */
    public function __construct() {
        parent::__construct('income_report', 'ad_report');
    }

    /**
     * $flag = 0 no group_by
     * $flag = 1 group_by date
     * $flag = 2 group_by order id
     * $flag = 3 group_by date orderId
     */
    public function get_total_price($orders = "", $flag = 0, $all = FALSE, $fdate = "", $edate = "") {
        if (!$orders) {
            return array();
        }

        if (!$fdate) {
            $fdate = date('Y-m-d', strtotime('-7 day'));
        }

        if (!$edate) {
            $edate = date('Y-m-d', strtotime('-1 day'));
        }

        $m = M('order_consume', 'ad_report');
        if ($all) {
            $where = array(
                'order_id'  =>  array('IN'  =>  "&/($orders)"),
            );
        } else {
            $where = array(
                'order_id'  =>  array('IN'  =>  "&/($orders)"),
                'date'  =>  array(
                    '>='    =>  $fdate,
                    '<='    =>  $edate,
                    Db_Sql::LOGIC => 'AND',
                )
            );
        }

        $attrs = array();
        if ($flag == 1) {
            $attrs = array(
                'select'    =>  'sum(consume) as consume, date',
                'group_by'  =>  'date',
            );
        } else if ($flag == 0){
            $attrs = array(
                'select'    =>  'sum(consume) as consume',
            );
        } else if ($flag == 2) {
            $attrs = array(
                'select'    =>  'sum(consume) as consume, order_id',
                'group_by'  =>  'order_id',
            );
        } else if ($flag == 3) {
            $attrs = array(
                'select'    =>  'sum(consume) as consume, date, order_id',
                'group_by'  =>  'date, order_id',
            );
        }

        $return = array();

        $return = $m->select($where, $attrs);

        return $return;
    }


    public function get_income_info($advId, $fdate = "", $edate = "") {
        if (!$fdate) {
            $fdate = date('Y-m-d', strtotime('-7 day'));
        }

        if (!$edate) {
            $edate = date('Y-m-d', strtotime('-1 day'));
        }

        $oids = self::get_adver_order($advId);


        if (empty($oids)) return array();
        
        $orderIds = array_get_column($oids, 'id');
        $idsStr = implode(',', $orderIds);
        # 获取展现次数(request) 点击次数(click) 点击率(click / request) 平均点击价格(income / click) 千次展现价格(income / request * 1000) 消费(income)

        $m = M('income_report', 'ad_report');
        $where = array(
            'date'      =>  array(
                '>='    =>  $fdate,
                '<='    =>  $edate,
                Db_Sql::LOGIC => 'AND',
            ),  
            'order_id'  =>  array('in' => "&/($idsStr)"),
        );
        $attrs = array(
            'select'    =>  'sum(request) as request, sum(click) as click, sum(income) as income'
        );
    
        $res = $m->selectOne($where, $attrs); 
        
        $return = array();

        $consume = $this->get_total_price($idsStr, 0, FALSE, $fdate, $edate);
        $return = array(
            'request'           =>  $res["request"],
            'click'             =>  $res["click"],
            'income'            =>  isset($consume[0]['consume']) ? $consume[0]['consume'] : 0,
            'rateClick'         =>  $res["request"] ? round($res["click"] / $res["request"] * 100, 2) . '%' : "-",
            'avgClickPrice'     =>  ($res["click"] && isset($consume[0]['consume']) && intval(isset($consume[0]['consume']) != 0)) ? round($consume[0]['consume'] / $res["click"], 2) : "-",
            'thousandResPrice'  =>  ($res["request"] && isset($consume[0]['consume']) && intval(isset($consume[0]['consume']) != 0)) ? round($consume[0]['consume'] / $res["request"] * 1000, 2) : "-",
        );

        return $return;
    }

    /**
      * 把income信息按照天拆分
      * params $fdate, $edate, $advId
      * return array
      * Mingshi
      * 2014-02-27
     */
    public function get_income_info_by_day($advId, $fdate = "", $edate = "") {
        if (!$fdate) {
            $fdate = date('Y-m-d', strtotime('-7 day'));
        }

        if (!$edate) {
            $edate = date('Y-m-d', strtotime('-1 day'));
        }

        $oids = self::get_adver_order($advId);

        if (empty($oids)) return array();

        $orderIds = array_get_column($oids, 'id');
        $idsStr = implode(',', $orderIds);

        $m = M('income_report', 'ad_report');
        $where = array(
            'date'      =>  array(
                '>='    =>  $fdate,
                '<='    =>  $edate,
                Db_Sql::LOGIC => 'AND',
            ),
            'order_id'  =>  array('in' => "&/($idsStr)"),
        );
        $attrs = array(
            'select'    =>  'sum(request) as request, sum(click) as click, sum(income) as income, date',
            'group_by'  =>  'date',
        );

        $res = $m->select($where, $attrs);
        
        $consume = $this->get_total_price($idsStr, 1, FALSE, $fdate, $edate);
        
        if (!empty($consume)) {
            array_change_key($consume, 'date');
        }

        $return = array();
        $tmp = array();
        // 重组数据
        if (!empty($res)) {
            foreach ($res as $value) {
                $tmp[$value['date']]['request']      =   $value['request'];
                $tmp[$value['date']]['click']        =   $value['click'];
                $tmp[$value['date']]['income']       =   isset($consume[$value['date']]['consume']) ? $consume[$value['date']]['consume'] : 0;
                $tmp[$value['date']]['rateClick']    =   $value['request'] ? round($value['click'] / $value['request'] * 100, 2) : '0';
                $tmp[$value['date']]['clickPrice']   =   ($value['click'] && isset($consume[$value['date']]['consume']) && intval($consume[$value['date']]['consume'] != 0)) ? round($consume[$value['date']]['consume'] / $value['click'], 2) : '0';
                $tmp[$value['date']]['requestPrice'] =   ($value['request'] && isset($consume[$value['date']]['consume']) && intval($consume[$value['date']]['consume'] != 0)) ? round($consume[$value['date']]['consume'] / $value['request'] * 1000, 2) : '0';
            }
        }

        for ($t = $fdate; $t <= $edate; $t = date('Y-m-d', (strtotime($t) + 86400))) {
            $return[$t]['request']      =   (isset($tmp[$t]) && $tmp[$t]['request']) ? $tmp[$t]['request'] : '0';
            $return[$t]['click']        =   (isset($tmp[$t]) && $tmp[$t]['click']) ? $tmp[$t]['click'] : '0';
            $return[$t]['income']       =   (isset($tmp[$t]) && $tmp[$t]['income']) ? $tmp[$t]['income'] : '0';
            $return[$t]['rateClick']    =   (isset($tmp[$t]) && $tmp[$t]['rateClick']) ? $tmp[$t]['rateClick'] : '0';
            $return[$t]['clickPrice']   =   (isset($tmp[$t]) && $tmp[$t]['clickPrice']) ? $tmp[$t]['clickPrice'] : '0';
            $return[$t]['requestPrice'] =   (isset($tmp[$t]) && $tmp[$t]['requestPrice']) ? $tmp[$t]['requestPrice'] : '0';
        }

        return $return;
    }


    /**
      * 获得给定adver的orders id
      * params advId integer
      * return array
      * Mingshi
      * 2014-02-27
     */
    private function get_adver_order($advId) {
        $advId = intval($advId);
        $return = array();
        $m = M('orders', 'ad_core');
        $return = $m->select(
            array(
                'advertiser_id' =>  $advId,
            ),
            array(
                'select'    =>  'id',
            )
        );

        return $return;
    }

    /**
      * 根据orderids获取request click income
      * @param array $oids
      * Mingshi
      * 2014-02-28
     */
    public function get_info_by_oids($oids = array()) {
        if (empty($oids)) return array();
        $idString = implode(',', $oids);

        $where = array(
            'order_id'  =>  array("IN"  =>  "&/($idString)"),
        );

        $attrs = array(
            'select'    =>  'sum(request) as request, sum(click) as click, sum(order_deal) as order_deal',
        );

        $res = $this->selectOne($where, $attrs);

        $consume = $this->get_total_price($idString, 0, TRUE);
        $res['income'] = $consume[0]['consume'];

        return $res;
    }
    
    /**
      * 根据orderids获取request click income 按照order_id group by
      * @param array $oids
      * Mingshi
      * 2014-02-28
     */
    public function get_info_by_oids_with_group($oids = array()) {
        if (empty($oids)) return array();
        $idString = implode(',', $oids);

        $where = array(
            'order_id'  =>  array("IN"  =>  "&/($idString)"),
        );

        $attrs = array(
            'select'    =>  'sum(request) as request, sum(click) as click, sum(order_deal) as order_deal, order_id',
            'group_by'  =>  'order_id',
        );
        
        $res = $this->select($where, $attrs);
        
        $consume = $this->get_total_price($idString, 2, TRUE);

        if (!empty($consume)) {
            array_change_key($consume, 'order_id');
        }

        $return = array();
        if (!empty($res)) {
            foreach ($res as $k => $val) {
                $return[$k]['request'] = $val['request'];
                $return[$k]['click'] = $val['click'];
                $return[$k]['order_deal'] = $val['order_deal'];
                $return[$k]['order_id'] = $val['order_id'];
                $return[$k]['income'] = isset($consume[$val['order_id']]['consume']) ? $consume[$val['order_id']]['consume'] : 0;
            }
        }

        return $return;
    }

    /**
      * 根据广告主id，推广计划和box获得相应统计数据
      * @param integet $advId, integer $cid, integer $bid
      * return array
      * Mingshi
      * 2014-03-02
     */
    public function get_report_info($advId, $cid = array(), $bid = array(), $fdate = "", $edate = "") {
        if (!$fdate) {
            $fdate = date('Y-m-d', strtotime('-7 day'));
        }

        if (!$edate) {
            $edate = date('Y-m-d', strtotime('-1 day'));
        }

        // 支持cid和bid的多选
        if (empty($cid) && empty($bid)) {
            return array();
        }

        $bidArr = array();
        if (!empty($cid)) {
            $boxes = F::$f->Model_Box_CampaignBox->get_box_by_cids($cid);
            
            foreach ($boxes as $_cid => $_bid) {
                foreach ($_bid as $_id) {
                    $bidArr[] = $_id;
                }
            }

            $orders = M('Report')->get_orders_by_advId($advId);
            $oids = array_get_column($orders, 'id');
            $bidArr = array_intersect($bidArr, $oids);
        }

        if (!empty($bid) && !empty($bidArr)) {
            $bidArr = array_intersect($bidArr, $bid);
        } else if (!empty($bid) && empty($bidArr)) {
            $bidArr = $bid;
        }

        $idString = "";

        $bidArr = array_unique($bidArr);

        if (!empty($bidArr)) {
            $idString = implode(',', $bidArr);
        } else {
            return array();
        }

        $where = array(
            'date'  =>  array(
                '>='    =>  $fdate,
                '<='    =>  $edate,
                Db_Sql::LOGIC   =>  'AND',
            ),
            'order_id'  =>  array('IN'  =>  "&/($idString)"),
        );
        $attrs = array(
            'select'    =>  'sum(request) as request, sum(click) as click, sum(income) as income, sum(order_deal) as order_deal, sum(order_total) as order_total, sum(order_money) as order_money, date',
            'group_by'  =>  'date',
        );
        
        $res = $this->select($where, $attrs);
        
        $return = array();

        $consume = $this->get_total_price($idString, 1, FALSE, $fdate, $edate);

        $tmpConsume = array();
        if (!empty($consume)) {
            array_change_key($consume, 'date');
        }
         
        array_change_key($res, 'date');
    
        for ($t = $fdate; $t <= $edate; $t = date('Y-m-d', (strtotime($t) + 86400))) {
            $return[$t]['request']          =   (isset($res[$t]['request'])) ? $res[$t]['request'] : 0;
            $return[$t]['click']           =   (isset($res[$t]['click'])) ? $res[$t]['click'] : 0;
            $return[$t]['income']          =   (isset($consume[$t]['consume'])) ? $consume[$t]['consume'] : 0;
            $return[$t]['order_deal']      =   (isset($res[$t]['order_deal'])) ? $res[$t]['order_deal'] : 0;
            $return[$t]['order_total']     =   (isset($res[$t]['order_total'])) ? $res[$t]['order_total'] : 0;
            $return[$t]['order_money']     =   (isset($res[$t]['order_money'])) ? $res[$t]['order_money'] : 0;
            
            $return[$t]['rateClick']        =   (isset($res[$t]['request']) && intval($res[$t]['request']) != 0 && isset($res[$t]['click'])) ? round($res[$t]['click'] / $res[$t]['request'] * 100, 2) : 0;

            $return[$t]['ecpm']             =   (isset($consume[$t]['consume']) && isset($res[$t]['request']) && intval($res[$t]['request']) != 0) ? round($consume[$t]['consume'] / $res[$t]['request'] * 1000, 2) : 0;

            $return[$t]['ecpc']             =   (isset($consume[$t]['consume']) && isset($res[$t]['click']) && intval($res[$t]['click']) != 0) ? round($consume[$t]['consume'] / $res[$t]['click'], 2) : 0;

            $return[$t]['eock']             =   (isset($res[$t]['order_deal']) && intval($res[$t]['order_deal']) != 0 && isset($res[$t]['click'])) ? round($res[$t]['click'] / $res[$t]['order_deal'], 2) : 0;

            $return[$t]['eoco']             =   (isset($res[$t]['order_deal']) && intval($res[$t]['order_deal']) != 0 && isset($consume[$t]['consume'])) ? round($consume[$t]['consume'] / $res[$t]['order_deal'], 2) : 0;
        }

        return $return;
    }


    /**
      * 获取广告主所有素材尺寸
      * @param integer $advId
      * return array
      * Mingshi
      * 2014-03-03
     */
    public function get_all_size($advId) {
        if (!intval($advId)) return array();

        //先获得该广告主名下的所有order
        $orders = M('orders', 'ad_core')->select(
            array(
                'advertiser_id' =>  intval($advId)
            )
        );

        $ordersIds = array_get_column($orders, 'id'); 
        if (empty($ordersIds)) return array();

        $idString = implode(',', $ordersIds);
        
        $m = new Db_Model('income_report as i RIGHT JOIN ad_core.slot as s ON s.id = i.slot_id', 'ad_report');
    
        $where = array(
            '&/i.order_id'  =>  array('IN' => "&/($idString)"),
        );
        $attrs = array(
            'select' => 's.width, s.height'
        );

        $return = array();
        
        $res = $m->select($where, $attrs);
        
        if (!empty($res)) {
            foreach ($res as $value) {
                $return[] = $value['width'] . "x" . $value['height'];
            }
            $return = array_unique($return);
        }

        return $return;

    }

    /**
      * 获取广告主的所有订单
      * @param integer $advId
      * return array
      * Mingshi
      * 2014-03-03
     */
    public function get_orders_by_advId($advId) {
        if (!intval($advId)) return array();

        return M('orders', 'ad_core')->select(
            array(
                'advertiser_id' =>  intval($advId)
            )
        );
    }

    /**
      * 获取尺寸报告
      * @param integer $advId
      * @param integer $cid
      * @param integer $bid
      * @param string $size
      * @param string $fdate
      * @param string $edate
     */
    public function get_size_report($advId, $cid = array(), $bid = array(), $size = "", $fdate = "", $edate = "") {
        if (empty($cid) && empty($bid)) {
            return array();
        }

        $bidArr = array();
        if (!empty($cid)) {
            $boxes = F::$f->Model_Box_CampaignBox->get_box_by_cids($cid);
            foreach ($boxes as $_cid => $_bid) {
                foreach ($_bid as $_id) {
                    $bidArr[] = $_id;
                }
            }

            $orders = M('Report')->get_orders_by_advId($advId);
            $oids = array_get_column($orders, 'id');
            $bidArr = array_intersect($bidArr, $oids);
        } 

        if (!empty($bid) && !empty($bidArr)) {
            $bidArr = array_intersect($bidArr, $bid);
        } else if (!empty($bid) && empty($bidArr)) {
            $bidArr = $bid;
        }

        $bidArr = array_unique($bidArr);

        $idString = "";
        if (!empty($bidArr)) {
            $idString = implode(',', $bidArr);
        } else {
            return array();
        }
        
        $sizes = array();
        if (!$size) {
            $all_size = $this->get_all_size(intval($advId));
            foreach ($all_size as $_size) {
                $sizeArr = explode('x', $_size);
                $tmp = array(
                    "width"     =>  $sizeArr[0],
                    "height"    =>  $sizeArr[1],
                );
                $sizes[] = $tmp;
            }
        } else {
            $sizeArr = explode('x', $size);
            $tmp = array(
                "width"     =>  $sizeArr[0],
                "height"    =>  $sizeArr[1],    
            );
            $sizes[] = $tmp;
        }
        
        if (!$fdate) {
            $fdate = date('Y-m-d', strtotime('-7 day'));
        }

        if (!$edate) {
            $edate = date('Y-m-d', strtotime('-1 day'));
        }
        
        # 查找order总价
        $ordersCost = $this->select(
            array(
                'date'  =>  array(
                    '>='    =>  $fdate,
                    '<='    =>  $edate,
                    Db_Sql::LOGIC => 'AND',
                ),
                'order_id'  =>  array('IN' => "&/($idString)")
            ),
            array(
                'select'    =>  'sum(income) as income, order_id', 
                'group_by'  =>  'order_id'
            )
        );
        
        $tmpOrderCost = array_rack2nd_keyvalue($ordersCost, 'order_id', 'income');

        $m = new Db_Model('income_report as i LEFT JOIN ad_core.slot as s ON s.id = i.slot_id', 'ad_report');
        
        $where = array(
            '&/i.date'  =>  array(
                '>='    =>  $fdate,
                '<='    =>  $edate,
                Db_Sql::LOGIC => 'AND',
            ),
            '&/i.order_id'  =>  array(
                'IN'    =>  "&/($idString)",
            ),
        );
        
        $sizeCondition = array();
        foreach ($sizes as $_size) {
            $tmpWhere = array(
                '&/s.width'   =>  $_size['width'],
                '&/s.height'  =>  $_size['height'],
                Db_Sql::LOGIC => 'AND',
            );
            $sizeCondition[] = $tmpWhere;
        }

        $sizeCondition[] = array(Db_Sql::LOGIC => 'OR');

        $where = array_merge(array($where), array($sizeCondition), array(Db_Sql::LOGIC => 'AND'));
        $attrs = array(
            'select'    =>  'i.request, i.click, i.income, s.width, s.height, i.order_id'
        );

        $return = array();
        
        $res = $m->select($where, $attrs);
        
        $tmpConsume = M('Report')->get_total_price($idString, 2, FALSE, $fdate, $edate);
        array_change_key($tmpConsume, 'order_id');

        $tmpSizeOrder = array();
        $tmpSizeCost = array();
        $tmpRateSize = array();
        if (!empty($res)) {
            foreach ($res as $v) {
                if (!isset($tmpSizeCost[$v['width'] . 'x' . $v['height']])) {
                    $tmpSizeCost[$v['width'] . 'x' . $v['height']] = 0;
                }

                $tmpSizeOrder[$v['width'] . 'x' . $v['height']] = $v['order_id'];
                $tmpSizeCost[$v['width'] . 'x' . $v['height']] += $v['income'];
            }
        }
        
        if (!empty($tmpSizeCost)) {
            foreach ($tmpSizeCost as $_size => $cost) {
                //$tmpRateSize[$_size] = (isset($tmpOrderCost[$tmpSizeOrder[$_size]]) && intval($tmpOrderCost[$tmpSizeOrder[$_size]]) != 0) ? round($cost / $tmpOrderCost[$tmpSizeOrder[$_size]], 2) : 0;
                $tmpRateSize[$_size] = (isset($tmpOrderCost[$tmpSizeOrder[$_size]]) && intval($tmpOrderCost[$tmpSizeOrder[$_size]]) != 0) ? $cost / $tmpOrderCost[$tmpSizeOrder[$_size]] : 0;
            }
        }
        
        if (!empty($res)) {
            $tmp = array();
            foreach ($res as $value) {
                if (!isset($tmp[$value['width'] . 'x' . $value['height']]['request'])) {
                    $tmp[$value['width'] . 'x' . $value['height']]['request'] = 0;
                }

                if (!isset($tmp[$value['width'] . 'x' . $value['height']]['click'])) {
                    $tmp[$value['width'] . 'x' . $value['height']]['click'] = 0;
                }

                if (!isset($tmp[$value['width'] . 'x' . $value['height']]['income'])) {
                    $tmp[$value['width'] . 'x' . $value['height']]['income'] = 0;
                }
                
                $_size = $value['width'] . 'x' . $value['height'];
                $tmp[$value['width'] . 'x' . $value['height']]['request'] += $value['request'];
                $tmp[$value['width'] . 'x' . $value['height']]['click'] += $value['click'];
                $tmp[$value['width'] . 'x' . $value['height']]['income'] = (isset($tmpConsume[$value['order_id']]['consume']) && isset($tmpRateSize[$_size])) ? round($tmpConsume[$value['order_id']]['consume'] * $tmpRateSize[$_size], 2) : 0;
            }
            
            foreach ($tmp as $_size => $value) {
                $return[$_size]['request'] = $value['request'];
                $return[$_size]['click'] = $value['click'];
                $return[$_size]['income'] = $value['income'];
                $return[$_size]['rateClick'] = $value['request'] != 0 ? round($value['click'] / $value['request'] * 100, 2) . '%' : 0;
                $return[$_size]['ecpm'] = $value['request'] != 0 ? round($value['income'] / $value['request'] * 1000, 2) : 0;
                $return[$_size]['ecpc'] = $value['click'] != 0 ? round($value['income'] / $value['click'], 2) : 0;
            }
        }

        return $return;
    }

    /**
      * 获取相应广告主的当月消费总额
      * @param $advId
      * return Integer 
      * Mingshi
      * 2014-03-04
     */
    public function get_month_cost_by_advId($advId) {
        if (!intval($advId)) return '0.00';
        
        # 先获取所有order ids 
        $orders = $this->get_orders_by_advId(intval($advId)); 
        $ids = array_get_column($orders, 'id');
        
        if (empty($ids)) {
            return 0;
        }

        $idString = implode(',', $ids);

        $year = date('Y', time());
        $month = date('m', time());
        $fdate = $year . "-" . $month . "-01";
        $edate = date('Y-m-d', time());

        $consume = $this->get_total_price($idString, 0, FALSE, $fdate, $edate);
        
        return $consume[0]['consume'] ? $consume[0]['consume'] : '0.00';
    }

    /**
      * 获取相应广告主的box包括状态
      * @param integer $advId
      * return array
      * Mingshi
      * 2014-03-04
     */
    public function get_orders_with_status($advId) {
        if (!intval($advId)) {
            return array();
        }

        # 先获得所有orders
        $orders = $this->get_orders_by_advId(intval($advId));
        $ids = array_get_column($orders, 'id');
        if (empty($ids)) {
            return array();
        }
        
        $status = M('Adorder')->get_order_status_by_oids($ids);

        $return = array();

        foreach ($orders as $_order) {
            if ($status[$_order['id']]['status']['state'] == Model_Box_Campaign::CAMPAIGN_ACTIVE) {
                $return['active'][] = $_order;
            } else if ($status[$_order['id']]['status']['state'] == Model_Box_Campaign::CAMPAIGN_PAUSE) {
                $return['pause'][] = $_order;
            }
        }
        
        return $return;
    }
}

