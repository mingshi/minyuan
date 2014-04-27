<?php
class SP_G
{
    public static $events = array();
}

/**
 * 短信报警
 */
function alert_sms($message, $jobName = 'aa.web.default!')
{
    if (! ALLOW_SMS) {
        return;
    }

    # Not yet
}

function _alert_sms($name, $phone, $message)
{
    if (! ALLOW_SMS) {
        return;
    }
    /*
    $url = Config::get('SMS_SEND_URL');
    if (! function_exists('http_post')) {
        sp_load_helper('http');
    }
    $message = iconv('utf-8', 'gb18030', $message);
    //防止消息过长
    $len1 = strlen($message);
    $len2 = mb_strlen($message, 'gbk');
    if ($len1 == $len2 && $len1 > 140) {
        $message = mb_substr($message, 0, 140, 'gbk');
    } else if ($len1 != $len2 && ($len2) > 60) {
        $message = mb_substr($message, 0, 60, 'gbk');
    }
    $params = array(
        'phone' => $phone,
        'msg' => $message 
    );
    $url = append_querystring_params($url, $params);
    $options = array(
        'max_redir' => 1,
        'conn_retry' => 3,
        'conn_timeout' => 60,
        'timeout' => 120,
        'use_post' => FALSE 
    );
    try {
        $response = http_post($url, array(), $options);
    } catch ( HTTPException $ex ) {
        return FALSE;
    }
    */
    return TRUE;
}

/**
 * log 
 */
function log_message($msg, $level = LOG_INFO)
{
    $args = func_get_args();
    if (trigger_event('on_log_message', $args)) {
        return;
    }
    if (! (is_int($level) && $level <= LOG_DEBUG && $level >= LOG_EMERG)) {
        return;
    }
    if (defined('LOG_LEVEL')) {
        $logLevel = LOG_LEVEL;
    } else {
        $logLevel = LOG_ERR;
    }
    $logTypes = array(
        LOG_DEBUG => 'DEBUG',
        LOG_INFO => 'INFO',
        LOG_NOTICE => 'NOTICE',
        LOG_WARNING => 'WARNING',
        LOG_ERR => 'ERR',
        LOG_CRIT => 'CRIT',
        LOG_ALERT => 'ALERT',
        LOG_EMERG => 'EMERG' 
    );
    if ($level <= $logLevel) {
        $logType = $logTypes[$level];
        $msg = date('Y-m-d H:i:s') . ":[{$logType}] {$msg}\n";
        $fn = date('Ymd') . ".log";
        $logdir = PATH_LOG;
        if (defined('APP_PATH_LOG')) {
            $logdir = APP_PATH_LOG;
        }
        if (! is_dir($logdir)) {
            mkdir($logdir, 0777, TRUE);
        }
        $fileName = $logdir . DS . $fn;
        if (! file_exists($fileName)) {
        	 error_log($msg, 3, $fileName);
        	 chmod($fileName, 0777);
        } else {
        	error_log($msg, 3, $fileName);
        }
       
        if (defined('LOG_STDOUT')) {
            echo $msg;
        }
    }
    
    /*
    if (defined('LOG_SCRIBE_LEVEL')) {
        $logScribeLevel = LOG_SCRIBE_LEVEL;
    } else {
        $logScribeLevel = LOG_ERR;
    }
    if ($level <= $logScribeLevel && Util_ScribeLog::$STATUS_OK) {
        //载入Scribe相关库文件
        if (! isset($GLOBALS['THRIFT_ROOT'])) {
            $GLOBALS['THRIFT_ROOT'] = PATH_LIB . DS . 'scribe';
            require_once $GLOBALS['THRIFT_ROOT'] . '/scribe.php';
            require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TSocket.php';
            require_once $GLOBALS['THRIFT_ROOT'] . '/transport/TFramedTransport.php';
            require_once $GLOBALS['THRIFT_ROOT'] . '/protocol/TBinaryProtocol.php';
        }
        $logCategory = LOG_SCRIBE_CATEGORY;
        if (defined('APP_LOG_SCRIBE_CATEGORY')) {
            $logCategory = APP_LOG_SCRIBE_CATEGORY;
        }
        $logCategory = ENV . '_' . $logCategory;
        $msg = '[' . php_uname('n') . '] ' . $msg;
        $ret = Util_ScribeLog::sendLog($msg, $logCategory);
    }
    */
}

