<?php
/**
 * @FileName    :   api.php
 * @QQ          :   224156865
 * @date        :   2014/04/27 17:36:54
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Api extends MY_Controller
{
    public function __construct() {
        $this->_setModuleDir('');
        parent::__construct(FALSE);
        /*
        if (!$this->checkSignature()) {
            echo "no access";exit;
        }
         */
    }

    public function index() {
        $file_in = file_get_contents("php://input");
        $xml = simplexml_load_string($file_in);

        $eventArray = array();

        foreach ($xml->children() as $child) {
            $eventArray[$child->getName()] = $child;
        }

        if (array_key_exists('Event', $eventArray) && $eventArray['Event'] == "CLICK" && $eventArray['EventKey'] == 'ORDER_SEARCH') {
            echo $this->pushCommonMeg($eventArray['FromUserName'], $eventArray['ToUserName'], '回复手机号码,即可查询订单情况');            
        }

        if (array_key_exists('Content', $eventArray)) {
            $str = "";
            if(preg_match("/1[3458]{1}\d{9}$/",$eventArray['Content'])) {
                // 通过手机号码 查询订单状态
                $m = new Db_Model('minyuan', 'minyuan');

                $result = $m->select(array(
                    'mobile'    =>  trim($eventArray['Content'])
                ));

                if ($result) {
                    foreach ($result as $res) {
                        $str .= $res['order_name'] . " " . $res['status'] . "\n";
                    }
                } else {
                        $str = "没有订单信息";
                } 
            } else {
                $str = "手机号码格式不对";
            }

            echo $this->pushCommonMeg($eventArray['FromUserName'], $eventArray['ToUserName'], $str);
        }

        exit;
    }

    private function checkSignature()
    {
        $signature = @$_GET["signature"];
        $timestamp = @$_GET["timestamp"];
        $nonce = @$_GET["nonce"];    
                    
        $token = "MINyUAnGlaSs888";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return TRUE;
        }else{
            return FALSE;
        }
    } 

    private function pushCommonMeg($toUser, $fromUser, $content)
    {
        //先获得token
        $token = $this->getWeixinAccessToken();

        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=" . $token;

        $data = '<xml>
            <ToUserName><![CDATA['. $toUser .']]></ToUserName>
            <FromUserName><![CDATA['. $fromUser .']]></FromUserName>
            <CreateTime>'. time() .'</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[' . $content . ']]></Content>
            </xml>';
        
        return $data;
    }
}

