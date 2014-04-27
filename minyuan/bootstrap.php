<?php
function error_report($errno, $msg, $smsMsg = '')
{
    log_message('error_report:'.$msg, LOG_ERR);

    //发送短信
    if (empty($smsMsg)) {
        $smsMsg = $msg;
    }

    alert_sms($smsMsg);
}

define('DEFAULT_DB_CLUSTER_ID', 'ad_core');
define('APP_PATH_LOG', dirname(__FILE__) . '/application/logs');
define('APP_PATH_CONF', dirname(__FILE__) . '/application/third_party/conf');
define('APP_PATH_LIB', implode(';', array(
    dirname(__FILE__) . '/application/third_party/lib',
    dirname(__FILE__) . '/../php_core/ad_lib',
)));
#define('APP_LOG_SCRIBE_CATEGORY', 'default');

require_once(dirname(__FILE__) . '/../php_core/bootstrap.php');

//读写主库
Db_Model::setForceReadOnMater();

sp_load_helper(array('array', 'http', 'parameter', 'string', 'date'));

Session::$config = array(
    'cookieName' => substr(ENV, 0, 1) . '_session',
//    'domain' => 'box.hzeng.net',
);

if (ENV == 'DEVELOPMENT') {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    ini_set('track_errors', 1);
} else {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
}

Util_Pagination::setDefaultOptions(array(
    'prefix' => '<ul>',
    'suffix' => '</ul>',
    'link_tag'  => '<li><a href="{url}" title="第{page}页">{page}</a></li>',
    'cur_link_tag' => '<li class="active"><a>{page}</a></li>',
    'prev_link_tag' => '<li><a href="{url}">«</a>',
    'no_prev_link_tag' => '<li class="disabled"><a>«</a>',
    'next_link_tag' => '<li><a href="{url}">»</a>',
    'no_next_link_tag' => '<li class="disabled"><a>»</a>',
    'more_tag' => '<li><a>..</a></li>',
));
