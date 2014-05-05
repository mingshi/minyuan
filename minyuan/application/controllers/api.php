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
        if (!$this->checkSignature()) {
            echo "no access";exit;
        }
    }

    public function index() {
        echo @$_GET['echostr'];
        $file_in = file_get_contents("php://input");
        $xml = simplexml_load_string($file_in);

        $eventArray = array();

        foreach ($xml->children() as $child) {
            $eventArray[$child->getName()] = $child;
        }

        if (array_key_exists('Event', $eventArray) && $eventArray['Event'] == "CLICK" && $eventArray['EventKey'] == 'ORDER_SEARCH') {
            $this->pushCommonMeg($eventArray['FromUserName'], '回复手机号码,即可查询订单情况');            
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

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $res = curl_exec($ch);

        return $res;
    }
}

