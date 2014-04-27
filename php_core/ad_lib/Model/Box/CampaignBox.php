<?php
class Model_Box_CampaignBox extends Db_Model 
{
    public function __construct()
    {
        parent::__construct('campaign_box', 'ad_box');
    }

    /**
      * 根据campaign id获得相应的box
      * @param array $cids
      * Mingshi
      * 2014-02-28
     */
    public function get_box_by_cids($cids) {
        if (empty($cids)) return array();
        
        $cidArr = implode(',', $cids);
        
        $where = array(
            'campaign_id'   =>  array('IN'  =>  "&/($cidArr)"),
        );

        $res = $this->select($where);
        $return = array();

        $return = array_rack2nd_keyvalue($res, 'campaign_id', 'box_id', TRUE);

        return $return;
    }

    /**
      * 根据广告主id获取box相应信息
      * @param integer $advId
      * Mingshi
      * 2014-02-28
     */
    public function get_boxs_by_advId($advId) {
        if (!intval($advId)) return array();

        $campaigns = F::$f->Model_Box_Campaign->get_campaigns_by_advId($advId);
        $camIds = array_get_column($campaigns, 'id');

        $boxs = $this->get_boxs_by_campaignIds($camIds); 
        
        $orders = array_get_column($boxs, 'box_id');

        $bids = array_get_column($boxs, 'box_id'); 
        $idString = implode(',', $bids);
        
        $consume = M('Report')->get_total_price($idString, 2, TRUE);

        if (!empty($consume)) {
            array_change_key($consume, 'order_id');
        }
         
        $ordersIncomeInfo = M('report')->get_info_by_oids_with_group($orders);

        array_change_key($ordersIncomeInfo, 'order_id');
        
        $totalPrice = M('Adorder')->get_total_price_by_oids_group($orders);
        $tPrice = array_rack2nd_keyvalue($totalPrice, 'id', 'total');

        $status = M('Adorder')->get_order_status_by_oids($orders);
        $boxOrder = F::$f->Model_Adorder->get_box_by_ids($orders);
        array_change_key($boxOrder, 'id');
        $finalBoxs = array();
        foreach ($boxs as $box) {
            $finalBoxs[$box['box_id']]['bid']                   =   $box['bid'];
            $finalBoxs[$box['box_id']]['cid']                   =   $box['cid'];
            $finalBoxs[$box['box_id']]['cname']                 =   $box['name'];
            $finalBoxs[$box['box_id']]['bname']                 =   $boxOrder[$box['box_id']]['name'];
            $finalBoxs[$box['box_id']]['end_time']              =   intval($boxOrder[$box['box_id']]['end_time']) ? $boxOrder[$box['box_id']]['end_time'] : '不限';
            $finalBoxs[$box['box_id']]['order_id']              =   $box['box_id'];
            $finalBoxs[$box['box_id']]['request']               =   isset($ordersIncomeInfo[$box['box_id']]) ? $ordersIncomeInfo[$box['box_id']]['request'] : 0;
            $finalBoxs[$box['box_id']]['click']                 =   isset($ordersIncomeInfo[$box['box_id']]) ? $ordersIncomeInfo[$box['box_id']]['click'] : 0;
            $finalBoxs[$box['box_id']]['income']                =   isset($consume[$box['box_id']]['consume']) ? $consume[$box['box_id']]['consume'] : 0; 
            $finalBoxs[$box['box_id']]['rateClick']             =   (isset($ordersIncomeInfo[$box['box_id']]['request']) && intval($ordersIncomeInfo[$box['box_id']]['request']) != 0 && isset($ordersIncomeInfo[$box['box_id']]['click'])) ? round($ordersIncomeInfo[$box['box_id']]['click'] / $ordersIncomeInfo[$box['box_id']]['request'] * 100, 2) . "%" : "-";
            $finalBoxs[$box['box_id']]['avgClickPrice']         =   (isset($ordersIncomeInfo[$box['box_id']]['click']) && intval($ordersIncomeInfo[$box['box_id']]['click']) != 0 && isset($consume[$box['box_id']]['consume'])) ? round($consume[$box['box_id']]['consume'] / $ordersIncomeInfo[$box['box_id']]['click'], 2) : "-";
            $finalBoxs[$box['box_id']]['thousandRequestPrice']  =   (isset($ordersIncomeInfo[$box['box_id']]['request']) && intval($ordersIncomeInfo[$box['box_id']]['request']) != 0 && isset($consume[$box['box_id']]['consume'])) ? round($consume[$box['box_id']]['consume'] / $ordersIncomeInfo[$box['box_id']]['request'] * 1000, 2) : "-";
            $finalBoxs[$box['box_id']]['order_deal']            =   isset($ordersIncomeInfo[$box['box_id']]['order_deal']) ? $ordersIncomeInfo[$box['box_id']]['order_deal'] : "-";
            $finalBoxs[$box['box_id']]['rateDeal']              =   (isset($ordersIncomeInfo[$box['box_id']]['click']) && intval($ordersIncomeInfo[$box['box_id']]['click']) != 0 && isset($ordersIncomeInfo[$box['box_id']]['order_deal'])) ? round($ordersIncomeInfo[$box['box_id']]['order_deal'] / $ordersIncomeInfo[$box['box_id']]['click'] * 100, 2) . "%" : "-";
            $finalBoxs[$box['box_id']]['avgDealPrice']          =   (isset($consume[$box['box_id']]['consume']) && isset($ordersIncomeInfo[$box['box_id']]['order_deal']) && intval($ordersIncomeInfo[$box['box_id']]['order_deal']) != 0) ? round($consume[$box['box_id']]['consume'] / intval($ordersIncomeInfo[$box['box_id']]['order_deal']), 2) : "-";
        
            $finalBoxs[$box['box_id']]['totalPrice']            =   isset($tPrice[$box['box_id']]) ? $tPrice[$box['box_id']] : "-";
            $finalBoxs[$box['box_id']]['status']                =   $status[$box['box_id']]['status'];
        }   
    
        return $finalBoxs;
    }
    
