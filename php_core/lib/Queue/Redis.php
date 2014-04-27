<?php
require_once dirname(__FILE__) . '/Base.php';
class Queue_Redis implements IQueue
{
    public $name = 'doraemon';
    public $prefix = 'queue_';
    private $cache_prefix;

    public function __construct($config)
    {
        if (! empty($config['name']))
            $this->name = $config['name'];
        if (! empty($config['prefix']))
            $this->prefix = $config['prefix'];
        $this->cache_prefix = $this->prefix . $this->name . '_';
    }

    public function put($data)
    {
        $redis = Db_Redis::getInstance();
        if (! $redis) {
            return FALSE;
        }
        return $redis->lpush($this->cache_prefix, json_encode($data));
    }

    public function get()
    {
        $redis = Db_Redis::getInstance();
        if (! $redis) {
            return FALSE;
        }
        $task = $redis->rpop($this->cache_prefix);
        if ($task !== FALSE) {
            $task = @json_decode($task, TRUE);
        }
        return $task;
    }

    public function clear()
    {
        $redis = Db_Redis::getInstance();
        if (! $redis) {
            return FALSE;
        }
        $task = $redis->del($this->cache_prefix);
        return $task;
    }
}