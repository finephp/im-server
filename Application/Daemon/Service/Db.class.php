<?php
namespace Daemon\Service;
class Db{
    public $db;
    static $_models = array();
    /**
     * @param $name
     * @return MongoModel | bool
     */
    static function MongoModel($name,$config = array()){
        try {
            if(empty($config)){
                $config = C('DB_CONFIG_MONGO');
            }
            $dbName = $config['DB_NAME'];
            //获取缓存
            $_models = & self::$_models;
            if(isset($_models[$dbName.'.'.$name])) {
                return $_models[$dbName.'.'.$name];
            }
            $_models[$dbName.'.'.$name] = $model = new MongoModel($dbName.'.'.$name, '', $config);
            return $model;
        }catch(\Exception $e){
            self::E(__METHOD__.":connect MongoDb error:".$e->getMessage());
            print_r(C('DB_CONFIG_MONGO'));
            return false;
        }
    }
    static function E($msg){
        echo (colorize($msg."\r\n",'FAILURE'));
        //return false;
    }
}
//自定义mongo类
class MongoModel extends \Think\Model\MongoModel{
    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     * @access public
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name='',$tablePrefix='',$connection='') {
        $this->_auto = array(
            // 新增时填充时间戳
            array('createdAt',new \MongoDate(),self::MODEL_INSERT),
            array('updatedAt',new \MongoDate(),self::MODEL_BOTH),
        );
        parent::__construct($name,$tablePrefix,$connection);
        $this->trueTableName = $this->name;
    }
}