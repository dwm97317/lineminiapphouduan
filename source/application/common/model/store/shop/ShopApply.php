<?php

namespace app\common\model\store\shop;

use app\common\model\BaseModel;

/**
 * 仓库申请加盟模型
 * Class Clerk
 * @package app\common\model\store
 */
class ShopApply extends BaseModel
{
    protected $name = 'store_shop_apply';
    protected $updateTime = false;
    
    
    /**
     * 加盟商申请记录详情
     * @param $where
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        return self::get($where);
    }
    
        /**
     * 关联会员记录表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User");
    }
}