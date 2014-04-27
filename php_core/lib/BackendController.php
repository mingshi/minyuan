<?php
class BackendController extends ScaffoldController
{
    protected $_permissionTable = array(
        'roles' => array(
            self::ROLE_ADMIN => array(
                '__name' => '管理员',
                '__all',
            ),
        ),
        'public' => array(
            'login', 'logout', 'index'
        ),
    );

    protected $_userModel = NULL;

    public function __construct($checkLogin, $userModel)
    {
        $this->_userModel = $userModel;

        parent::__construct($checkLogin, TRUE);
    }

    protected function _getUserInfo($uid)
    {
        return $this->_userModel->find($uid);
    }

    protected function _getUserExtraData($userInfo)
    {
        if ( ! $userInfo) {
            return NULL;
        }

        return $userInfo['username'];
    }

    protected function _onSessionOk()
    {
        $this->data['myuid'] = $GLOBALS['myuid'] = $this->data['me']['id'];
        parent::_onSessionOk();
    }

    /**
     * 获取用户的角色
     */
    protected function _getUserRole($userInfo)
    {
        return isset($userInfo['roles']) ? $userInfo['roles'] : self::ROLE_ADMIN;
    }
    
    /**
     * 添加member登陆
     * mingshi
     * 2014-02-26
     */
    protected function _loginWithAuth($checkAllow = TRUE) {
        $params = $this->_getPostParams(array(
            'username',
            'password',
        ));

        $username = trim($params['username']);
        $password = trim($params['password']);

        if (empty($username)) {
            return $this->_fail('请输入账号');
        }

        if (empty($password)) {
            return $this->_fail('请输入密码');
        }

        $expire = Config::get('Cookie.Expire', 3600);

        $auth = new HZ_Auth();

        $res = $auth->login($username, $password);

        $resArr = json_decode($res, TRUE);

        if ($resArr['code'] != 0 || $resArr['status'] != "ok") {
            return $this->_fail($resArr['msg']);
        }
        
        $extraData = $this->_getUserExtraData($resArr['info']);

        $m = $this->_userModel;

        $rid = "";
        $localUserInfo = $m->selectOne(array('username' => $username));
        if (empty($localUserInfo)) {

            if ($checkAllow === FALSE) {
                $rid = $m->insert(array(
                    'uid'           =>  $resArr['info']['id'],
                    'username'      =>  $resArr['info']['username'],
                    'realname'      =>  $resArr['info']['realname'],
                    'login_time'    =>  '&/CURRENT_TIMESTAMP',
                    'login_ip'      =>  get_client_ip(),
                ));
            } else {
                return $this->_fail('账号不允许登录');
            }
        } else {
            $rid = $localUserInfo["id"];
            $m->update(array('uid' => $resArr['info']['id']), array(
                'realname'      =>  $resArr['info']['realname'],
                'login_time'    =>  '&/CURRENT_TIMESTAMP',
                'login_ip'      =>  get_client_ip(),
            ));
        }
        
        $this->session->setUserID($rid, FALSE, $expire, $extraData);

        $this->_done();
    }

    protected function _login()
    {
        $params = $this->_getPostParams(array(
            'username',
            'password',
        ));

        $username = trim($params['username']);
        $password = trim($params['password']);

        if (empty($username)) {
            return $this->_fail('请输入账号');
        }
        
        if (empty($password)) {
            return $this->_fail('请输入密码');
        }

        $expire = Config::get('Cookie.Expire', 3600);
        $uid = 0;
        $m = $this->_userModel;
        $isSuperUser = FALSE;
                
        if ( ! $m->selectCount(array('username' => $username))) {
            $this->_fail('账号不存在');
            return; 
        }
        
        $passwordHash = md5(Config::get('Cookie.Slat') . $username . $password);

        $userInfo = $m->selectOne(array(
            'username' => $username,
            'password' => $passwordHash
        ));

        if (empty($userInfo)) {
            return $this->_fail('密码错误');
        }

        $uid = $userInfo['id'];
        $m->update(array('id' => $uid), array(
            'login_time' => '&/CURRENT_TIMESTAMP'
        ));
        
        $extraData = $this->_getUserExtraData($userInfo);
        $this->session->setUserID($uid, FALSE, $expire, $extraData);
        $this->_onLogin($userInfo);

        $this->_done();
    }

    protected function _onLogin($userInfo)
    {
        return;
    }
}
