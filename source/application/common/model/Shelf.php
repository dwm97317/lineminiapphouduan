<?php
namespace app\common\model;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Shelf extends BaseModel
{
    protected $name = 'shelf';
    protected $updateTime = false;
    
     /**
     * 关联货架表
     * @return \think\model\relation\HasMany
     */
    public function shelf()
    {
        return $this->hasOne('Shelf','shelf_id','shelf_id');
    }
    
    /**
     * 关联货架表
     * @return \think\model\relation\HasMany
     */
    public function shelfunit()
    {
        return $this->HasMany('ShelfUnit','shelf_id','id');
    }

}
