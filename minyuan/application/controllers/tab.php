<?php
/**
 * @FileName    :   tab.php
 * @QQ          :   224156865
 * @date        :   2014/05/04 21:26:52
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Tab extends MY_Controller
{
    public function __construct() {
        $this->_setModuleDir('');
        parent::__construct(FALSE);
    }

    public function index() {
        $token = $this->getWeixinAccessToken();
        $tabs = array(
            "button"    => array(
                0   =>    array(
                    "name"  =>  "产品介绍",
                    "sub_button"    =>  array(
                        0   =>  array(
                            "type"  =>  "view",
                            "name"  =>  "钢化玻璃",
                            "url"   =>  "http://www.glassmy.com/gh.html"        
                        ),
                        1   =>  array(
                            "type"  =>  "view",
                            "name"  =>  "夹胶玻璃",
                            "url"   =>  "http://www.glassmy.com/jc.html",
                        ),
                        2   =>  array(
                            "type"  =>  "view",
                            "name"  =>  "中空玻璃",
                            "url"   =>  "http://www.glassmy.com/zk.html"
                        ),
                        3   =>  array(
                            "type"  =>  "view",
                            "name"  =>  "幕墙玻璃",
                            "url"   =>  "http://www.glassmy.com/mq.html"
                        ),
                    ),
                ),

                1   =>  array(
                    "name"  =>  "订单查询",
                    "type"  =>  "click",
                    "key"   =>  "ORDER_SEARCH" 
                ),
            ),
        );

        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $token;
    
        foreach ($tabs as $btn) {
            foreach ($btn as $key => $_btn) {
                $tabs['button'][$key]['name'] = urlencode($tabs['button'][$key]['name']);
                if (isset($_btn['sub_button'])) {
                    foreach ($_btn['sub_button'] as $k => $b) {
                        $tabs['button'][$key]['sub_button'][$k]['name'] = urlencode($tabs['button'][$key]['sub_button'][$k]['name']);
                    }
                }
            }
        }
            
        $tmpTabs = json_encode($tabs);
        $postData = urldecode($tmpTabs);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen(json_encode($postData)))
        );

        $res = curl_exec($ch);
        var_dump($res);exit;
    }
}

