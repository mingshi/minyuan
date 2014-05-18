<?php

define('ERR_MSG_DATABASE', '数据库错误。');
define('ERR_MSG_USER_NOT_EXISTS', '用户不存在。');
define('ERR_MSG_PARAM_MISSING', '缺少参数。');
define('ERR_MSG_PARAM_INVALID', '无效的参数。');
define('ERR_MSG_ACCESS_DENY', '你没有权限进行该操作。');
define('ERR_MSG_SAVE_SUCC', '保存成功。');
define('ERR_MSG_REDIRECT', 'ERR_MSG_REDIRECT');

/**
 * Extend the codeignitor controller
 */
abstract class Controller extends CI_Controller
{
    const ROLE_ADMIN = 1;

    const ROLE_GUEST = 1024;

    public $data = array();
    public $isAjax = FALSE;
    public $session = NULL;
    public $permission = NULL;

    private $_moduleDir = 'modules';
    //模板继承深度, -1 不限
    private $_tplInheritanceDepth = -1;

    private $_css = array(
        'file'  => array(),
        'inner' => array()
    );
    
    private $_javascript = array(
        'file'  => array(),
        'inner' => array()
    );
    
    private $_enableUserPermission = FALSE;
   
    
    public function __construct($checkLogin = TRUE, $enableUserPermission = FALSE)
    {
        parent::__construct();
        
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
        ) { 
            $this->isAjax = TRUE;
        }
        
        $this->_enableUserPermission = $enableUserPermission;

        if ($enableUserPermission) {
            $this->permission = new Permission();
            if ( ! empty($this->_permissionTable)) {
                $this->permission->setPermissionTable($this->_permissionTable);
            }
        } 

