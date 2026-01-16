<?php

namespace app\common\model\store\shop;

use app\common\model\BaseModel;

/**
 * 加盟商结算单模型
 * Class Apply
 * @package app\common\model\dealer
 */
class Withdraw extends BaseModel
{
    protected $name = 'store_shop_withdraw';

    /**
     * 关联仓库表
     * @return \think\model\relation\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo('app\common\model\store\Shop');
    }

    /**
     * 结算单详情
     * @param $id
     * @return Apply|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return self::get($id);
    }
    
}