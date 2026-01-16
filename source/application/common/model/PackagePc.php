<?php
namespace app\common\model;
/**
 * 包裹模型
 * Class OrderAddress
 * @package app\common\model
 */
class PackagePc extends BaseModel
{
    protected $name = 'package_pc';

    protected $updateTime = false;
    public function getWxappId(){
        return self::$wxapp_id;
    }
}
