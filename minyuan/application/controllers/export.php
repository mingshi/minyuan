<?php
/**
 * @FileName    :   export.php
 * @QQ          :   224156865
 * @date        :   2014/05/18 09:49:43
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Export extends MY_Controller
{
    public function __construct() {
        $this->_setModuleDir('');
        parent::__construct(TRUE);
    }

    public function index() {
        if ($this->data['me']['is_admin'] != 1) {
            $this->_fail('你没有权限');
            return FALSE;
        }

        $this->data['menu'] = 'export';

        $m = new Db_Model('minyuan', 'minyuan');
        $orders = $m->select(array());
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="orders' . time() . '.csv"');
        header('Cache-Control: max-age=0');

        $fp = fopen('php://output', 'a'); 
        
        $head = array('编号', '手机', '订单名', '状态', '数量', '日期', '用户');
        
        fputcsv($fp, $head);

        foreach ($orders as $row) {
            $tmp = array($row['order_no'], $row['mobile'], $row['order_name'], $row['status'], $row['number'], $row['order_date'], $row['uid']);
            fputcsv($fp, $tmp);
        }

        fclose($fp);
    }
}

