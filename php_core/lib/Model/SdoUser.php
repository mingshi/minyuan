<?php
class Model_SdoUser extends Model_Base
{
    const CAS_LOGIN_URI = 'https://cas.sdo.com/cas/login';
    const CAS_VALIDATE_URI = 'https://cas.sdo.com/cas/Validate';
    const CAS_DISPATH_LOGIN_URI = 'http://10.241.14.39:8080/dispatchLogin';
    //静态密码验证地址
    const DISPATCH_LOGIN_URI = 'https://dplogin.sdo.com/dispatchlogin.fcgi';
    const BATCH_WSDL_URL = "http://ptcom.sdo.com/BatchPTComSearch.asmx?WSDL";

    const CACHE_KEY_IP_USERNAME_LIMIT = 'login.auth.limit.ip:%s:username:%s';
    const CACHE_KEY_IP_LIMIT = 'login.auth.limit.ip:%s';

    public function __construct()
    {
        parent::__construct();
    }

    public static function getInstance()
    {
        static $instance = NULL;
        if ($instance == NULL) {
            $instance = new self();
        }
        return $instance;
    }

    public static function getAuthCode($username)
    {

    }

    public function checkAuthCode($username, $authCode)
    {
        return TRUE;
    }

    protected function _checkErrorTimes($username)
    {
        $ip = get_client_ip();
        $key = sprintf(self::CACHE_KEY_IP_USERNAME_LIMIT, $ip, $username);
        $limit = Cache_PHPRedis::GET($key);
        $sysLimit = Config::get('DEFAULT_LOGIN_AUTH.IP_USERNAME_LIMIT');
        $sysLimit = empty($sysLimit) ? 5 : $sysLimit;
        if (!empty($limit) && $limit >= $sysLimit) {
            return FALSE;
        }

        $key = sprintf(self::CACHE_KEY_IP_LIMIT, $ip);
        $limit = Cache_PHPRedis::GET($key);
        $sysLimit = Config::get('DEFAULT_LOGIN_AUTH.IP_LIMIT');
        $sysLimit = empty($sysLimit) ? 10 : $sysLimit;
        if (!empty($limit) && $limit >= $sysLimit) {
            return FALSE;
        }
        return TRUE;
    }

    protected function _recordErrorTimes($username)
    {
        $ip = get_client_ip();
        $key = sprintf(self::CACHE_KEY_IP_USERNAME_LIMIT, $ip, $username);
        Cache_PHPRedis::INCR($key);
        $ipUsernameLimitTime = Config::get('DEFAULT_LOGIN_AUTH.IP_USERNAME_LIMIT_TIME');
        $cacheTime = empty($ipUsernameLimitTime) ? 1800 : $ipUsernameLimitTime;
        Cache_PHPRedis::EXPIRE($key, $cacheTime);

        $key = sprintf(self::CACHE_KEY_IP_LIMIT, $ip);
        Cache_PHPRedis::INCR($key);
        $ipLimitTime = Config::get('DEFAULT_LOGIN_AUTH.IP_LIMIT_TIME');
        $cacheTime = empty($ipLimitTime) ? 1800 : $ipLimitTime;
        Cache_PHPRedis::EXPIRE($key, $cacheTime);
    }

