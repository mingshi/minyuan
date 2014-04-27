<?php
class Logout extends MY_Controller
{
    public function __construct()
    {
        parent::__construct(FALSE);
    }
    
    public function index()
    {
        $redirectUri = d(@$_GET['redirect_uri'], '/');
        Session::getInstance()->clearUserID();
        redirect($redirectUri);
    }
}
