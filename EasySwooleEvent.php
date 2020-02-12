<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;
use \EasySwoole\Core\Component\Di;
use EasySwoole\Core\Utility\File;
use App\Lib\Redis\Redis;
use App\Lib\process\ConsumerTest;
use App\Lib\Cache\Video as videoCache;
use EasySwoole\Core\Swoole\Process\ProcessManager;
use EasySwoole\Core\Component\Crontab\CronTab;
use EasySwoole\Core\Swoole\Time\Timer;
use App\Model\Es\EsClient as Es;
Class EasySwooleEvent implements EventInterface {

    public static function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        // 载入项目 Conf 文件夹中所有的配置文件
        self::loadConf(EASYSWOOLE_ROOT . '/Config');
    }

    public static function loadConf($ConfPath)
    {
        $Conf  = Config::getInstance();
        $files = File::scanDir($ConfPath);
        foreach ($files as $file) {
            $data = require_once $file;
            $Conf->setConf(strtolower(basename($file, '.php')), (array)$data);
        }
    }
    public static function mainServerCreate(ServerManager $server,EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.
        //Mysql
        Di::getInstance()->set('MYSQL',\MysqliDb::class,Array (
                'host' => '127.0.0.1',
                'username' => 'root',
                'password' => 'root',
                'db'=> 'test',
                'port' => 3306,
                'charset' => 'utf8')
        );
        //Redis
        Di::getInstance()->set('REDIS', Redis::getInstance());
        //ElasticSearch
//        Di::getInstance()->set('ElasticSearch', Es::getInstance());
        //注册消费进程
        $allNum = 3;
        for ($i = 0 ;$i < $allNum;$i++){
            ProcessManager::getInstance()->addProcess("consumer_test_{$i}",ConsumerTest::class);
        }
        //定时存储mysql到json文件
        $videoCacheObj = new videoCache();
//        CronTab::getInstance()->addRule('Heather', "*/1 * * * *", function() use($videoCacheObj){
//            $videoCacheObj->setIndexVideo();
//        });
        //计时器必须是worker进程启动后执行
        $register->add(EventRegister::onWorkerStart, function(\swoole_server $server, $workerId) use($videoCacheObj){
            if($workerId == 0) {
                Timer::loop(2*1000, function() use($videoCacheObj) {
                    $videoCacheObj->setIndexVideo();
                });
            }
        });

    }

    public static function onRequest(Request $request,Response $response): void
    {
        // TODO: Implement onRequest() method.
    }

    public static function afterAction(Request $request,Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}