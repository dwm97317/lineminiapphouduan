<?php
namespace app\common\model;

/**
 * 货位数据模型
 * Class OrderAddress
 * @package app\common\model
 */
class ShelfUnitItem extends BaseModel
{
    protected $name = 'shelf_unit_item';
    protected $updateTime = false;
    
    
    
     /**
     * 关联货架表
     * @return \think\model\relation\HasMany
     */
    public function shelfunit()
    {
        return $this->hasOne('ShelfUnit','shelf_unit_id','shelf_unit_id');
    }

}
