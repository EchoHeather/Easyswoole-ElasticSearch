<?php
namespace App\Lib\Upload;
use App\Lib\Utils;
class Base
{
    public $type = '';
    public function __construct($request, $type = null){
        $this->request = $request;
        if(empty($type)) {
            $files = $this->request->getSwooleRequest()->files;
            $type = array_keys($files)[0];
            $this->type = $type;
        }else{
            $this->type = $type;
        }

    }

    /**
     * 上传公共方法
     * @return bool
     * @throws \Exception
     */
    public function upload(){
        if($this->type != $this->fileType){
            return false;
        }
        //拿到文件
        $files =  $this->request->getUploadedFile($this->type);
        //判断文件大小
        $this->size = $files->getSize();
        $this->checkSize();
        //文件  类型/格式
        $this->clientMediaType = $files->getClientMediaType();
        $this->checkMediaType();
        //文件全称
        $fileName = $files->getClientFilename();
        $getFile = $this->getFile($fileName);
        $flag = $files->moveTO($getFile);
        if(!empty($flag)) {
            return $this->file;
        }
        return false;

    }

    /**
     * 检测文件格式
     * @return bool
     * @throws \Exception
     */
    public function checkMediaType(){
        //分离格式
        $clientMediaType = explode('/', $this->clientMediaType);
        $clientMediaType = $clientMediaType[1] ?? '';
        if(empty($clientMediaType)) {
            throw new \Exception("上传{$this->type}文件不合法");
        }
        //判断格式
        if(!in_array($clientMediaType, $this->fileExtTypes)){
            throw new \Exception("上传{$this->type}文件类型不合法");
        }
        return true;
    }

    /**
     * 生成文件
     * @param $fileName
     * @return string
     */
    public function getFile($fileName) {
        $pathinfo = pathinfo($fileName);
        $extension = $pathinfo['extension'];

        $dirName = "/".$this->type . "/". date("Y") . "/" . date("m");
        $dir = EASYSWOOLE_ROOT  . "/webroot" . $dirName;
        if(!is_dir($dir)) {
            mkdir($dir, 0777 , true);
        }

        $basename = "/" .Utils::getFileKey($fileName) . ".".$extension;

        $this->file = $dirName . $basename;
        return $dir  . $basename;
    }

    /**
     * 判断文件size
     * @return bool
     */
    public function checkSize(){
        if(empty($this->size)){
            return false;
        }
        //TODO判断大小
        //....
    }
}