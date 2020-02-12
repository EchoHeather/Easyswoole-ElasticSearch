<?php

namespace App\HttpController\Api;
USE EasySwoole\Core\Http\Message\Status;
use App\Model\Es\EsVideo;
class Search extends Base
{
    //搜索方法
    public function index() {
        $keyword = trim($this->params['keyword']);
        if(empty($keyword)) {
            return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingData(0, [], 0));
        }
        $esObj = new EsVideo();
        $res = $esObj->searchByName($keyword,$this->params['from'], $this->params['size']);
        if(empty($res)) {
            return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingData(0, [], 0));
        }
        $hits = $res['hits']['hits'];
        $total = $res['hits']['total'];
        if(empty($total)) {
            return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingData(0, [], 0));
        }
        foreach($hits as $hit) {
            $source = $hit['_source'];
            $resData[] = [
                'id' => $hit['_id'],
                'name' => $source['name'],
                'image' => $source['image'],
                'uploader' => $source['uploader'],
                'create_time' => $source['create_time'],
                'video_duration' => $source['video_duration'],
                'keywords' => [$keyword],
            ];
        }
        return $this->writeJson(Status::CODE_OK, 'OK', $this->getPagingData($total, $resData, 0));
    }
}
