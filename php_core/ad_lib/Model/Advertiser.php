<?php
class Model_Advertiser extends Db_Model
{
    const STATUS_NORMAL = 100;
    const STATUS_FORBIDDEN = 0;
    const STATUS_DELETE = -100;
    
    const TYPE_COMPANY = 'company';
    const TYPE_INDIVIDUAL = 'individual';
    
    public static $TYPES = array(
        self::TYPE_COMPANY => '公司',
        self::TYPE_INDIVIDUAL => '个人',
    );

    private static $SALT = "FEELING NOT GOOD AGAIN!";

    public function __construct()
    {
        parent::__construct('advertiser', 'ad_core');
    }

    public static function passwordHash($username, $password) 
    {
        return md5($password . $username . self::$SALT);
    }

    public function check($username, $password) 
    {
        $passwdHash = self::passwordHash($username, $password);

        return $this->selectOne(array(
            'login_name' => $username,
            'password_hash' => $passwdHash,
            'status' => self::STATUS_NORMAL,
        ));
    }

    /**
      * 添加box系统的相关业务逻辑
      * mingshi <fivemingshi@gmail.com>
      * 2014-02-27 15:39
     */

    /**
      * 获得广告主基本信息
      * params  $advIds
      * type    array OR Integer
      * return  array
     */

    public function getAdverInfo ($advIds) {
        $return = array();

        $searchIds = "";

        if (is_array($advIds)) {
            $searchIds = implode(',', $advIds);
        } else {
            $searchIds = $advIds;
        }

        $return = $this->select(
            array(
                'id'    =>  array('IN'  =>  "&/($searchIds)"),
            )
        );

        return $return;
    }

    /**
     * 根据广告主id获得其名下所有素材
     * @param $advId
     * return array
     * Mingshi
     * 2014-03-02
     */
    public function get_materials_by_advid($advId) {
        if (!intval($advId)) return array();

        $return = array();
        
        #获得所有的ad
        $ads = $this->get_ads_by_advid($advId);
    
        $ids = array_get_column($ads, 'id');
        $idString = implode(',', $ids);
        
        $where = array(
            'id'    =>  array('IN'  =>  "&/($idString)"),
        ); 
        $attrs = array(
            'select'    =>  'material_ids',
        );

        $mids = M('ad', 'ad_core')->select($where, $attrs);
    
        #处理mid
        $midString = "";
        foreach ($mids as $mid) {
            $midString .= $mid['material_ids'] . ",";
        }
        
        $midString = trim($midString, ','); 

        $mWhere = array(
            'id'    =>  array('IN'  =>  "&/($midString)"),
        );

        $return = array();
        
        $return = M('material', 'ad_core')->select($mWhere);
        
        return $return;
    }

    /**
      * 根据广告主id获得所有ad
      * @param $advId
      * return array
      * Mingshi
      * 2014-03-02
     */
    public function get_ads_by_advid($advId) {
        if (!intval($advId)) return array();

        $m = M('orders', 'ad_core');
        #先活的所有的orders
        $orders = $m->select(
            array(
                'advertiser_id' =>  intval($advId)
            )
        );
         
        $rids = array_get_column($orders, 'id');
        $idString = implode(',', $rids);
        
        $return = array();
        #下面获得所有的ad
        $m_ad = M('ad', 'ad_core');
        $where = array(
            'order_id'  =>  array('IN' => "&/($idString)"),
        );
        $return = $m_ad->select($where);
        return $return;
    }
}
