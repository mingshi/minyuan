<?php
/**
  * hz member login & get user info
  * 2013-08-30 14:20
  * auth: mingshi
  */

class HZ_Auth {
    // config for auth login url
    protected $loginUrl = "http://member.adeaz.com/api/login";

    // config for auth user info url
    protected $userInfoUrl = "http://member.adeaz.com/api/userinfo_by_username";

    // config for login key
    protected $loginKey = "7d26eda13b33c1257999de31bcd8ebfc";
    protected $loginSign = "adeazMemberSigKey@#$%^&";

    //config for user info key
    protected $infoKey = "3ec8e544422a626075d904d0a9be0dcb";
    protected $infoSign = "!adeazMemberUiFO7&^%";

    public function login($username, $password) {
        if (!$username || !$password) {
            return FALSE;
        }
        
        $sign = $this->create_login_sign($username, $password);

        $params = array();
        $params['username'] = $username;
        $params['password'] = $this->create_tmp_pass($password);
        $params['sign']     = $sign;
        
        return $this->go_curl($this->loginUrl, $params);
    }

    public function get_user_info($username) {
        if (!$username) return FALSE;
        
        $params = array();
        $params['username'] = $username;
        $params['key'] = $this->infoKey;
        $params['sign'] = $this->create_user_info_sign($username);

        return $this->go_curl($this->userInfoUrl, $params);
    }
    
    private function create_tmp_pass($password) {
        return md5($this->loginSign.md5($password));
    }

    private function create_login_sign($username, $password) {
        $tmpPassword = $this->create_tmp_pass($password);
        $tmpUri = "key=".$this->loginKey."&password=".$tmpPassword."&username=".$username;

        return md5($tmpUri);
    }

    private function create_user_info_sign($username) {
        $tmpUri = "key=".$this->infoSign."&username=".$username;
        return md5($tmpUri);
    }

    private function go_curl($url, $params) {
        try {
            $data = http_build_query($params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $res = curl_exec($ch);
            curl_close($ch);
            
            $tmpRes = @json_decode($res, TRUE);

            if (!isset($tmpRes['status'])) {
                $tmpArr = array(
                    status  =>  'err',
                    code    =>  113,
                    msg =>  '用户系统错误'
                );
                $res = json_encode($tmpArr);
            }
            
            return $res;
        } catch (Exception $e) {
            $tmpArr = array(
                status  =>  'err',
                code    =>  114,
                msg =>  '系统错误'
            );
            return json_encode($tmpArr);
        }
    }
}