    public function getUserInfo($username, $password, &$error, $authCode = NULL)
    {
        $defaultLoginAuth = Config::get('DEFAULT_LOGIN_AUTH');
        if (!isset($defaultLoginAuth['ENABLE']) || $defaultLoginAuth['ENABLE']) {
            if ($authCode) {
                if (! $this->checkAuthCode($username, $authCode)) {
                    $error = "验证码错误";
                    return FALSE;
                }
            }else{
                if (! $this->_checkErrorTimes($username)) {
                    $error = "您尝试登录次数过多,请稍后再试";
                    return FALSE;
                }
            }
        }
        
        $validateURI = Config::get('User_VALIDATE_URI');
        $appId = Config::get('AUTH_APP_ID');
        $params = array(
            'appId' => $appId,
            'appArea' => '0',
            'IdType' => '1', //PTID 0数字ID
            'uuid' => $username,
            'Password' => $password,
            'clientIP' => get_client_ip()
        );
        $url = append_querystring_params($validateURI, $params);
        $options = array(
            'max_redir' => 1,
            'conn_retry' => 3,
            'conn_timeout' => 60,
            'timeout' => 120,
            'use_post' => FALSE
        );
        try {
            //ETS::start(STAT_ET_CAS_RESPONSE);
            $response = http_post($url, array(), $options);
            //ETS::end(STAT_ET_CAS_RESPONSE);
            $info = explode('^$^', $response);
            $allowList = array(
                '0',
                '5',
                '9',
                'C',
                'D'
            );
            if (! in_array($info[0], $allowList)) {
                log_message("User login fail.cas response:$response", LOG_WARNING);
                $error = "帐号或密码错误";
                if (!isset($defaultLoginAuth['ENABLE']) || $defaultLoginAuth['ENABLE']) {
                    $this->_recordErrorTimes($username);
                }
                return FALSE;
            }
            if ($info[0] == '0') {
                $retData = array(
                    'sdid' => $info[1],
                    'ptid' => $info[2]
                );
            } else {
                $retData = array(
                    'sdid' => $info[1],
                    'ptid' => $info[3]
                );
            }
            return $retData;
        } catch ( HTTPException $ex ) {
            error_report(NULL, '认证接口访问错误。' . $ex);
            $error = "认证服务器繁忙";
            return FALSE;
        }
        return NULL;
    }

    public static function getCasLoginUri($service, $gateway = false)
    {
        $params = array(
            'service' => $service
        );
        if ($gateway) {
            $params['gateway'] = 'true';
        }
        return append_querystring_params(self::CAS_LOGIN_URI, $params);
    }

    public static function getUserInfoByTicket($ticket, &$error)
    {
        $validateURI = self::CAS_VALIDATE_URI;
        $protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE ? 'http' : 'https';
        $service = "$protocol://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $validateParams = array(
            'ticket' => $ticket,
            'extend' => TRUE,
            'service' => $service
        );
        $url = append_querystring_params($validateURI, $validateParams);
        $options = array(
            'max_redir' => 1,
            'conn_retry' => 3,
            'conn_timeout' => 60,
            'timeout' => 120,
            'use_post' => FALSE
        );
        try {
            //ETS::start(STAT_ET_CAS_RESPONSE);
            $response = http_post($url, array(), $options);
            //ETS::end(STAT_ET_CAS_RESPONSE);
            $info = explode("\n", $response);
            if ($info[0] !== 'yes') {
                log_message("Cas response invalid.Cas response:$response", LOG_ERR);
                $error = "认证服务器繁忙";
                return FALSE;
            }
            return array(
                'sdid' => $info[2],
                'ptid' => $info[1],
                'loginid' => $info[3]
            );
        } catch ( HTTPException $ex ) {
            error_report(NULL, '认证接口访问错误。' . $ex->getMessage());
            $error = "认证服务器繁忙";
            return FALSE;
        }
        return FALSE;
    }

    /**
     * 批量根据pt帐号得到 sdid
     * @param array $pt
     */
    public static function getSdidByPt($ptArray)
    {
        $userInfos = array();
        if (!is_array($ptArray)) {
            $ptArray = array($ptArray);
        }

        $param = array();
        $param['value'] = implode("^$^", $ptArray);
        try {
            $client = new soapclient(self::BATCH_WSDL_URL);
            $result = $client->BatchPTCom($param);
        } catch (Exception $e) {
            return false;
        }

        $xml = simplexml_load_string($result->BatchPTComResult);
        $stat = $xml->xpath("/Result/StatCode");
        $accounts = $xml->xpath("/Result/accounts/account");
        if (false === $stat || false === $accounts)
        {
            return false;
        }

        if ($stat[0] != 0)
        {
            return false;
        }

        foreach ($accounts as $account)
        {
            $attributes = $account->attributes();
            $userInfos[(string)$account]['ptid'] = (string)$attributes['ptid'];
            $userInfos[(string)$account]['sdid'] = (string)$attributes['sdid'];
        }

        return $userInfos;
    }
}
