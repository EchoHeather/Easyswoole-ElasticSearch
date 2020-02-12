<?php

namespace App\HttpController\Api;

use App\HttpController\Api\Base;
use EasySwoole\Core\Component\Di;
use App\Lib\Redis\Redis;
use App\Model\Video;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Component\Cache\Cache;
use App\Lib\Cache\Video as videoCache;
class Index extends Base
{

    /**
     * 方法一首页直接读取mysql
     * @return mixed
     */
    public function lists_1() {
        $condition = [];
        if(!empty($this->params['cat_id'])) {
            $condition['cat_id'] = intval($this->params['cat_id']);
        }
        try{
            $videoModel = new Video();
            $data = $videoModel->getVideoData($condition, $this->params['page'], $this->params['size']);
        }catch (\Exception $e){
            Logger::getInstance()->log('sql错误:'. $e->getMessage());
            return $this->writeJson(Status::CODE_BAD_REQUEST, '服务异常');
        }
        if(!empty($data['lists'])) {
            foreach($data['lists'] as &$list) {
                $list['create_time'] = date("Y-m-d H:i:s", $list['create_time']);
                $list['video_duration'] = gmstrftime('%H:%M:%S', $list['video_duration']);
            }
        }
        return $this->writeJson(Status::CODE_OK, 'OK', $data);
    }

    /**
     * 定时逻辑App\Lib\Cache\Video\setIndexVideo()
     * 方法二首页读取定时文件并分页
     * 方法三读取内存方法swoole_serialize已废弃
     * 方法四读取redis
     * @return mixed
     */
    public function lists_2() {
        $catId = !empty($this->params['cat_id']) ? intval($this->params['cat_id']) : 0;
        try{
            $videoData = ( new videoCache() )->getCache($catId);
        }catch (\Exception $e){
            return $this->writeJson(Status::CODE_BAD_REQUEST, '请求失败');
        }

        $count = count($videoData);
        return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingData($count, $videoData));
    }

    public function getVideo (){
        $db = Di::getInstance()->get('MYSQL');
        $data = $db->get('pu');
        return $this->writeJson('200', 'ok', $data);
    }

    public function getRedis (){
        $redis = Redis::getInstance()->get('name');
        return $this->writeJson('200', 'ok', $redis);
    }

    public function yaconf(){
        $res = \Yaconf::get('redis');
        return $this->writeJson('200', 'ok', $res);
    }

}