/**
 * 报警
 */
if (! function_exists('error_report')) {

    function error_report($errno, $msg, $smsMsg = '')
    {
        log_message('error_report:' . $msg, LOG_ERR);
         //send mail or sms?			
    }
}
if (! function_exists('class_alias')) {

    function class_alias($original, $alias)
    {
        if (! class_exists($alias)) {
            eval('abstract class ' . $alias . ' extends ' . $original . ' {}');
        }
    }
}

function M($table, $dbClusterId = NULL)
{
    
    if (preg_match('@^\w+$@', $table)) {
        $modelClass = 'Model_' . camelize($table);

        if (class_exists($modelClass)) {
            return Factory::$f->$modelClass;
        }
    }

    if (!$dbClusterId && defined('DEFAULT_DB_CLUSTER_ID')) {
        $dbClusterId = DEFAULT_DB_CLUSTER_ID;
    }

    if (!$dbClusterId) {
        return NULL;
    }

    return Factory::$f->Db_Model($table, $dbClusterId);
}

/**
 * d default value, if the check value is false then return the default value.
 * 
 * @param mixed $checkValue 
 * @param mixed $defaultValue 
 * @access public
 * @return void
 */
function d($checkValue, $defaultValue)
{
    return empty($checkValue) ? $defaultValue : $checkValue;
}

/**
 * h htmlspecialchars 
 * 
 * @param mixed $str 
 * @access public
 * @return void
 */
function h($str)
{
    return htmlspecialchars($str);
}

function tag_a($url, $title = '', $popup = FALSE, $attributes = '')
{
    if (! $title) {
        $title = $url;
    }
    if (! is_bool($popup)) {
        $attributes = $popup;
        $popup = NULL;
    }
    if ($popup) {
        $attributes .= ' target="_blank"';
    }
    return "<a href=\"$url\" $attributes>$title</a>";
}

function form_hash($name, $html = TRUE)
{
    $uid = isset($GLOBALS['myuid']) ? $GLOBALS['myuid'] : 0;
    $hash = sha1(date('Y-m-d') . $name . '-ITISASECRETKEY!' . "-$uid");
    if ($html) {
        return '<input type="hidden" name="' . FORM_HASH_NAME . '" value="' . $name . '"/>' . "\n" . '<input type="hidden" name="' . FORM_HASH . '" value="' . $hash . '"/>';
    }
    return $hash;
}

function check_form_hash($name)
{
    $hash = isset($_GET[FORM_HASH]) ? $_GET[FORM_HASH] : (isset($_POST[FORM_HASH]) ? $_POST[FORM_HASH] : '');
    $genHash = form_hash($name, FALSE);
    return $hash === $genHash;
}

function set_default_cookie($name, $value, $expires = 0)
{
    if (is_array($value)) {
        $value = json_encode($value);
    }
    $domain = $_SERVER['HTTP_HOST'];
    setcookie($name, $value, $expires, '/', $domain);
    $_COOKIE[$name] = $value;
}

function unset_default_cookie($name)
{
    $domain = $_SERVER['HTTP_HOST'];
    setcookie($name, '', time() - 3600 * 24 * 365, '/', $domain);
    unset($_COOKIE[$name]);
}
/**
 * 执行时间统计
 * Execution time statictis
 */
class ETS
{
    private static $starts = array();
    private static $warnTimes = array(
        STAT_ET_DB_CONNECT => 0.1,
        STAT_ET_DB_QUERY => 0.2,
        STAT_ET_MEMCACHE_CONNECT => 0.05,
        STAT_ET_REDIS => 0.05 
    );
    private static $names = array(
        STAT_ET_DB_CONNECT => 'DB_Connect',
        STAT_ET_DB_QUERY => 'DB_Query',
        STAT_ET_MEMCACHE_CONNECT => 'MEMCACHE_Connect',
        STAT_ET_REDIS => 'REDIS_Query' 
    );

