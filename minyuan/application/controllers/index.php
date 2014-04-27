<?php
class Index extends MY_Controller
{
    public function __construct()
    {
        $this->_setModuleDir('');
        parent::__construct(FALSE);
    }

    public function index()
    {
        echo "1111111";exit;
    }
}
