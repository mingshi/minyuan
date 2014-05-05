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
        $a = json_encode($_POST); 
        file_put_contents('/tmp/wxJ', $a, FILE_APPEND);
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
}

