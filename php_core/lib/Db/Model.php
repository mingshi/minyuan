<?php
class Db_Model extends Model_Base
{
    protected static $_forceReadOnMater = FALSE;
    
    protected $_table = NULL;
    protected $_dbClusterId = NULL;
    protected $_readOnMaster = FALSE;
    //Used with farm db
    protected $_objectId = NULL;

    protected $_eventHandlers = array();

    private $_dbInstance = NULL;
    protected $_sqlHelper = NULL;
    private $_lastSql;
     
    /**
     * 构造器
     * 
     * @param object $logger default NULL,日志记录对象
     * @param string $table default NULL, 表名，为NULL则不能使用基类提供的数据库操作方法
     * @param int $clusterId default NULL, 数据库cluster id
     * @param int $objectId default NULL, 对象id，用于分库选取用，单库不需要设置此参数
     */
    function __construct ($table = NULL, $clusterId = NULL, $objectId = NULL)
    {
        $this->_table = $table;
        $this->_dbClusterId = $clusterId;
        $this->_objectId = $objectId;
        $this->_sqlHelper = Db_Sql::getInstance();
    }
    
    //设置所有的Model都强制读写主库
    public static function setForceReadOnMater ($bool = TRUE)
    {
        Db_Model::$_forceReadOnMater = $bool;
    }
    
    protected static function _getMapValue($map, $key)
    {
        if (is_null($key)) {

            return $map;
        }

        return isset($map[$key]) ? $map[$key] : NULL;
    }

    public function getLastId()
    {
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }

