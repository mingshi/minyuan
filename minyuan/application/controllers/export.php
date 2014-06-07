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
	header("Content-Type:application/ms-download;charset=GB2312");
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
        
        $head = array(iconv('UTF-8','gbk','编号'), iconv('UTF-8', 'gbk', '手机'), iconv('UTF-8', 'gbk', '订单名'), iconv('UTF-8', 'gbk', '状态'), iconv('UTF-8', 'gbk', '数量'), iconv('UTF-8', 'gbk', '日期'), iconv('UTF-8', 'gbk', '用户'));
        
        fputcsv($fp, $head);

        foreach ($orders as $row) {
            $row['order_name'] = iconv('UTF-8','gbk',$row['order_name']);
            $row['status'] = iconv('UTF-8','gbk',$row['status']);
	    $tmp = array($row['order_no'], $row['mobile'], $row['order_name'], $row['status'], $row['number'], $row['order_date'], $row['uid']);
            fputcsv($fp, $tmp);
        }

        fclose($fp);
    }
}

