<?php
class Util_SndaAccountCertify
{
    const CODE_SUCC = 0;
    const CODE_ERR = 1;
    
    private $_service = '';
    protected $_subSystem = "1004211";
    
    public function __construct()
    {
        if(ENV == 'DEVELOPMENT') {
            $this->_service = 'http://192.168.100.180:8083';
        } else {
            $this->_service = 'http://61.172.241.94:8083';
        }
    }
    
    public function request($path, $params = array())
    {
        if ( ! isset($params['sub'])) {
            $params['sub'] = $this->_subSystem;
        }
        
        $url = $this->_service . $path;
        $url = append_querystring_params($url, $params);
        
        $options = array(
            'max_redir'    => 1,
            'conn_retry'   => 3,
            'conn_timeout' => 60, 
            'timeout'      => 120,
            'use_post'     => FALSE,
        );  
        
        try {
            $response = http_post($url, array(), $options);
            $response = iconv('gbk', 'utf-8', $response);
            $segments = explode('|', $response);
            $ret = array();
            $ret['code'] = @$segments[0] == 1 ? self::CODE_SUCC : self::CODE_ERR;
            if ($ret['code'] == self::CODE_SUCC) {
                $ret['data'] = $segments;
            } else {
                $ret['msg'] = $segments[1];
            }
			return $ret;
        } catch (HTTPException $ex) {
            log_message("用户校验接口失败:$url|".$ex->getMessage(), LOG_ERR);
            return FALSE;
        }
        return FALSE;
    }
    
    public function certify($user, $pwd, $dyn, $ip = '')
    {
        $params = array(
            'user' => $user,
            'pwd' => strtoupper(md5($pwd)),
            'dyn' => $dyn,
            'ip' => $ip
        );
        
        if (defined('SUBSYSTEM_ID')) {
            $params['sub'] = SUBSYSTEM_ID;
        }
        
        $result = $this->request('/Tivoli/SsoCertify', $params);
        if ($result && $result['code'] == self::CODE_SUCC) {
            $data = $result['data'];
            $result['data'] = array(
                'user_id' => $data[1],
                'center' => $data[2],
                'department' => $data[3],
                'role' => $data[4],
            );
        }
        return $result;
    }
}