        return $db->getLastId();
    }

    public function insert ($insArr, $returnLastId = FALSE)
    {
        if ($this->_table === NULL) {
            return FALSE;
        }
        
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }
        
        $this->beforeInsert($insArr);
        
        $sql = 'INSERT ' . $this->_table() . $this->_sqlHelper->insert($insArr);
        $ret = $db->mod($sql);
        $this->_lastSql = $sql;
        if ($ret === FALSE) {
            $this->log("[$sql] " . $db->getWriteErrorInfo(), LOG_ERR);
            return FALSE;
        }
        
        $lastId = 0;
        if ($returnLastId) {
            $lastId = $db->getLastId();
        }
        
        $this->afterInsert($insArr, $lastId);
        
        return $returnLastId ? $lastId : $ret;
    }
    
    public function insertReplace ($insArr, $replaceArr = NULL)
    {
        if ($this->_table === NULL) {
            return FALSE;
        }
        
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }
        
        $this->beforeInsertReplace($insArr, $replaceArr);
        
        $sql = 'INSERT ' . $this->_table() . $this->_sqlHelper->replace($insArr, $replaceArr);
        
        $ret = $db->mod($sql);
        $this->_lastSql = $sql;
        
        if ($ret === FALSE) {
            $this->log("[$sql] " . $db->getWriteErrorInfo(), LOG_ERR);
            return FALSE;
        }
        
        $this->afterInsertReplace($insArr, $replaceArr);
        
        return $ret;
    }
    
    public function update ($where, $uptArr)
    {
        if ($this->_table === NULL) {
            return FALSE;
        }
        
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }
        
        $this->beforeUpdate($where, $uptArr);
        
        $sql = 'UPDATE ' . $this->_table() . $this->_sqlHelper->update($uptArr) . $this->_sqlHelper->where($where);
        $ret = $db->mod($sql);
        $this->_lastSql = $sql;
        
        if ($ret === FALSE) {
            $this->log("[$sql] " . $db->getWriteErrorInfo(), LOG_ERR);
            return FALSE;
        }
        
        $this->afterUpdate($where, $uptArr);
        
        return $ret;
    }
    
    public function delete ($where)
    {
        if ($this->_table === NULL) {
            return FALSE;
        }
        
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }
        
        $this->beforeDelete($where);
        
        $sql = 'DELETE FROM ' . $this->_table() . $this->_sqlHelper->where($where);
        
        $ret = $db->mod($sql);
        $this->_lastSql = $sql;
        
        if ($ret === FALSE) {
            $this->log("[$sql] " . $db->getWriteErrorInfo(), LOG_ERR);
            return FALSE;
        }
        
        $this->afterDelete($where);
        
        return $ret;
    }
    
    public function addEventHandler($handlerObj)
    {
        $class = get_class($handlerObj);
        if ( ! isset($this->_eventHandlers[$class])) {
            $this->_eventHandlers[$class] = $handlerObj;
        }
    }

    protected function beforeInsert (&$data)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->beforeInsert($this, $data);
        }
    }

    protected function afterInsert ($data, $lastId)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->afterInsert($this, $data, $lastId);
        }
    }

    protected function beforeUpdate (&$where, &$data)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->beforeUpdate($this, $where, $data);
        }
    }

    protected function afterUpdate ($where, $data)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->afterUpdate($this, $where, $data);
        }
    }

    protected function beforeInsertReplace (&$data, &$replace)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->beforeInsertReplace($this, $data, $replace);
        }
    }

    protected function afterInsertReplace ($data, $replace)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->afterInsertReplace($this, $data, $replace);
        }
    }

    protected function beforeDelete (&$where)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->beforeDelete($this, $where);
        }
    }

    protected function afterDelete ($where)
    {
        foreach ($this->_eventHandlers as $handler) {
            $handler->afterDelete($this, $where);
        }
    }
    
    public function select ($where = array(), $attrs = array())
    {
        if ($this->_table === NULL) {
            return FALSE;
        }
        
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }
        
        if (is_callable(array(
            $this, 
            'beforeSelect'
        ), TRUE)) {
            $this->beforeSelect($where, $attrs);
        }
        
        $selectFields = isset($attrs['select']) ? $attrs['select'] : '*';
        
        $sql = "SELECT {$selectFields} FROM " . $this->_table() . $this->_sqlHelper->where($where, $attrs);
        $res = NULL;
        $this->_lastSql = $sql;
        
        if ($db->select($sql, $res) === FALSE) {
            $this->log("[$sql] " . $db->getReadErrorInfo(), LOG_ERR);
            return FALSE;
        }
        
        if (is_callable(array(
            $this, 
            'afterSelect'
        ), TRUE)) {
            $this->afterSelect($res);
        }
        
        return $res;
    }
    
    public function selectOne ($where = array(), $attrs = array())
    {
        $attrs['limit'] = 1;
        $attrs['offset'] = 0;
        
        $res = $this->select($where, $attrs);
        if ($res === FALSE) {
            return FALSE;
        }
        if (empty($res)) {
            return NULL;
        }
        return $res[0];
    }
    
    public function selectCount ($where = array(), $attrs = array())
    {
        if (! isset($attrs['select'])) {
            $attrs['select'] = 'COUNT(0)';
        }
        $attrs['select'] .= ' AS `total`';
        
        $res = $this->selectOne($where, $attrs);
        if ($res === FALSE) {
            return FALSE;
        }
        return intval($res['total']);
    }
    
    /**
     * getListData 返回记录行、总数、及分页HTML代码的数组 
     * 
     * @param mixed $where 
     * @param mixed $attrs 
     * @param mixed $data 如果传入该参数的话，则直接将返回数据设置在此变量中
     * @access public
     * @return array()
     */
    public function getListData($where, $attrs, &$data = NULL)
    {
        $items = $this->select($where, $attrs);
        $total = $this->selectCount($where);

        $pageSize = $attrs['limit'];
        $pagination = Util_Pagination::getHtml($total, $pageSize);

        if ( ! is_null($data)) {
            $data['items'] = $items;
            $data['totalCount'] = $total;
            $data['pagination'] = $pagination;
            return;
        }

        return array(
            'items' => $items,
            'totalCount' => $total,
            'pagination' => $pagination,
        );
    }

    /**
     * find 主键查询
     * 
     * @param mixed $primaryKeys 单个值或是数组值
     * @param string $primaryKeyName 
     * @access public
     * @return mixed 主键传入单个值时，返回单行记录，多行值返回以主键值为Key的关联数组
     */
    public function find($primaryKeys, $primaryKeyName = 'id')
    {
        if (empty($primaryKeys)) {
            return array();
        }

        $needArray = is_array($primaryKeys);

        if ($needArray) {
            $primaryKeys = array_unique($primaryKeys);
        }

        $rows = $this->select(array(
            $primaryKeyName => $primaryKeys,
        ));
        
        if ( ! $needArray) {
            return $rows ? $rows[0] : array();
        }

        $ret = array();
        foreach ($rows as $row) {
            $ret[$row[$primaryKeyName]] = $row;
        }

        return $ret;
    }

    /**
     * Execute sql statement:
     * For select statement, return the rows;
     * For non-select statement, return rows affected;
     * When error, return false
     * 
     * @param string $sql
     */
    public function execute ($sql)
    {
        $method = @strtoupper(array_shift(explode(' ', trim($sql))));
        
        $db = $this->_getDbInstance();
        if (! $db) {
            return FALSE;
        }
        
        if (in_array($method, array(
            'SELECT', 
            'SHOW', 
            'DESC'
        ))) {
            $res = NULL;
            if ($db->select($sql, $res) === FALSE) {
                $this->log("[$sql] " . $db->getReadErrorInfo(), LOG_ERR);
                return FALSE;
            }
            return $res;
        } else {
            $ret = $db->mod($sql, 'a');
            $this->_lastSql = $sql;
            if ($ret === FALSE) {
                $this->log("[$sql] " . $db->getWriteErrorInfo(), LOG_ERR);
                return FALSE;
            }
            return $ret;
        }
    }
    
    /**
     * Magic函数 
     * 用于实现 get_by_xxx/getByXxx方法 
     */
    public function __call ($name, $args)
    {
        if (strpos($name, 'get_by_') === 0) {
            $key = substr($name, 7);
            $value = $args[0];
            return $this->selectOne(array(
                $key => $value
            ));
        } else 
            if (strpos($name, 'getBy') === 0) {
                $key = strtolower(substr($name, 5));
                if ($key) {
                    $where = array(
                        $key => $args[0]
                    );
                    return $this->selectOne($where);
                }
            } else 
                if (strpos($name, 'before') === 0 || strpos($name, 'after') === 0) {
                    return TRUE;
                }
        trigger_error('Undefined method ' . $name . ' called!');
        return FALSE;
    }
    
    public function setReadOnMaster ($bool = TRUE)
    {
        $this->_readOnMaster = $bool;
        if ($this->_dbInstance) {
            $this->_dbInstance->setReadOnMaster($bool);
        }
    }
    
    public function getTable ()
    {
        return $this->_table;
    }
    
    public function table ($table = NULL)
    {
        if (empty($table)) {
            return $this->_table;
        }
        $this->_table = $table;
    }
    
    public function getDatabaseName()
    {
        $db = $this->_getDbInstance();
        if ($db) {
            return $db->getDbName();
        }

        return NULL;
    }

    private function _table()
    {
        $tables = $this->_table;
        if (is_string($this->_table)) {
            $tables = array($this->_table);
        }

        $arr = array();
        foreach ($tables as $table) {
            if (preg_match('@^\w+$@', $table)) {
                $arr[] = "`{$table}`";
            } else {
                $arr[] = $table;
            }
        }

        return implode(',', $arr);
    }

    public function getLastSql ()
    {
        return $this->_lastSql;
    }
    
    protected function _getDbInstance ()
    {
        if ($this->_dbInstance) {
            return $this->_dbInstance;
        }
        
        if ($this->_dbClusterId !== NULL) {
            if ($this->_objectId !== NULL) {
                //It's farm db
                $this->_dbInstance = Db_FarmDb::getInstanceByObjectId($this->_objectId, $this->_dbClusterId);
            } else {
                $this->_dbInstance = Db_GlobalDb::getInstance($this->_dbClusterId);
            }
            $this->_dbInstance->setReadOnMaster(Db_Model::$_forceReadOnMater || $this->_readOnMaster);
            return $this->_dbInstance;
        }
        
        return NULL;
    }
    
    public function __destruct ()
    {
        if ($this->_dbInstance) {
            $this->_dbInstance->close();
            $this->_dbInstance = NULL;
        }
        $this->_sqlHelper = NULL;
    }
}
