<?php
namespace App\Lib\Cache;
use App\Model\Video as videoModel;
use EasySwoole\Core\Component\Cache\Cache;
use EasySwoole\Core\Component\Di;
class Video
{
    public function setIndexVideo() {
        $catIds = array_keys(\Yaconf::get('category.cats'));
        array_unshift($catIds, 0);
        $cacheType = \Yaconf::get("base.indexCacheType");
        $modelObj = new videoModel();
        foreach($catIds as $catId) {
            try{
                $condition = [];
                if(!empty($catId)) {
                    $condition['cat_id'] = $catId;
                }
                $data = $modelObj->getVideoCacheData($condition);
            }catch (\Exception $e){
                $data = [];
            }
            if(empty($data)) {
                continue;
            }
            foreach($data as &$list) {
                $list['create_time'] = date("Y-m-d H:i:s", $list['create_time']);
                $list['video_duration'] = gmstrftime('%H:%M:%S', $list['video_duration']);
            }
            switch ($cacheType) {
                case "file":
                    //方法一定时存储mysql到json文件 定时任务:根目录EasySwooleEvent.php->mainServerCreate()
                    $dir = $this->getVideoCatIdFile();
                    if(!is_dir($dir)) {
                        mkdir($dir, 0777 , true);
                    }
                    $flag = file_put_contents($dir."/".$catId.".json", json_encode($data));
                    break;
                case "cache":
                    //方法二写入swoole内置table 注：swoole_serialize已废弃
                    $flag = Cache::getInstance()->set($this->getCatKey($catId), $data);
                    break;
                case "redis":
                    //方法三 写入redis
                    $flag = Di::getInstance()->get('REDIS')->set($this->getCatKey($catId), json_encode($data));
                    break;
                default:
                    throw new \Exception("请求不合法");
                    break;
            }
            //TODO 可做报警发送短信、邮件等 并且记录日志
//            if(empty($flag)) {
//                echo "no";
//            }else{
//                echo "yes";
//            }
        }
    }

    public function getCache($cat_id = 0) {
        $cacheType = \Yaconf::get("base.indexCacheType");
        switch($cacheType) {
            case "file":
                $dir = $this->getVideoCatIdFile();
                $videoFile = $dir."/".$cat_id.".json";
                $videoFile = is_file($videoFile) ? file_get_contents($videoFile) : [];
                $videoData = !empty($videoFile) ? json_decode($videoFile, true) : [];
                break;
            case "cache": //easyswoole 2.x已废弃
                $videoData = Cache::getInstance()->get($this->getCatKey($cat_id));
                $videoData = !empty($videoData) ? $videoData : [];
                break;
            case "redis":
                $videoData = Di::getInstance()->get('REDIS')->get($this->getCatKey($cat_id));
                $videoData = !empty($videoData) ? json_decode($videoData, true) : [];
                break;
            default:
                throw new \Exception("请求不合法");
                break;
        }
        return $videoData;
    }

    //cat_id存储位置
    public function getVideoCatIdFile() {
        return EASYSWOOLE_ROOT."/webroot/video/json";
    }
    //cat_id Redis key
    public function getCatKey($catId = 0) {
        return "index_video_data_cat_id".$catId;
    }
}
