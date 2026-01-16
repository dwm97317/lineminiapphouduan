<?php
namespace app\api\model;

use app\common\model\PackageService as PackageServiceModel;

/**
 * 包裹图片模型
 * Class GoodsImage
 * @package app\store\model
 */
class PackageService extends PackageServiceModel
{
    public static  function detail($id){
        return (new static()) ->find($id);
    }
}
