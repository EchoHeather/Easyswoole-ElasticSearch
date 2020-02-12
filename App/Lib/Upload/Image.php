<?php
namespace App\Lib\Upload;
use App\Lib\Upload\Base;
class Image extends Base
{
    public $fileType = 'image';
    public $maxSize  = 122;
    public $fileExtTypes = [
        'jpg',
        'png',
        'jpeg'
    ];
}
