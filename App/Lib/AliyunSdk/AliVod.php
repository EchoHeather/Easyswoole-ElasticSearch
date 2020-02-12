<?php
namespace App\Lib\AliyunSdk;
require_once EASYSWOOLE_ROOT.'/App/Lib/AliyunSdk/aliyun-php-sdk-core/Config.php';   // 假定您的源码文件和aliyun-php-sdk处于同一目录
require_once EASYSWOOLE_ROOT.'/App/Lib/AliyunSdk/aliyun-oss-php-sdk/autoload.php';
use vod\Request\V20170321 as vod;
use OSS\OssClient;
use OSS\Core\OssException;
class AliVod
{
    public $regionId = 'cn-shanghai';  // 点播服务接入区域
    public $client;
    public $ossClient;
    //初始化VOD客户端
    public function __construct() {
        $profile = \DefaultProfile::getProfile($this->regionId, \Yaconf::get("aliyun.accessKeyId"), \Yaconf::get("aliyun.accessKeySecret"));
        $this->client = new \DefaultAcsClient($profile);
    }

    /**
     * 获取视频上传地址和凭证
     * @param $title
     * @param $vedioFileName
     * @param array $other
     * @return mixed|\SimpleXMLElement
     * @throws \Exception
     */
    public function createUploadVideo($title, $vedioFileName, $other = []) {
        $request = new vod\CreateUploadVideoRequest();
        $request->setTitle($title);        // 视频标题(必填参数)
        $request->setFileName($vedioFileName); // 视频源文件名称，必须包含扩展名(必填参数)
        if(!empty($other['description'])) {
            $request->setDescription("视频描述");  // 视频源文件描述(可选)
        }
        if(!empty($other['coverURL'])) {
            $request->setCoverURL("http://img.alicdn.com/tps/TB1qnJ1PVXXXXXCXXXXXXXXXXXX-700-700.png"); // 自定义视频封面(可选)
        }
        if(!empty($other['tags'])) {
            $request->setTags("标签1,标签2"); // 视频标签，多个用逗号分隔(可选)
        }
        $res = $this->client->getAcsResponse($request);
        if(empty($res) || empty($res->VedioId)) {
            throw new \Exception("获取上传凭证不合法");
        }
        return $res;
    }

    /**
     * 使用上传凭证和地址初始化OSS客户端
     * @param $uploadAuth
     * @param $uploadAddress
     * @return OssClient
     */
    public function initOssClient($uploadAuth, $uploadAddress) {
        $this->ossClient = new OssClient($uploadAuth['AccessKeyId'], $uploadAuth['AccessKeySecret'], $uploadAddress['Endpoint'],
            false, $uploadAuth['SecurityToken']);
        $this->ossClient->setTimeout(\Yaconf::get("aliyun.timeOut"));    // 设置请求超时时间，单位秒，默认是5184000秒, 建议不要设置太小，如果上传文件很大，消耗的时间会比较长
        $this->ossClient->setConnectTimeout(\Yaconf::get("aliyun.connectTimeout"));  // 设置连接超时时间，单位秒，默认是10秒
        return $this->ossClient;
    }

    /**
     * 上传本地文件
     * @param $uploadAddress
     * @param $localFile
     * @return mixed
     */
    public function uploadLocalFile($uploadAddress, $localFile) {
        return $this->ossClient->uploadFile($uploadAddress['Bucket'], $uploadAddress['FileName'], $localFile);
    }
}