<?php
class Index extends MY_Controller
{
    public function __construct()
    {
        parent::__construct(FALSE);

        $this->_setModuleDir('');
    }

    public function index()
    {
        $this->_view('index');
    }
}
