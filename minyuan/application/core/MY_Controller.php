<?php
class MY_Controller extends BackendController 
{
    public function __construct($checkLogin = TRUE)
    {
        parent::__construct($checkLogin, M('advertiser'));

        $this->load->library('session', NULL, 'ci_session');

        $this->data['c_menu'] = $this->router->class;
        $this->data['c_submenu'] = $this->router->class . '.' . $this->router->method;

        http_cache_header(0);
    }

    protected function _getUserExtraData($userInfo)
    {
        return NULL;
    }

    protected function getWeixinAccessToken() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx3ed335dc89e8be0e&secret=6a27535c16cb50eb9dfbf2c3448dd2e7";
        if ($file_content = @file_get_contents("/tmp/WtMiNyUan")) {
            $token_arr = explode(" ", $file_content);
            if (count($token_arr) == 2) {
                if (time() - $token_arr[1] < 7200) {
                    return $token_arr[0];
                }
            }
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回  
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $res = curl_exec($ch);

        $return = json_decode($res, TRUE);

        file_put_contents("/tmp/WtMiNyUan", $return['access_token'] . " " . time());           
        return $return['access_token'];
    }
}
