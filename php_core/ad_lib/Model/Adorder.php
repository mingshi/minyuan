<?php
/**
 * @FileName    :   Adorder.php
 * @QQ          :   224156865
 * @date        :   2014/02/28 15:49:31
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Model_Adorder extends Db_Model {
    const STATUS_DELETE = -100;
    const STATUS_INACTIVE = 90;
    const STATUS_ACTIVE = 100;

    public static $STATUS = array(
        self::STATUS_DELETE => '已删除',
        self::STATUS_INACTIVE => '无效',
        self::STATUS_ACTIVE => '有效',
    );

    public function __construct() {
        parent::__construct('orders', 'ad_core');
    }

    /**
      * 根据订单ids获得总的price
      * @param array $oids
      * Mingshi
      * 2014-02-28
     */

    public function get_total_price_by_oids($oids = array()) {
        if (empty($oids)) return 0;

        $idString = implode(',', $oids);
        $res = $this->selectOne(
            array(
                'id'  =>  array("IN" => "&/($idString)"),
            ),
            array(
                'select'    =>  'sum(total_price) as total',
            )
        );

        return $res['total'];
    }

    public function get_total_price_by_oids_group($oids = array()) {
        if (empty($oids)) return 0;

        $idString = implode(',', $oids);
    
        $res = $this->select(
            array(
                'id'  =>  array("IN" => "&/($idString)"),
            ),
            array(
                'select'    =>  'sum(total_price) as total, id',
                'group_by'  =>  'id',
            )
        );
        
        return $res;
    }

    public function get_order_status_by_oids($oids = array()) {
        if (empty($oids)) return array();

        $idString = implode(',', $oids);
        $activeStatus = Model_Ad::get_active_status();

        $where = array(
            'order_id'  =>  array('IN' => "&/($idString)"),
            'status'    =>  array('IN' => "&/($activeStatus)"),
        );
        $attrs = array(
            'select'    =>  'count(*) as total, order_id',
            'group_by'  =>  'order_id',
        );
        
        $res = M('Ad')->select($where, $attrs);
    
        array_change_key($res, 'order_id');

        $return = array();
        foreach ($oids as $oid) {
            $return[$oid]['status']['state'] = (isset($res[$oid]) && !empty($res[$oid])) ? Model_Box_Campaign::CAMPAIGN_ACTIVE : Model_Box_Campaign::CAMPAIGN_PAUSE;
            $return[$oid]['status']['des'] = (isset($res[$oid]) && !empty($res[$oid])) ? Model_Box_Campaign::$CAMPAIGN_STATUS[Model_Box_Campaign::CAMPAIGN_ACTIVE] : Model_Box_Campaign::$CAMPAIGN_STATUS[Model_Box_Campaign::CAMPAIGN_PAUSE];
        }

        return $return;
    }

    /**
      * 根据box id获得相应的order
      * @param array $cids
      * Zhaolei
      * 2014-03-03
     */
    public function get_box_by_ids($ids){
        if (empty($ids)) return array();
        $idArr = implode(',', $ids);

        $where = array(
            'id'   =>  array('IN'  =>  "&/($idArr)"),
        );
        $res = $this->select($where);
        return $res;
    }

    /**
     * get_box_price 获取某天box的总价
     * 
     * @param mixed $pOrderId 
     * @param mixed $pDate 
     * @access public
     * @return void
     */
    public function get_box_price($pOrderId, $pDate)
    {
         static $cache = array();

        if (isset($cache[$pDate][$pOrderId])) {

            return $cache[$pDate][$pOrderId];
        }

        $row = $this->selectOne(array(
            'id' => $pOrderId,
        ), array(
            'select' => 'id,total_price as price',
        ));
    
        if (empty($row)) {

            return 0;
        }

        $date = date('Y-m-d');
        $history = array(
            $date => $row['price']
        );

        $rows = F::$f->Db_Model('order_bid_log', 'ad_core')->select(array(
            'order_id' => $pOrderId,
        ));

        foreach ($rows as $row) {
            $history[$row['date']] = $row['price']; 
        }

        ksort($history);

        $dates = array_reverse(array_keys($history));

        foreach ($dates as $date) {
            if ($pDate >= $date) {
                $cache[$pDate][$pOrderId] = $history[$date]; 
                break;
            }
        }
        
        if (!isset($cache[$pDate][$pOrderId])) {
            $cache[$pDate][$pOrderId] = $history[$dates[count($dates) - 1]];
        }

        return $cache[$pDate][$pOrderId];
    }
}