    public static function start($name)
    {
        self::$starts[$name] = microtime(TRUE);
    }

    public static function end($name, $msg = '')
    {
        if (empty(self::$starts[$name])) {
            return FALSE;
        }
        $start = self::$starts[$name];
        $end = microtime(TRUE);
        $executeTime = $end - $start;
        if (isset(self::$warnTimes[$name])) {
            if ($executeTime > self::$warnTimes[$name]) {
                $log = 'ET:' . self::$names[$name] . ':' . $executeTime . ':' . $msg;
                log_message($log, LOG_ERR);
            }
        }
        return $executeTime;
    }
}

/**
 * 事件驱动函数
 */
/**
 * 触发事件
 * @name string 事件名
 * @params array 事件参数
 * @return void
 */
function trigger_event($name, $params)
{
    static $eventStack = array();
    /*
	if ($name != 'on_log_message' && $name != 'on_autoload') {
		log_message("trigger event $name start", LOG_DEBUG);
	}
	*/
    $ret = FALSE;
    $handlers = empty(SP_G::$events[$name]) ? array() : SP_G::$events[$name];
    $defaultHandler = "event_handler_{$name}";
    if (is_callable($defaultHandler)) {
        $handlers[] = array(
            'function' => $defaultHandler 
        );
    }
    if (empty($handlers)) {
        return $ret;
    }
    if (! is_array($params)) {
        $params = array(
            $params 
        );
    }
    if (isset($GLOBALS['__EVENT_NAME'])) {
        $eventStack[] = $GLOBALS['__EVENT_NAME'];
    }
    $GLOBALS['__EVENT_NAME'] = $name;
    foreach ( $handlers as $options ) {
        if (isset($options['file'])) {
            require_once ($options['file']);
        }
        $func = $options['function'];
        if (! empty($options['class'])) {
            $func = array(
                $options['class'],
                $func 
            );
        }
        $ret = ! ! call_user_func_array($func, $params) || $ret;
    }
    if (! empty($eventStack)) {
        $GLOBALS['__EVENT_NAME'] = array_pop($eventStack);
    } else {
        unset($GLOBALS['__EVENT_NAME']);
    }
    /*
	if ($name != 'on_log_message' && $name != 'on_autoload') {
		log_message("trigger event $name end", LOG_DEBUG);
	}
	*/
    return $ret;
}

/**
 * 注册事件
 * @param $name 事件名
 * @options array|string 事件处理函数定义, 如果是String, 'func' | 'class.func'
 * array(
 * 'class' => '类名，optional',
 * 'function' => '函数名, required,
 * 'file' => '引入文件, optional'
 * )
 */
function register_event($name, $options)
{
    if (is_string($options)) {
        if (strpos($options, '.') !== FALSE) {
            $segments = explode($options);
            $options = array(
                'class' => $segments[0],
                'function' => $segments[1] 
            );
        } else {
            $options = array(
                'function' => $options 
            );
        }
    }
    if (is_array($name)) {
        $events = $name;
    } else {
        $events = explode(',', $name);
    }
    foreach ( $events as $event ) {
        SP_G::$events[$event][] = $options;
    }
}

function get_event_name()
{
    return isset($GLOBALS['__EVENT_NAME']) ? $GLOBALS['__EVENT_NAME'] : NULL;
}

function getSignature($data, $secretKey)
{
    ksort($data);
    $str = '';
    foreach ( $data as $key => $value ) {
        $str .= "$key=$value";
    }
    $str .= $secretKey;
    return md5($str);
}

function get_signkey($params) {
    $str = '';
    if (is_array($params) && !empty($params)) {
        foreach ($params as $key=>$v) {
            $str .= $key.$v;
        }
    }
    return md5($str);
}

