<?php
class MY_Controller extends BackendController 
{
    public function __construct($checkLogin = TRUE)
    {
        parent::__construct($checkLogin, NULL);
    }
}
