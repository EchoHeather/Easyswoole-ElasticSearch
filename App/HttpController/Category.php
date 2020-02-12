<?php

namespace App\HttpController;

use EasySwoole\Core\Http\AbstractInterface\Controller;

/**
 * Class Index. 
 * @package App\HttpController
 */
class Category extends Controller
{
    /**
     * 首页方法
     * @author : evalor <master@evalor.cn>
     */
    public function index()
    {
        
        $video = [
            'id' => 1,
            'name' => 'heather'
            
        ];
        return $this->writeJson(200, '请求成功', $video);
        
    }
}
