<?php
/**
 * @FileName    :   edit.php
 * @QQ          :   224156865
 * @date        :   2014/06/14 22:05:12
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class edit extends MY_Controller
{
    public function __construct() {
        $this->_setModuleDir('');
        parent::__construct(TRUE);
    }

    public function index() {
        $this->data['menu'] = 'edit';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = $this->data;
            $order_no = trim($_POST['order_no']);
            //检查是否存在这个订单
            $m = new Db_Model('minyuan', 'minyuan');

            $tmpOrder = $m->select(array(
                'order_no'  =>  $order_no
            ));

            if (empty($tmpOrder)) {
                $this->_fail('不存在该订单');
                return FALSE;
            }

            $this->_done('/edit/do_edit?order_no=' . $order_no, '正在为您跳转...');
        } else {
            $this->_view('edit');
        }
    }

    public function do_edit() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->data['menu'] = 'edit';
            $data = $this->data;
            $order_no = trim($_POST['order_no']);

            $status = trim($_POST['order_status']);

            $upData = array('status' => $status);

            $m = new Db_Model('minyuan', 'minyuan');

            $m->update(array('order_no' => $order_no), $upData);

            $this->_done('/edit/do_edit?order_no=' . $order_no, '修改成功');
        } else {
            $this->data['menu'] = 'edit';
            $data = $this->data;

            $order_no = trim($_GET['order_no']);

            $m = new Db_Model('minyuan', 'minyuan');
            $tmpOrder = $m->select(array(
                'order_no'  =>  $order_no
            ));

            if (empty($tmpOrder)) {
                $this->_fail('不存在该订单');
                return FALSE;
            }

            $this->data['order_name'] = $tmpOrder[0]['order_name'];
            $this->data['order_no'] = $tmpOrder[0]['order_no'];
            $this->data['mobile'] = $tmpOrder[0]['mobile'];
            $this->data['order_date'] = $tmpOrder[0]['order_date'];
            $this->data['order_num'] = $tmpOrder[0]['number'];
            $this->data['order_status'] = $tmpOrder[0]['status'];


            $this->_view('do_edit');
        }
    }
}

