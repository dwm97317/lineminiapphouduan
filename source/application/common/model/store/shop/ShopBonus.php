<?php

namespace app\common\model\store\shop;

use app\common\model\BaseModel;

/**
 * 仓库申请加盟模型
 * Class Clerk
 * @package app\common\model\store
 */
class ShopBonus extends BaseModel
{
    protected $name = 'store_shop_bonus';
    protected $updateTime = false;
    
    
    /**
     * 分红详情
     * @param $bonus_id
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($bonus_id)
    {
        return static::get($bonus_id,['shop','line']);
    }
    
    /**
     * 关联仓库表
     * @return \think\model\relation\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo('app\\store\\model\store\\Shop');
    }
    
    public function service()
    {
        return $this->belongsTo('app\\store\\model\\PackageService','line_id');
    }

    /**
     * 关联路线表
     * @return \think\model\relation\BelongsTo
     */
    public function line()
    {
        return $this->belongsTo('app\\store\\model\Line');
    }
    
}