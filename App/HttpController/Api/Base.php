<?php

namespace App\HttpController\Api;

use EasySwoole\Core\Http\AbstractInterface\Controller;

class Base extends Controller
{
    //get or post 参数
    public $params;
    /**
     * 首页方法
     */
    public function index()
    {
        $data = [
            'id' => 1,
            'name' => 'echo-heather'
        ];
        return $this->writeJson('200', 'ok', $data);
    }

    /**
     * 获取分页逻辑
     * @param $count
     * @param $data
     * @param int $isSplice
     * @return array
     */
    public function getPagingData($count, $data, $isSplice = 1){
        $totalPage = ceil($count/$this->params['size']);
        $maxPageSize = \Yaconf::get('base.maxPageSize');
        if($totalPage > $maxPageSize) {
            $totalPage = $maxPageSize;
        }
        $data = $data ?? [];
        if($isSplice == 1) {
            $data = array_splice($data, $this->params['from'], $this->params['size']);
        }
        return [
            'total_page' => $totalPage,
            'page_size'  => $this->params['page'],
            'count'      => intval($count),
            'lists'      => $data
        ];
    }

    /**
     * 权限相关
     * @param $action
     * @return bool
     */
    public function onRequest($action):?bool
    {
        $this->getParams();
        return true;
    }

    //获取GET或POST的参数
    public function getParams() {
            $params = $this->request()->getRequestParam();
            $params['page'] = !empty($params['page']) ? intval($params['page']) : 1;
            $params['size'] = !empty($params['size']) ? intval($params['size']) : 5;
            $params['id'] = !empty($params['id']) ? intval($params['id']) : 0;
            $params['keyword'] = !empty($params['keyword']) ?? [];
            $params['from'] = ($params['page'] -1) * $params['size'];
            $this->params = $params;

    }
//    public function onException(\Throwable $throwable,$actionName):void
//    {
//        $this->writeJson('400', '请求不合法');
//    }

    //重写父类writeJson方法
    protected function writeJson($statusCode = 200,$msg = null,$result = null){
        if(!$this->response()->isEndResponse()){
            $data = Array(
                "code"=>$statusCode,
                "result"=>$result,
                "msg"=>$msg
            );
            $this->response()->write(json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type','application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        }else{
            trigger_error("response has end");
            return false;
        }
    }

}
