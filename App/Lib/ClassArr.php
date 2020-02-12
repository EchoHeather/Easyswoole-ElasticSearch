<?php
namespace App\Lib;
/**
 * 反射处理
 * Class ClassArr
 * @package App\Lib
 */
class ClassArr
{

    /**
     * 反射类配置 APP\Lib\Upload
     * @return array
     */
    public function uploadClassStat() {
        return [
            'image' => '\App\Lib\Upload\Image',
            'video' => '\App\Lib\Upload\Video'
        ];
    }

    public function initClass($type, $supportedClass, $params = [], $needInstance = true) {
        if(!array_key_exists($type, $supportedClass)) {
            return false;
        }
        $className = $supportedClass[$type];
        return $needInstance ? (new \ReflectionClass($className))->newInstanceArgs($params) : $className;
    }
}