<?php
namespace app\common\model;

/**
 * 包裹项目模型
 * Class OrderAddress
 * @package app\common\model
 */
class PackageItem extends BaseModel
{
    protected $name = 'package_item';
    protected $updateTime = false;

    public function getListAllItem(){
        return $this->select();
    }

}
