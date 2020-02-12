<?php

namespace App\HttpController\Api;

use App\HttpController\Api\Base;
use App\Lib\ClassArr;
/**
 * 上传逻辑
 * Class Upload
 * @package App\HttpController\Api
 */
class Upload extends Base
{

    public function file (){
        $request = $this->request();
        $files = $request->getSwooleRequest()->files;
        $type = array_keys($files)[0];
        if(empty($type)) {
            return $this->writeJson(400, '上传文件不合法');
        }
        try{
            $ClassObj = new ClassArr();
            $uploadClassStat = $ClassObj->uploadClassStat();
            $initClass = $ClassObj->initClass($type, $uploadClassStat, [$request, $type]);
            $file = $initClass->upload();
        }catch (\Exception $e){
            return $this->writeJson(400, $e->getMessage(), []);
        }
        if(empty($file)) {
            return $this->writeJson(400, '上传失败', []);
        }
        $data = [
            'file' => $file
        ];
        return $this->writeJson(200, 'ok', $data);
    }


}
