<?php
namespace App\Model\Es;
use EasySwoole\Core\AbstractInterface\Singleton;
use Elasticsearch\ClientBuilder;
//ElasticSearch 配置
class EsClient
{
    use Singleton;
    public $esClient = '';
    private function __construct() {
        try{
            $this->esClient = ClientBuilder::create()->setHosts([\Yaconf::get("elasticsearch.host")])->build();
        }catch (\Exception $e){
            throw new \Exception('ElasticSearch内部异常');
        }
        if(empty($this->esClient)) {
            throw new \Exception('ElasticSearch未发现索引');
        }
    }


    public function __call($name, $arguments) {
        return $this->esClient->$name(...$arguments);
    }
}