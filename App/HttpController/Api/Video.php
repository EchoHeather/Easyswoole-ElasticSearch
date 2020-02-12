<?php

namespace App\HttpController\Api;
use App\Model\Video as VideoModel;
use EasySwoole\Core\Http\Message\Status;
use EasySwoole\Core\Utility\Validate\Rule;
use EasySwoole\Core\Utility\Validate\Rules;
use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Swoole\Task\TaskManager;
use EasySwoole\Core\Component\Di;
class Video extends Base
{
    public $logType = 'video:';

    public function index() {
        $id = intval($this->params['id']);
        if(empty($id)) {
            return $this->writeJson(Status::CODE_BAD_REQUEST, '请求不合法');
        }
        try{
            $video = (new VideoModel())->getById($id);
        }catch (\Exception $e) {
            Logger::getInstance()->log('sql_videomodel_getById_error:'.$e->getMessage());
            return $this->writeJson(Status::CODE_BAD_REQUEST, '请求不合法');
        }
        if(!$video || $video['status'] != \Yaconf::get("status.normal")) {
            return $this->writeJson(Status::CODE_BAD_REQUEST, '该视频不存在');
        }
        $video['video_duration'] = gmstrftime('%H:%M:%S', $video['video_duration']);
        //播放数统计逻辑
        //投放异步task任务
        TaskManager::async(function() use($id){
            //可以添加日、周、月、年、总等多个有序集合 格式为key=名字+相应的日期
            Di::getInstance()->get("REDIS")->zincrby(\Yaconf::get('redis.video_play_key'), 1, $id);
        });
        return $this->writeJson(Status::CODE_OK, 'ok', $video);
    }

    //排行榜接口  日排行  周排行 月 总排行
    public function rank($type) {
        switch($type) {
            case "all":
                $result = Di::getInstance()->get("REDIS")->zrevrange(\Yaconf::get('redis.video_play_key'), 0, -1, "withscores");
                break;
            case "day":
                //....todo
                $result = [];
                break;
            case "week":
                //....todo
                $result = [];
                break;
            case "mouth":
                //....todo
                $result = [];
                break;
            case "year":
                //....todo
                $result = [];
                break;
            default:
                $result = [];
                break;
        }
        return $this->writeJson(Status::CODE_OK, 'ok', $result);

    }

    public function add() {
        $params = $this->request()->getRequestParam();
        Logger::getInstance()->log($this->logType. "add:" .json_encode($params));
        $ruleObj = new Rules();
        $ruleObj->add('name','视频名称错误')->withRule(Rule::REQUIRED)->withRule(Rule::MIN_LEN, 2)->withRule(Rule::MAX_LEN, 20);
        $ruleObj->add('url','视频地址错误')->withRule(Rule::REQUIRED);
        $ruleObj->add('image','图片地址错误')->withRule(Rule::REQUIRED);
        $ruleObj->add('content','视频描述错误')->withRule(Rule::REQUIRED);
        $ruleObj->add('cat_id','栏目ID错误')->withRule(Rule::REQUIRED);
        $validate = $this->validateParams($ruleObj);
        if($validate->hasError()){
            return $this->writeJson(Status::CODE_BAD_REQUEST, $validate->getErrorList()->first()->getMessage());
        }
        $data = [
            'name' => $params['name'],
            'url'  => $params['url'],
            'image'=> $params['image'],
            'content'=> $params['content'],
            'cat_id'=> $params['cat_id'],
            'create_time'=> time(),
            'uploader' => 'cc',
            'status'=> \Yaconf::get('status.normal'),
        ];
        try{
            $modelObj = new VideoModel();
            $videoId  = $modelObj->add($data);
        }catch (\Exception $e) {
            return $this->writeJson(Status::CODE_BAD_REQUEST, $e->getMessage());
        }
        if(!empty($videoId)){
            return $this->writeJson(Status::CODE_OK, 'ok', ['id' => $videoId]);
        }else{
            return $this->writeJson(Status::CODE_BAD_REQUEST, '提交视频有误', ['id' => 0]);
        }

    }
}
