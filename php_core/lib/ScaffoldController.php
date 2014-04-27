<?php
class ScaffoldController extends Controller
{
    public function __construct($checkLogin = TRUE, $enableUserPermission = FALSE)
    {
        parent::__construct($checkLogin, $enableUserPermission);
        
        $this->load->library('form_validation');
        $this->load->helper('form');
        $this->form_validation->set_error_delimiters('<p class="error">', '</p>');

        if ( ! empty($this->_config)) {
            $this->_initConfig();
        }
        http_cache_header(0);
    }
    
    public function index()
    {
        $this->mlist();
    }

    protected function _setConfig($config)
    {
        $this->_config = $config;
        $this->_initConfig();
    }
    
    protected function _initConfig()
    {
        $config = &$this->_config;
        
        if (empty($config['table'])) {
            $config['table'] = strtolower($this->router->class);
        }
        
        if (empty($config['model'])) {
            throw new Exception("Missing scaffold config item 'model'.");
        }
        
        if (empty($config['fields'])) {
            $config['fields'] = array();
        }

        if (empty($config['name'])) {
            $config['name'] = $config['table'];
        }
        
        if (empty($config['primary_key'])) {
            $config['primary_key'] = 'id';
        }
        
        if (empty($config['helper'])) {
            $config['helper'] = new ScaffoldHelper();
        }
        
        $config['controller'] = $this->router->class;
        $config['controller_directory'] = $this->router->directory;

        if (!isset($config['list_url'])) {
            $config['list_url'] = '/' . $config['controller_directory'] . $config['controller'] . '/mlist';
        }
    }

    private function _setRequiredSource()
    {
        $config = $this->_config;
        foreach ($config['fields'] as $fieldCfg) {
            if (@$fieldCfg['type'] == 'date' ||
                @$fieldCfg['type'] == 'datetime'
            ) {
                //$this->_addJavascript('/scaffold/js/jquery-ui-1.8.4.custom.min.js');
                //$this->_addJavascript('/scaffold/js/datetimepicker/jquery.ui.datetimepicker.min.js');
                //$this->_addCss('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.7/themes/base/jquery-ui.css');
            }
            if (@$fieldCfg['type'] == 'image') {
                //$this->_addJavascript('/scaffold/js/swfupload.js');
                //$this->_addJavascript('/scaffold/js/jquery.img_uploader.js');
            }
            if (@$fieldCfg['rich_html']) {
                //$this->_addJavascript('/scaffold/js/kindeditor/kindeditor-min.js');
            }
        }
    }

    public function create()
    {
        $this->_mod('create'); 
    }
    
    public function edit()
    {
        $this->_mod('update');
    }
    
    public function mlist()
    {
        $config = $this->_config;
        $instanceName = $config['name'];
        $offset = $this->_getOffsetParam();
        $pageSize = d(@$config['list']['page_size'], 15);
        $sort = d(@$config['list']['sort'], '');
        $where = d(@$config['list']['where'], array());
        $attrs = array(
            'offset' => $offset,
            'limit' => $pageSize
        );
        if ($sort) {
            $attrs['order_by'] = $sort;
        }
        
        if (isset($config['list']['filter'])) {
            foreach ($config['list']['filter'] as $field) {
                if (isset($_GET[$field])) {
                    if ($_GET[$field] !== '') {
                        $where[$field] = $_GET[$field];
                    }
                }
            }
        }

        param_request(array(
            'kw' => 'STRING',
        ));

        $config['helper']->beforeList($where, $attrs);

        if ($GLOBALS['req_kw'] && isset($config['list']['keyword'])) {
            $subWhere = array();
            $kw = $GLOBALS['req_kw'];
            $kwc = $config['list']['keyword']; 

            $like = array( 'like' => '%' . $kw . '%' );
            
            $likeFields = is_hashmap($kwc) ? $kwc['like'] : $kwc; 

            if (!is_array($likeFields)) {
                $likeFields = array($likeFields) ;
            }

            foreach ($likeFields as $likeField) {
                $subWhere[] = array( $likeField => $like ); 
            }

            if (is_array($kwc) && isset($kwc['='])) {
                $subWhere[] = array( $kwc['='] => $kw );
            }
            
            $subWhere['__logic'] = 'OR';
            
            if ($where) {
                $where = array(
                    $where, 
                    $subWhere,
                    '__logic' => 'AND',
                );
            } else {
                $where = $subWhere;
            }
        }
        
        if (isset($config['delete_alias'])) {
            $subWhere = array(
                $config['delete_alias']['field'] => array(
                    '!=' => $config['delete_alias']['value']
                )
            );
            if ($where) {
                $where = array(
                    $where,
                    $subWhere,
                    '__logic' => 'AND',
                );
            } else {
                $where = $subWhere;
            }
        }

        $model = $config['model'];
        $total = $model->selectCount($where);
        $items = $model->select($where, $attrs);
        $title = d(@$config['title'], $instanceName . '列表');
        $this->_setPageTitle(d(@$config['page_title'], $title));
        $this->data['title'] = $title;
        $this->data['scaffold_config'] = $config;
        $this->data['scaffold_items'] = $config['helper']->processList($items);
        $this->data['scaffold_item_count'] = $total;
        $this->data['scaffold_pagination'] = Util_Pagination::getHtml($total, $pageSize);
        $this->data['scaffold_helper'] = $config['helper'];
        
        $this->_setRequiredSource();
        $this->_scaffoldView('list');
    }
     
