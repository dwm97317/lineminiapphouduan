<?php
namespace app\common\model;

/**
 * 包裹模型
 * Class OrderAddress
 * @package app\common\model
 */
class PackageService extends BaseModel
{
    protected $name = 'package_services';
    protected $updateTime = false;
    
    public static  function detail($id){
        return (new static()) ->find($id);
    }
}
