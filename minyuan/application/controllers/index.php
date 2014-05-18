<?php
class Index extends MY_Controller
{
    public function __construct()
    {
        $this->_setModuleDir('');
        parent::__construct(TRUE);

        $this->_setConfig(array(
            'name'  =>  '添加订单状态',
            'model' =>  new Db_Model('minyuan', 'minyuan'),
            'fields'=>array(
                array(
                    'field' =>  'mobile',
                    'label' =>  '手机号码',
                    'rules' =>  'number|required|trim|strip_tags|max_with[11]|min_with[11]',
                ),

                array(
                    'field' =>  'order_name',
                    'label' =>  '订单名称',
                    'rules' =>  'required|trim|strip_tags',
                ),

                array(
                    'field' =>  'status',
                    'label' =>  '订单状态',
                    'rules' =>  'required|trim|strip_tags',
                ),
            )
        ));
    }

    public function index()
    {
        $this->_view('index');
    }

    public function create()
    {
        $data = $this->data;
        param_get(array(
            'mobile'    =>  'STRING',
            'order_name'    =>  'STRING',
            'status'    =>  'STRING',
        ), '', $params, array());

        $uniq = md5($params['_POST']['mobile'] . 'MiYuANGlASs' . $params['_POST']['order_name']);

        $m = new Db_Model('minyuan', 'minyuan');
        $tmpOrder = $m->select(array(
            'uniq'  =>  $uniq
        ));

        if (!empty($tmpOrder)) {
            $this->_fail('不能重复添加相同的订单');
            return FALSE;
        }

        if (!preg_match("/1[3458]{1}\d{9}$/",$params['_POST']['mobile'])) {
            $this->_fail('手机号码格式不正确');
            return FALSE;
        }

        $config = $this->_config;
        foreach ($config['fields'] as $fieldCfg) {
            $this->form_validation->set_rules(
                $fieldCfg['field'],
                $fieldCfg['label'],
                isset($fieldCfg['rules']) ? $fieldCfg['rules'] : ''
            ); 
        }

        if ($this->form_validation->run() === FALSE) {
            $this->_fail(validation_errors());
            return FALSE;
        } else {
            $model = $config['model'];
            $data = array();
            foreach ($config['fields'] as $field) {
                $data[$field['field']] = $params['_POST'][$field['field']];
            }
            $data['uniq'] = $uniq;
            $data['uid'] = $this->data['myuid'];

            $ret = $model->insert($data);

            if (!$ret) {
                $this->_fail('添加失败');
            } else {
                $this->_done(NULL, '添加成功');
            }
        }        
    }
}
