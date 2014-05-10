<?php
class Login extends MY_Controller
{

    public function __construct()
    {
        parent::__construct(FALSE);
        
        $this->_setModuleDir('');

        $this->load->helper('captcha');
    }

    public function captcha()
    {
        return create_captcha();
    }

    public function index()
    {
        if (!empty($this->data['myuid'])) {
            redirect('/');
        }
        
    	# 进行登录验证
        if (check_form_hash('login')) {
            $this->_login();
        }
        
        # 显示登录页面
        $this->_view('login');
    }

    protected function _login()
    {
        if ($this->form_validation->run('login') === FALSE) {
            $this->_fail(validation_errors());
            return;
        }
        
        $params = $this->_getPostParams(array(
            'username',
            'password',
        ), array(
            'remember' => 'BOOL'
        ));

        $username = $params['username'];
        $password = $params['password'];
        $expire = 3600 * 4;

        if ($params['remember']) {
            $expire = 3600 * 24 * 7;
        }

        $uid = 0;

        $userInfo = "";

        if (empty($userInfo)) {

            return $this->_fail('用户名或密码错误');
        }

        $this->_onLogin($userInfo);

        $uid = $userInfo['id'];

        $session = Session::getInstance();
        $session->setUserID($uid, FALSE, $expire);

        param_request(array(
            'redirect_uri' => $GLOBALS['PARAM_STRING']
        ));
        $redirectUri = d(@$_GET['redirect_uri'], '/');

        $this->_done();
    }

    # 登录成功后，打相关log 
    protected function _onLogin($userInfo)
    {
        //$uid = $userInfo['id'];
        //$username = $userInfo['username'];
        //$ip = get_client_ip();
        //$area = '未知'; 

        //$location = @json_decode(file_get_contents('http://g.ggxt.net/location?__ip=' . $ip), TRUE);

        //if ($location && isset($location['country'])) {
            //$area = $location['province'] . $location['city']; 
        //}

    }
}
