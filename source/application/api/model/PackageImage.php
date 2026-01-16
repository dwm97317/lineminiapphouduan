<?php
namespace app\api\model;

use app\common\model\PackageImage as PackageImageModel;

/**
 * 包裹图片模型
 * Class GoodsImage
 * @package app\store\model
 */
class PackageImage extends PackageImageModel
{
        /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'package_id',
        'image_id',
        'id',
        'file_name',
        'file_url'
    ];
}