    public function delete()
    {
        $config = $this->_config;
        $primaryKey = $config['primary_key'];
        
        if (isset($config['can_delete']) && $config['can_delete'] === FALSE) {
            $this->_fail('不能被删除');
            $this->_done();
        }

        param_request(array(
            $primaryKey => $GLOBALS['PARAM_STRING'],
        ));
        if (empty($GLOBALS['req_'.$primaryKey])) {
            show_error(ERR_MSG_PARAM_MISSING);
        }
        $id = explode(',', $GLOBALS['req_'.$primaryKey]);
        $conds = array(
            $primaryKey => $id,
        );
        
        $model = $config['model'];
        
        $rows = $model->select($conds);
        foreach ($rows as $row) {
            if ($config['helper']->onDelete($row) === FALSE) {
                $this->_fail($config['helper']->errors());
                return ;
            }
        }
        
        if (isset($config['delete_alias'])) {
            $upt = array(
                $config['delete_alias']['field'] => $config['delete_alias']['value']
            );
            $ret = $model->update($conds, $upt);
        } else {
            $ret = $model->delete($conds);
        }

        if ($ret) {
            $config['helper']->onDeleteSucc($rows); 
            $this->_succ('删除成功');
        } else {
            $this->_fail('删除失败');
        }
        $this->_done();
    }
    
    protected function _mod($type, $rewrite=FALSE)
    {
        $config = $this->_config;
        $primaryKey = $config['primary_key'];
        $instanceName = $config['name'];
        $controller = $config['controller'];
        
        $this->_setRequiredSource();
                
        if (check_form_hash("$controller/$type")) {
            $this->_do($type, $config);
        }
        
        $this->data['scaffold_item'] = array();

        $title = '创建' . $instanceName;    
        if ($type == 'update') {
            if (isset($_GET[$primaryKey])) {
                $conds = array($primaryKey => $_GET[$primaryKey]);
                $model = $config['model'];
                $item = $model->selectOne($conds);
                $item = $config['helper']->processItem($item);
                if ($item) {
                    $this->data['scaffold_item'] = $item;
                    $title = '编辑' . $instanceName;
                }
            }
            if (empty($this->data['scaffold_item'])) {
                show_error('Scaffold error:No instance specified to edit.');
            }

            if ($config['helper']->canEdit($item) === FALSE) {
                show_error($config['helper']->errors());
            }
        }
        $this->_setPageTitle(d(@$config['page_title'], $title));
        $this->data['title'] = d(@$config['title'], $title);
        $this->data['scaffold_config'] = $config;
        $this->data['form_hash'] = form_hash("$controller/$type");
        $this->data['scaffold_helper'] = $config['helper'];
        
        if (isset($_GET['redirect_uri'])) {
            $redirect_uri = $_GET['redirect_uri'];
        } else {
            $redirect_uri = $config['list_url'];
        }
        $this->data['redirect_uri'] = $redirect_uri;
        $this->data['isAjax'] = $this->isAjax;

        $processor = 'process' . camelize($type) . 'Data';
        $config['helper']->$processor($this->data);
        
        if ($rewrite === FALSE) {
            $this->_scaffoldView('mod');
        }
    }
    