    /**
      * 根据campaign id 获取box
      * @param array $campaignIds
      * Mingshi
      * 2014-02-28
     */
    public function get_boxs_by_campaignIds($campaignIds = array()) {
        if (empty($campaignIds)) return array();

        # 获得相应advId
        $cidString = implode(',', $campaignIds);
        $advids = M('campaign', 'ad_box')->select(
            array(
                'id'    =>  array('IN'  =>  "&/($cidString)")
            ),
            array(
                'select'    =>  'advertiser_id',
            )
        );
        
        $aids = array_get_column($advids, 'advertiser_id');
        $aidString = implode(',', $aids);
        
        # 再拿到相应的order id
        $orders = M('orders', 'ad_core')->select(
            array(
                'advertiser_id' =>  array('IN'  =>  "&/($aidString)")
            ),
            array(
                'select'    =>  'id'
            )
        );

        $oids = array_get_column($orders, 'id');
        $oidString = implode(',', $oids);

        $idString = implode(',', $campaignIds);
        $where = array(
            '&/b.campaign_id'   =>  array('IN'  =>  "&/($idString)"),
            '&/b.box_id'    =>  array('IN'  =>  "&/($oidString)")
        );
        $attrs = array(
            'select'    =>  'c.id as cid, b.id as bid, c.name, b.box_id',
            'order_by'  =>  'b.create_time DESC',
        );

        $m = new Db_Model('campaign as c RIGHT JOIN campaign_box as b ON c.id = b.campaign_id', 'ad_box');

        return $m->select($where, $attrs);
    }

    /**
      * 根据campaign id 获取box id => name数组
      * @param array $campaignIds
      * Zhaolei
      * 2014-03-04
     */
    public function get_boxskv_by_campaignIds($campaignIds = array()) {
        $boxCamp = $this->get_boxs_by_campaignIds($campaignIds);
        $boxIds = array_get_column($boxCamp, 'box_id');
        $boxes = F::$f->Model_Adorder->get_box_by_ids($boxIds);
        return array_rack2nd_keyvalue($boxes, 'id', 'name');
    }
}
