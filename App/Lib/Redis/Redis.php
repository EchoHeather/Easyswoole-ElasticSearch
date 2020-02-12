<?php
namespace App\Lib\Redis;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Config;
class Redis
{
    //复制单例模式代码
    use Singleton;
    public $redis = '';
    //自执行 new Redis
    private function __construct(){
        if(!extension_loaded('redis')) {
            throw new \Exception('redis.so文件不存在');
        }
        try{
            $redisConf = \Yaconf::get('redis');
            $this->redis = new \Redis();
            $res = $this->redis->connect($redisConf['host'], $redisConf['port'], $redisConf['time_out']);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
        if($res === false) {
            throw new \Exception('redis连接失败');
        }
    }

    /**
     * Redis get
     * @param $key
     * @return bool|string
     */
    public function get($key) {
        if(empty($key)) {
            return '';
        }
        return $this->redis->get($key);
    }

    /**Redis set
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key, $value, $time = 0) {
        if(empty($key)) {
            return '';
        }
        if(is_array($value)) {
            $value = json_encode($value);
        }
        if(!$time) {
            return $this->redis->set($key, $value);
        }
        return $this->redis->setex($key, $time, $value);
    }

    /**
     * Redis lPop
     * @param $key
     * @return string
     */
    public function lPop($key) {
        if(empty($key)) {
            return '';
        }
        return $this->redis->lPop($key);
    }

    /**
     * Redis rPush
     * @param $key
     * @param $value
     * @return int|string
     */
    public function rPush($key, $value) {
        if(empty($key)) {
            return '';
        }
        return $this->redis->rPush($key, $value);
    }

    /**
     * Redis zIncrBy
     * @param $key
     * @param $number
     * @param $member
     * @return bool|float
     */
    public function zincrby($key, $number, $member) {
        if(empty($key) || empty($member)) {
            return false;
        }
        return $this->redis->zIncrBy($key, $number, $member);
    }

    /**
     * Redis zrevrange
     * @param $key
     * @param $start
     * @param $stop
     * @param $type
     * @return array|bool
     */
    public function zrevrange($key, $start, $stop, $type) {
        if(empty($key)) {
            return false;
        }
        return $this->redis->zrevrange($key, $start, $stop, $type);
    }

    /**
     * php匿名函数 方法不存在直接调用Redis函数
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments) {
        return $this->redis->$name(...$arguments);
    }
}