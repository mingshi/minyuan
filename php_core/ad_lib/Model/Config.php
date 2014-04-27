<?php 
class Model_Config extends Db_Model
{
    public function __construct()
    {
        parent::__construct('config', 'hzeng_backend');
    }

    public static function get($name, $default = array())
    {
        $row = Factory::$f->Model_Config->selectOne(array('name' => $name));

        if ($row) {

            return json_decode($row['content'], TRUE);
        }

        return $default;
    }

    public static function set($name, $value, $ext = array()) 
    {
        $ins = array_merge(array(
            'name' => $name,
            'content' => json_encode($value),
        ), $ext);

        return Factory::$f->Model_Config->insertReplace($ins, $ins);
    }
}
