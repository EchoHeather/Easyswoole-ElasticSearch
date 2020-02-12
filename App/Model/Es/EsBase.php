<?php
namespace App\Model\Es;
use EasySwoole\Core\Component\Di;
class EsBase
{
    public $esClient = null;
    public function __construct() {
        $this->esClient = Di::getInstance()->get('ElasticSearch');
    }

    /**
     * ElasticSearch search
     * @param $name
     * @param int $from
     * @param int $size
     * @param string $type
     * @return mixed
     * @throws \Exception
     */
    public function searchByName($name, $from = 0, $size = 10, $type = 'match') {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => [
                'query' => [
                    $type =>[
                        'name' => $name
                    ]
                ],
                'from' => $from,
                'size' => $size
            ]
        ];
        try{
            $res = $this->esClient->search($params);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return $res;
    }
}