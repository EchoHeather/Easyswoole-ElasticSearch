<?php

namespace App\HttpController;
use App\Lib\AliyunSdk\AliVod;
use EasySwoole\Core\Http\AbstractInterface\Controller;
use Elasticsearch\ClientBuilder;
use EasySwoole\Core\Http\Message\Status;
use App\Model\Es\EsClient;
use App\Model\Es\EsVideo;
use EasySwoole\Core\Component\Di;

/**
 * Class Index. 
 * @package App\HttpController
 */
class Index extends Controller
{
    /**
     * 首页方法
     */
    public function index()
    {   
        //测试ElasticSearch
        $params = [
            'index' => 'video',
            'type'  => "_doc",
            'id'    => 1
        ];
        try{
            $client = Di::getInstance()->get('ElasticSearch');
            $res = $client->get($params);
        }catch (\Exception $e){
            $this->writeJson(Status::CODE_BAD_REQUEST, $e->getMessage());
        }
        $this->writeJson(Status::CODE_OK, 'ok', $res);
    }

    public function demo() {
        $name = $this->request()->getRequestParam('name');
        $cli = new EsVideo();
        $res = $cli->searchByName($name, 'match');
        print_r($res);
    }

    /**
     * 测试阿里云OSS上传
     * @throws \Exception
     */
    public function testAli() {
        $obj = new AliVod();
        $title = "111";
        $videoName = "1.mp4";
        $res = $obj->createUploadVideo($title, $videoName);
        $uploadAddress = json_decode(base64_decode($res->UploadAddress), true);
        $uploadAuth = json_decode(base64_decode($res->UploadAuth), true);
        $obj->initOssClient($uploadAddress, $uploadAuth);
        $res = $obj->uploadLocalFile($uploadAddress, 'q.mp4');
        print_r($res);
    }
}
