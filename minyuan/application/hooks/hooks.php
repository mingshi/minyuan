<?php
function init_system()
{
    register_event('on_autoload', 'handle_on_autoload');
    register_event('on_load_class_succ', 'handle_on_load_class_succ');
    register_event('on_log_message', 'handle_on_log_message');
    class_alias('Factory', 'F');
    class_alias('Config', 'C');
}

//CI 框架的自动载入的类忽略
function handle_on_autoload($class)
{
    if (strpos($class, 'CI_') === 0 || strpos($class, 'MY_') === 0) {
        return TRUE;
    }
    return FALSE;
}

function handle_on_load_class_succ($class)
{
}

//CI　框架的log_message方法被自定义的log_message方法替换了，这里适配下
function handle_on_log_message($level, $msg = '')
{
    $levels = array(
        'debug' => LOG_DEBUG,
        'info'  => LOG_INFO,
        'error' => LOG_ERR,
    );
    if (isset($levels[$level])) {
        log_message($msg, $levels[$level]);
        return TRUE;
    }
}
