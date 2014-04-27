<?php
class Model_Box_Campaign extends Db_Model 
{
    const CAMPAIGN_ACTIVE = 1;
    const CAMPAIGN_PAUSE = 2;

    public static $CAMPAIGN_STATUS = array(
        self::CAMPAIGN_ACTIVE   =>  "有效",
        self::CAMPAIGN_PAUSE    =>  "暂停",
    );
        
    public function __construct()
    {
        parent::__construct('campaign', 'ad_box');
    }

    protected function beforeInsert(&$data)
    {
        if (!isset($data['create_time'])) {
            $data['create_time'] = '&/CURRENT_TIMESTAMP';
        }
    }
    
    /**
      * 根据广告主id获得所有的推广计划以及box信息
      * @param integer $advId
      * return array
      * mingshi
      * 2014-03-02
     */
    public function get_adver_campaigns($advId) {
        if (!intval($advId)) return array();

        $orders = M('Report')->get_orders_by_advId($advId);
        $bids = array_get_column($orders, 'id');
        $idString = implode(',', $bids);
        
        $m = new Db_Model('campaign as c LEFT JOIN campaign_box as b ON c.id = b.campaign_id', 'ad_box');
            
        $where = array(
            '&/c.advertiser_id' =>  intval($advId),
            '&/b.box_id'    =>  array('IN'  =>  "&/($idString)")
        );
        
        $attrs = array(
            'select'    =>  'c.id as cid, c.name, b.id as bid, b.box_id',
            'order_by'  =>  'c.last_update_time DESC'
        );

        $return = array();
        $res = $m->select($where, $attrs);
        
        if (!empty($res)) {
            foreach ($res as $value) {
                $return[$value['cid']]['campaign']['cid'] = $value['cid'];
                $return[$value['cid']]['campaign']['name'] = $value['name'];

                $box = array(
                    'box_id'    =>  $value['box_id'],
                    'id'        =>  $value['bid'],
                );

                $return[$value['cid']]['box'][] = $box;
            }
        }
        
        return $return;
    }

    /**
      * 获得相关广告主的推广计划概况
      * params $advId integer
      * return array
      * mingshi
      * 2014-02-28
     */
    public function get_adver_campaign($advId) {
        if (!intval($advId)) return array();
        $return = array();
        
        $orders = M('Report')->get_orders_by_advId($advId);
        $oids = array_get_column($orders, 'id');
        $oidString = implode(',', $oids);
    
        $m = new Db_Model('campaign as c LEFT JOIN campaign_box as b ON c.id = b.campaign_id', 'ad_box');
        $where = array(
            '&/c.advertiser_id'   =>  intval($advId),
            '&/b.box_id'    =>  array('IN' => "&/($oidString)")
        );
        $attrs = array(
            'select'    =>  'c.name, b.box_id, c.id',
            'order_by'  =>  'c.last_update_time DESC',
        );

        $campaigns = $m->select($where, $attrs);
        
        $newRes = array_rack2nd_keyvalue($campaigns, 'id', 'box_id', TRUE);
        $idName = array_rack2nd_keyvalue($campaigns, 'id', 'name') ;

        $tmpConsume = array();
        foreach ($newRes as $_cid => $bid) {
            $tmp = 0;
            foreach ($bid as $_id) {
                $consume = M('Report')->get_total_price($_id, 0, TRUE);
                $tmp += isset($consume[0]['consume']) ? $consume[0]['consume'] : 0;
            }
            $tmpConsume[$_cid] = $tmp;
        }

        #获得campaign id array
        $campaignArray = array_unique(array_get_column($campaigns, 'id'));
        $status = $this->get_campaign_status($campaignArray);

        $newCamp = array();
        foreach ($newRes as $cid => $value) {
            $incomeInfo                             =   M('Report')->get_info_by_oids($value);
            
            $newCamp[$cid]['name']                  =   isset($idName[$cid]) ? $idName[$cid] : "";
            $newCamp[$cid]['bids']                  =   $value;
            $newCamp[$cid]['status']                =   $status[$cid]['status'];
            $newCamp[$cid]['totalPrice']            =   M('Adorder')->get_total_price_by_oids($status[$cid]['activeOrders']);
            $newCamp[$cid]['request']               =   $incomeInfo['request'];
            $newCamp[$cid]['click']                 =   $incomeInfo['click'];
            $newCamp[$cid]['income']                =   isset($tmpConsume[$cid]) ? $tmpConsume[$cid] : 0;
            $newCamp[$cid]['rateClick']             =   $incomeInfo["request"] ? round($incomeInfo['click'] / $incomeInfo['request'] * 100, 2) . "%" : "-";
            $newCamp[$cid]['avgClickPrice']         =   ($incomeInfo['click'] && isset($tmpConsume[$cid])) ? round($tmpConsume[$cid] / $incomeInfo['click'], 2) : "-";
            $newCamp[$cid]['deal']                  =   $incomeInfo['order_deal'];
            $newCamp[$cid]['rateDeal']              =   $incomeInfo['click'] ? round($incomeInfo['order_deal'] / $incomeInfo['click'] * 100, 2) . "%" : "-";
            $newCamp[$cid]['avgDealPrice']          =   (intval($incomeInfo['order_deal']) && isset($tmpConsume[$cid])) ? round($tmpConsume[$cid] / $incomeInfo['order_deal'], 2) : "-";
            $newCamp[$cid]['thousandRequestPrice']  =   ($incomeInfo["request"] && isset($tmpConsume[$cid]))? round($tmpConsume[$cid] / $incomeInfo['request'] * 1000, 2) : "-";
        }

        return $newCamp;
    }

    /**
      * 获得推广计划是否有效或暂停
      * @param array $cids
      * return array
      * mingshi
      * 2014-02-28
     */
    private function get_campaign_status($cids = array()) {
        if (empty($cids)) return array();
        
        # 首先获得order ids
        $m = F::$f->Model_Box_CampaignBox;
        $boxs = $m->get_box_by_cids($cids);

        # 根据每一个campaign的orderids 获取其状态
        $return = array();
        foreach ($boxs as $cid => $box) {
            $return[$cid] = $this->get_status_by_orders($box);
        }

        return $return;
    }

    /**
      * 根据orders获取是否存在有效的投放
      * @param array $oids
      * mingshi
      * 2014-02-28
     */
    private function get_status_by_orders($oids = array()) {
        if (empty($oids)) return array();

        $idString = implode(',', $oids);
        $activeStatus = Model_Ad::get_active_status(); 

        $return = array();

        $where = array(
            'order_id'  =>  array('IN' => "&/($idString)"),
            'status'    =>  array('IN' => "&/($activeStatus)"),
        );

        $res = M('Ad')->select($where);
        $return['status']['state'] = empty($res) ? self::CAMPAIGN_PAUSE : self::CAMPAIGN_ACTIVE;
        $return['status']['des']   = empty($res) ? self::$CAMPAIGN_STATUS[self::CAMPAIGN_PAUSE] : self::$CAMPAIGN_STATUS[self::CAMPAIGN_ACTIVE];
        
        if (!empty($res)) {
            $return['activeOrders'] = array_get_column($res, 'order_id');
        } else {
            $return['activeOrders'] = array();
        }

        return $return;
    }

    /**
      * 根据广告主id获得campaign ids
      * @param integer $advId
      * mingshi
      * 2014-02-28
     */
    public function get_campaigns_by_advId($advId) {
        if (!intval($advId))  return array();
        return $this->select(
            array(
                'advertiser_id' =>  intval($advId),
            )
        );       
    }
}
