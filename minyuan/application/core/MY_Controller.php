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
}
