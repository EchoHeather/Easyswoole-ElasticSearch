<?php
namespace App\Lib\process;
use EasySwoole\Core\Swoole\Process\AbstractProcess;
use Swoole\Process;
use \EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\Logger;
class ConsumerTest extends AbstractProcess
{
    private $isRun = false;
    public function run(Process $process)
    {
        // TODO: Implement run() method.
        /*
         * 举例，消费redis中的队列数据
         * 定时500ms检测有没有任务，有的话就while死循环执行
         */
        $this->addTick(500,function (){
            if(!$this->isRun){
                $this->isRun = true;
                while (true){
                    try{
                        $task = Di::getInstance()->get('REDIS')->lPop('list_test');
                        if($task){
                            // do you task
                            Logger::getInstance()->log($this->getProcessName()."---".$task);
                        }else{
                            break;
                        }
                    }catch (\Throwable $throwable){
                        break;
                    }
                }
                $this->isRun = false;
            }
//            var_dump($this->getProcessName().' task run check');
        });
    }
    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }
    public function onReceive(string $str, ...$args)
    {
        // TODO: Implement onReceive() method.
    }
}