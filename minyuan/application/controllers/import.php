<?php
/**
 * @FileName    :   import.php
 * @QQ          :   224156865
 * @date        :   2014/05/18 10:15:28
 * @link
 * @Auth        :   Mingshi <fivemingshi@gmail.com>
 */

class Import extends MY_Controller
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
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_FILES['file']['error'] > 0) {
                $this->_fail('上传失败');
                return FALSE;
            } else {
                $name = $_FILES['file']['name'];
                if (!$name) {
                    $this->_fail('请选择文件');
                    return FALSE;
                }

                $tmp_name = $_FILES['file']['tmp_name'];
                if ($_FILES['file']['type'] != 'text/csv') {
                    $this->_fail('只允许上传csv文件');
                    return FALSE;
                }

                $location = '/tmp/upload/';

                if (file_exists($location.$name)) {
                    $this->_fail('文件已经存在');
                    return FALSE;
                }

                if (move_uploaded_file($tmp_name, $location.$name)) {
                    $fp = fopen($location.$name, 'r');

                    $m = new Db_Model('minyuan', 'minyuan');
                    while (! feof($fp)) {
                        $tmpData = fgetcsv($fp);
                        $uniq = md5($tmpData[0] . 'MiYuANGlASs' . $tmpData[1]);
                        $tmpOrder = $m->select(array(
                            'uniq'  =>  $uniq
                        ));
                        
                        if ($tmpData[0] != '手机')  {
                            if (!empty($tmpOrder)) {
                                $upData = array();
                                $upData['status']  = $tmpData[2];
                                $ret = $m->update(array('uniq' => $uniq), $upData);
                            } else {
                                $data = array();
                                if (preg_match("/1[3458]{1}\d{9}$/",$tmpData[0])) {
                                    $data['mobile'] = $tmpData[0];
                                    $data['order_name'] = $tmpData[1];
                                    $data['status'] = $tmpData[2];
                                    $data['uniq'] = $uniq;
                                    $data['uid'] = $tmpData[3];

                                    $ret = $m->insert($data);
                                }
                            }
                        }
                    }
                    $this->_done(NULL, '上传成功');
                } else {
                    $this->_fail('上传失败');
                    return FALSE;
                }
            }
        }

        $this->data['menu'] = 'import';
        $this->_view('import'); 
    }
}