        $this->_initSession($checkLogin);
    }
    
    /**
     * 初始化session时，用于获取用户信息，如果需要用户登录，需要重载该方法 
     */
    protected function _getUserInfo($uid)
    {
        return array();
    }
    
    /**
     * Session验证时使用的额外数据，为防止不同环境中的session中uid相同互串
     */
    protected function _getUserExtraData($userInfo)
    {
        return NULL;
    }
    
    /**
     * 获取用户的角色
     */
    protected function _getUserRole($userInfo)
    {
        return self::ROLE_ADMIN;
    }

    //不推荐使用，后期会删除 
    public function checkPermission($resource = NULL, $strict = TRUE)
    {
        return $this->_checkPermission($resource, $strict);
    }

    protected function _checkPermission($resource = NULL, $strict = TRUE)
    {
        if ( ! $this->permission) {
            return TRUE;
        }

        if ( ! $resource) {
            $resource = $this->router->class . '.' . $this->router->method;
        }
        
        return $this->permission->checkPermission($resource, $strict);
    }
   
    /**
     * 当前用户登录信息合法时调用
     */
    protected function _onSessionOk()
    {
    }
    
    /**
     * 当用户操作出现权限不足时
     */
    protected function _onPermissionDeny()
    {
        $msg = '对不起，您没有权限进行该操作!';
        
        if (!empty($_POST)) {
            $this->_fail($msg);
        }

        if ( ! empty($_SERVER['HTTP_REFERER'])) {
            $msg .= '&nbsp;<a href="' . $_SERVER['HTTP_REFERER'] . '">返回前一页</a>';
        }
        $this->_showError($msg); 
    }
    
    protected function _getLoginUrl()
    {
        return append_querystring_params(
            '/login', array(
                'redirect_uri' => get_self_full_url(),
            )
        );
    }

    protected function _initSession($checkLogin)
    {
        $data = &$this->data;
        $this->session = Session::getInstance();
        $uid = $this->session->getUserID();
        $data['myuid'] = $uid;
        $data['me'] = array();

        if ($checkLogin && ! $this->session->isValid()) {
            $loginUrl = $this->_getLoginUrl(); 
            $this->_done($loginUrl);
        }
        
        if ($uid) {
            $GLOBALS['myuid'] = $uid;
            $extra = $this->session->getExtraData();
            $userInfo = $this->_getUserInfo($uid);
            $userExtra = $this->_getUserExtraData($userInfo);
            if ( ! empty($userInfo) && ( ! $extra || $extra == $userExtra)) {
                $data['me'] = $userInfo;
            } else {
                $reason = empty($userInfo) ? 'no userinfo' : "extra info not match:$extra != $userExtra";
                log_message("User session error:$reason", LOG_ERR);
                $this->session->clearUserID();
                if ($checkLogin) {
                    $redirectUri = append_querystring_params(
                        '/login', array(
                            'redirect_uri' => get_self_full_url(),
                        )
                    );
                    redirect($redirectUri);    
                } else {
                    return;
                }
            }
            if ($this->_enableUserPermission) {
                $roles = $this->_getUserRole($userInfo); 
                $this->permission->setRoles($roles);
                if ( ! $this->_checkPermission()) {
                    $this->_onPermissionDeny();
                }
            }
            $this->_onSessionOk();
        }
    }
    
    protected function _setModuleDir($dir)
    {
        $this->_moduleDir = $dir;
    }
    
    protected function _setTplInheritanceDepth($depth)
    {
        $this->_tplInheritanceDepth = $depth;    
    }

    /**
     * 设置页面标题
     */
    protected function _setPageTitle($title)
    {
        $this->data['__title'] = $title;
    }
    
    /**
     * 添加引入css文件或代码
     */
    protected function _addCss($css)
    {
        if (preg_match('@\.css@', $css)) {
            if ( ! in_array($css, $this->_css['file'])) {
                $this->_css['file'][] = $css;    
            }
            return;
        }
        $this->_css['inner'][] = $css;
    }
    
    /**
     * 清除当前引入CSS文件，包括默认
     */
    protected function _clearCss()
    {
        $this->_css['file'] = array();    
    }
    
    /**
     * 添加引入javascript文件或代码
     */
    protected function _addJavascript($js)
    {
        if (preg_match('@\.js@', $js)) {
            if ( ! in_array($js, $this->_javascript['file'])) {
                $this->_javascript['file'][] = $js;
            }
            return;
        }
        return $this->_javascript['inner'][] = $js;
    }
    
    /**
     * 清除当前引入javascript文件，包括默认
     */
    protected function _clearJavascript()
    {
        $this->_javascript['file'] = array();
    }
    
    protected function _getPostParams($names, $def = array(), $strip = TRUE)
    {
        foreach ($names as $name) {
            if ( ! isset($def[$name])) {
				if ($strip) {
					$def[$name] = $GLOBALS['PARAM_STRING'] | $GLOBALS['PARAM_STRIPTAGS'];
				} else {
					$def[$name] = $GLOBALS['PARAM_STRING'];
				}
            }
        }
        $params = array();
        param_post($def, '', $params);

        return $params;
    }

    protected function _getGetParams($names, $def = array(), $strip = TRUE)
    {
        foreach ($names as $name) {
            if ( ! isset($def[$name])) {
				if ($strip) {
					$def[$name] = $GLOBALS['PARAM_STRING'] | $GLOBALS['PARAM_STRIPTAGS'];
				} else {
					$def[$name] = $GLOBALS['PARAM_STRING'];
				}
            }
        }
        $params = array();
        param_get($def, '', $params);

        return $params;
    }
    
    protected function _done($redirectUri = NULL, $msg = NULL)
    {
        if (empty($redirectUri)) {
            param_request(array(
                'redirect_uri' => $GLOBALS['PARAM_STRING'],
            ));
            $redirectUri = d(@$GLOBALS['req_redirect_uri'], $_SERVER['HTTP_REFERER']);
        }
        
        $this->_processRedirectUri($redirectUri);
        
        if ($this->isAjax) {
            $data = $redirectUri;
            if ( ! empty($msg)) {
                $data = array(
                    'redirect_uri' => $redirectUri,
                    'msg' => $msg,
                );
            }
            $this->_succ(ERR_MSG_REDIRECT, $data);
        } else {
            if ($msg) {
                $this->_succ($msg); 
            }
            redirect($redirectUri);
        }
    }
    
    protected function _processRedirectUri(&$redirectUri) {}

    protected function _setFlashMsg($type, $msg)
    {
        $option = array(
            'type' => $type,
            'msg' => $msg,
            'time' => time(),
        );
        set_default_cookie('F_MSG', $option);
    }

    
    function _getFlashMsg($clear = FALSE, $scalar = TRUE)
    {
        $name = 'F_MSG';
        $msg = isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
        if ($msg && $clear) {
            unset_default_cookie($name);
        }
        $msg = @json_decode($msg, TRUE);
        if ($scalar) {
            return @$msg['msg'];
        }
        return $msg;
    }

    protected function _succ($msg = ERR_MSG_SAVE_SUCC, $data = array(), $onlySet = FALSE)
    {
        if ($this->isAjax && ! $onlySet) {
            if ($msg == ERR_MSG_REDIRECT) {
                $ret = array(
                    'code' => 0,
                    'redirect_uri' => $data,
                );
                if (is_array($data)) {
                    $ret['redirect_uri'] = $data['redirect_uri'];
                    $ret['msg'] = $data['msg'];
                }
            } else {
                $ret = array(
                    'code' => 0,
                    'msg'  => $msg,
                    'data' => $data,
                );
            }
            echo json_encode($ret);
            exit;
        }
        
        $this->_setFlashMsg('succ', $msg);
    }
    
    protected function _fail($msg)
    {
        if ($this->isAjax) {
            $ret = array(
                'code' => -1,
                'msg' => $msg,
            );
            echo json_encode($ret);
            exit;
        }
        
        $this->_setFlashMsg('error', $msg);
    }
    
    protected function _showError($message)
    {
        show_error($message);
    }
    
    protected function _getOffsetParam()
    {
        $offset = 0;
        if (isset($_GET['offset'])) {
            $offset = (int) $_GET['offset'];
        }
        if ($offset < 0) {
            $offset = 0;
        }
        return $offset;
    }

    /**
     * 输出通用框架结构的页面
     */
    protected function _view($module, $returnContent = FALSE, $__content = NULL)
    {
        $data = &$this->data;
        $data['__js'] = $this->_javascript;
        $data['__css'] = $this->_css;
        $data['__msg'] = $this->_getFlashMsg(TRUE, FALSE);
        if ($this->_moduleDir) {
            $module = $this->_moduleDir . '/' . $module;
        }
        return $this->_myview($module, $data, $returnContent, $__content);
    }
    

    /**
     * 输出非通用框架结构的页面
     */
    protected function _viewSingle($tpl, $returnContent = FALSE)
    {
        $data = &$this->data;
        $data['__js'] = $this->_javascript;
        $data['__css'] = $this->_css;
        $data['__msg'] = $this->_getFlashMsg(TRUE, FALSE);
        return $this->_myview("singles/{$tpl}", $data, $returnContent);
    }

    private function _myview($view, $data, $returnContent = FALSE, $__content = NULL)
    { 
        $viewPath = APPPATH . 'views';
        $segments = explode('/', $view);
        
        if ($segments[count($segments) - 1] == '__content' && ! is_null($__content)) {
            $content = $__content;
        } else {
            $content = $this->load->view($view, $data, TRUE);
        }

        $tplDepth = count($segments);
        if ($this->isAjax) {
            if ($this->_tplInheritanceDepth >= 0) {
                $tplDepth = $this->_tplInheritanceDepth;
            } else {
                $tplDepth = 0;    
            }
        } else {
            if ($this->_tplInheritanceDepth >= 0) {
                $tplDepth = $this->_tplInheritanceDepth;
            }
        }
        
        while (TRUE) {
            if ($tplDepth <= 0) {
                break;
            }
            $tpl = array_pop($segments);
            $path = $viewPath . '/' . implode('/', $segments);
            $baseTpl = $path . '/__base.php';
            if (file_exists($baseTpl)) {
                $data['__content'] = $content;
                $view = implode('/', $segments) . ($segments ? '/' : '')
                      . '__base';
                $content = $this->load->view($view, $data, TRUE);
            }
            
            if (count($segments) == 0) {
                break;
            }
            --$tplDepth;
        }
        
        if (isset($_SERVER['HTTP_PARTIAL'])) {
            $contentId = $_SERVER['HTTP_PARTIAL'];
            if (preg_match("@content-id=\"{$contentId}\">(.+?)<!--END {$contentId}-->@msi", $content, $ma)) {
                $content = $ma[1];
            } else {
                $content = '';
            }
        }

        if ($returnContent) {
            return $content;
        }
        
        $this->output->append_output($content);
    }
}

/**
 * has_permission 判断是否对某一个或某一些资源拥有权限
 * 
 * @param mixed $resource 单个资源或多个资源，多个资源之间使用 , | & 号分隔，,与&表示且关系，|表示或关系
 * @param mixed $strict 默认为TRUE，为TURE表示检查是否拥有该资源的全部权限，FLASE则部分权限也返回TRUE
 * @return boolean 
 */
function has_permission($resource, $strict = TRUE)
{
    $instance = get_instance();
    if (empty($instance->permission)) {
        return TRUE;
    }
    $p = $instance->permission;

    $query = preg_split('@([|&,])@', $resource, NULL, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    
    $ret = TRUE;
    $preCondType = '&';
    for ($i = 0, $n = count($query); $i < $n; $i += 2) {
        $resource = $query[$i];
        $cond = $p->checkPermission($resource, $strict);
        $ret = $preCondType == '|' ? ($ret or $cond) : ($ret and $cond);
        $preCondType = isset($query[$i + 1]) ? $query[$i + 1] : '|';
    }

    return $ret;
}
