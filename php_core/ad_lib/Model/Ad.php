<?php
/**
 * @FileName    :   Adorder.php
 * @QQ          :   224156865
 * @date        :   2014/02/28 14:35:27
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Model_Ad extends Db_Model {
    const STATUS_DELETE     =   -100;
    const STATUS_DRAFT      =   10;
    const STATUS_READY      =   20;
    const STATUS_PAUSE      =   30;
    const STATUS_COMPLETE   =   40;
    const STATUS_UNCOMPLETE =   50;
    const STATUS_OK         =   100;

    public function __construct() {
        parent::__construct('ad', 'ad_core');
    }

    public static $AD_STATUS = array(
        self::STATUS_DELETE     =>  "删除",
        self::STATUS_DRAFT      =>  "草稿",
        self::STATUS_READY      =>  "准备投放",
        self::STATUS_PAUSE      =>  "暂停",
        self::STATUS_COMPLETE   =>  "投放完成",
        self::STATUS_UNCOMPLETE =>  "未完成",
        self::STATUS_OK         =>  "正在投放",
    );

    public static function get_active_status() {
        return self::STATUS_READY . "," . self::STATUS_UNCOMPLETE . "," . self::STATUS_OK;
    }

}

