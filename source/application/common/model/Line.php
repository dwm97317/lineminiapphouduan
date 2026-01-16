<?php
namespace app\common\model;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Line extends BaseModel
{
    protected $name = 'Line';
    protected $updateTime = false;
    
     /**
     * 关联封面图
     * @return \think\model\relation\HasOne
     */
    public function image()
    {
        return $this->hasOne('uploadFile', 'file_id', 'image_id');
    }

}
