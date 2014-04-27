<?php
Class Db_Redis 
{
    public static $PERSISTENT_CONNECT = TRUE;
    public static $TIMEOUT = 5; 
    
    protected $_client = NULL;
    
    protected static $_instances = array();

    private function __construct()
    {
        $this->_client = new Redis();  
    }

    public function __call ($name, $arguments)
    {
        if ( ! $this->_client) {
            log_message('Redis client invalid!', LOG_ERR);
            return FALSE;
        }
        
        if ( ! method_exists($this->_client, $name)) {
            trigger_error("The method \"$name\" not exists for Redis object.", E_USER_ERROR);
            return FALSE;
        }
        
        if (empty($arguments)) {
            $arguments = array();
        }
        
        try {
            ETS::start(STAT_ET_REDIS);
            $ret = call_user_func_array(array($this->_client, $name), $arguments);
            ETS::end(STAT_ET_REDIS, "method:$name");
        } catch (Exception $e) {
            $ret = FALSE;
            log_message("Redis exception:" . $e, LOG_ERR);
        }

        if ($ret === FALSE && in_array($name, array('open', 'connect', 'popen', 'pconnect'))) {
			error_report(STAT_ER_REDIS, "REDIS connect error:{$arguments[0]}:{$arguments[1]}");
        }

        return $ret;
    }
    
    public function __destruct()
    {
        if ($this->_client) {
            @$this->_client->close();
        }
    }
    
    public static function getInstance($clusterId = 'default')
    {
        if (isset(self::$_instances[$clusterId])) {
            return self::$_instances[$clusterId];
        }

        $config = Config::get("redis_single.{$clusterId}"); 

        if (empty($config)) {
            trigger_error("Config error:no redis cluster config $clusterId", E_USER_ERROR);
            return NULL;
        }

        list($map, $db) = explode('.', $config);

        $physicalConfig = Config::get("redis_physical.{$map}");
        if (empty($physicalConfig)) {
            trigger_error("Config error:no redis physical config $map", E_USER_ERROR);
            return NULL;
        }
 
        $host = $physicalConfig['host'];
        $port = $physicalConfig['port'];

        $client = new self();
        
        $connectRet = TRUE;
        if (self::$PERSISTENT_CONNECT) {
            $connectRet = $client->pconnect($host, $port, self::$TIMEOUT); 
        } else {
            $connectRet = $client->connect($host, $port, self::$TIMEOUT);
        }

        if ( ! $connectRet) {
            return NULL;
            //throw error?    
        }

        self::$_instances[$clusterId] = $client;
        return self::$_instances[$clusterId];
    }
}