    protected function _scaffoldDo($type, $partial = NULL)
    {
        $this->_do($type, $this->_config, $partial);
    }

    protected function _do($type, $config, $partial = NULL)
    {
        foreach ($config['fields'] as $fieldCfg) {
            if ($partial && !in_array($fieldCfg['field'], $partial)) {
                continue;
            }
            $this->form_validation->set_rules(
                $fieldCfg['field'],
                $fieldCfg['label'],
                isset($fieldCfg['rules']) ? $fieldCfg['rules'] : ''
            );
        }

        if ($this->form_validation->run() === FALSE) {
            $this->_fail(validation_errors());
            return FALSE;
        }
        
        $data = array();
        foreach ($config['fields'] as $fieldCfg) {
            if ($partial && !in_array($fieldCfg['field'], $partial)) {
                continue;
            }
            $name = $fieldCfg['field'];
            if (isset($_POST[$name])) {
                $data[$name] = $_POST[$name];
            }
        }
        
        if (isset($config["can_{$type}"]) && $config["can_{$type}"] === FALSE) {
            $this->_fail($type . ' forbidden!');
            return;
        }
        
        foreach (array_keys($data) as $field) {
            if (is_array($data[$field])) {
                //将数组转换成字符串
                $data[$field] = implode(',', $data[$field]);
            }
        }

        
        $model = $config['model'];
        $primaryKey = $config['primary_key'];
        $id = NULL;
        if ($type == 'create') {

            if ($config['helper']->onCreate($data) === FALSE) {
                $this->_fail($config['helper']->errors());
                return ;
            }

            if (isset($data[$primaryKey])) {
                $id = $data[$primaryKey];
                $ret = $model->insert($data);
            } else {
                $ret = $model->insert($data, TRUE);
                $id = $ret;
            }
            if ( ! $ret) {
                $this->_fail(ERR_MSG_DATABASE);
                return;
            } else {
                $data[$config['primary_key']] = $id;
            }
        } else {
            if (! isset($_POST[$primaryKey])) {
                show_error('Scaffold update mode need field "'.$primaryKey.'" setted.');
            }

            $conds = array($primaryKey => $_POST[$primaryKey]);
            
            $item = $model->selectOne($conds);
            
            if (empty($item)) {
                show_404();
            }

            if ($config['helper']->onUpdate($data, $item) === FALSE) {
                $this->_fail($config['helper']->errors());
                return ;
            }

            $ret = $model->update($conds, $data);
            if (! $ret) {
                $this->_fail(ERR_MSG_DATABASE);
                return;
            } else {
                $data[$primaryKey] = $_POST[$primaryKey];
            }
            $id = $_POST[$primaryKey];
        }
        if ($config['helper']->onFinish($id, $type) === FALSE) {
            $this->_fail($config['helper']->errors());
            return ;
        }
        $name = d(@$config['name'], $config['controller']);
        $action = $type == 'create' ? '创建' : '更新';
        $this->_done(NULL, $action.$name.'成功');
    }
    
    private function _scaffoldView($tpl)
    {
        $tplPath = dirname(__FILE__) . '/page/scaffold/tpl/' . $tpl . '.php';

        extract($this->data);
        ob_start();
        include($tplPath);

        $content = ob_get_contents();
        @ob_end_clean();

        $this->_view('__content', FALSE, $content);
    }
}

class ScaffoldHelper {
    protected $_scaffoldModuleDir = 'scaffold_modules';

    protected $_errors = '';

    public function errors()
    {
        return $this->_errors;
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'process') === 0) {
            return $arguments[0];
        }
        
        if (preg_match('@Render$@', $name)) {
            $ci = get_instance();
            $tplName = underscore(preg_replace('@Render$@', '', $name));
            $module = strtolower($ci->router->class);
            $action = strtolower($ci->router->method);
            $tplPath = APPPATH . 'views/' . $this->_scaffoldModuleDir. '/' . $module . '/';
            if (is_dir($tplPath . $action)) {
                $tplPath .= "$action/";
            }
            $tplPath .= $tplName;
            if (file_exists($tplPath . '.php')) {
                $viewPath = str_replace(APPPATH . 'views/', '', $tplPath);
                $data = $ci->data;
                $data = array_merge($data, array('args' => $arguments));
                $data['data'] = &$ci->data;
                $ci->load->view($viewPath, $data);
                return TRUE;
            }
        }
        return NULL;
    }

    public function onUpdate(&$row)
    {
        return TRUE;
    }

    public function onCreate(&$row)
    {
        return TRUE;
    }

    public function onDelete(&$row)
    {
        return TRUE;
    }

    public function processTpl($tpl, $data)
    {
        $ma = NULL;
        preg_match_all('@\{([^}]+)\}@U', $tpl, $ma);
        if (! $ma) {
            return $tpl;
        }
        $vars = array_unique($ma[1]);
        $search = array();
        $replace = array();
        foreach ($vars as $var) {
            $search[] = '{'.$var.'}';
            if (isset($data[$var])) {
                $replace[] = $data[$var];
            } else {
                # {create_time|strtotime|date 'Y-m-d'}
                $val = '';
                $segments = preg_split('@\s*\|\s*@u', $var);
                
                $first = array_shift($segments);

                $ma = NULL;
                if (preg_match('@^&(\w+)$@', $first, $ma) || isset($data[$first])) {
                    $val = $data[$ma ? $ma[1] : $first];
                } else {
                    #Maybe it's a function
                    if ( ! preg_match('@^(?:(\w+)::)?(\w+)((?:\s+\S+)*)$@u', $first, $ma)) {
                        trigger_error('Invalid scaffold expression:' . $tpl);
                        return '';
                    }

                    $class = $ma[0];
                    $method = $ma[1];
                    $args = preg_split('@\s+@', trim($ma[2]));
                    
                    if ($class) {
                        $method = array($class, $method);
                    }
                    
                    $args = array_map(create_function('$s', 'return trim($s, "\\"\'");'), $args);
                    $val = call_user_func_array($method, $args);
                }

                foreach ($segments as $segment) {
                    if ( ! preg_match('@^(?:(\w+)::)?(\w+)((?:\s+\S+)*)$@u', $segment, $ma)) {
                        trigger_error('Invalid scaffold expression:' . $tpl);
                        return '';
                    }

                    $class = $ma[1];
                    $method = $ma[2];
                    $args = array();
                    
                    if (trim($ma[3])) {
                        $args = preg_split('@\s+@', trim($ma[3]));
                    }

                    if ($class) {
                        $method = array($class, $method);
                    }
                    
                    $passArguIndex = array_search('-', $args);
                    if ($passArguIndex === FALSE) {
                        $args[] = $val;
                    } else {
                        $args[$passArguIndex] = $val;
                    }
                    $args = array_map(create_function('$s', 'return trim($s, "\\"\'");'), $args);

                    $val = call_user_func_array($method, $args);
                }
                $replace[] = $val; 
            }
        }
        return str_replace($search, $replace, $tpl);
    }

    function editLink($config, $item, $title = '编辑')
    {
        if (@$config['can_edit'] !== FALSE) {
            return '<a class="edit-btn btn btn-mini" href="/' . $config['controller_directory'] . $config['controller']
                . '/edit?' . $config['primary_key'] . '=' . $item[$config['primary_key']]
                . '&redirect_uri=' . urlencode(get_self_full_url())
                . '">' . $title . '</a>';
        } else {
            return '';    
        }
    }

    function deleteLink($config, $item, $title = '删除')
    {
        if (@$config['can_delete'] !== FALSE) {
            return '<a href="javascript:;" class="del-btn btn btn-mini btn-danger" rel="'
                . $item[$config['primary_key']] . '">' . $title . '</a>';
        } else {
            return '';
        }
    }

}
