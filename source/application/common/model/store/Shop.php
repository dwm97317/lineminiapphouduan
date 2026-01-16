<?php

namespace app\common\model\store;

use app\common\model\BaseModel;
use app\common\model\Region as RegionModel;
use app\common\model\store\shop\Capital;
/**
 * 商家门店模型
 * Class Shop
 * @package app\common\model\store
 */
class Shop extends BaseModel
{
    protected $name = 'store_shop';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region'];

    /**
     * 关联文章封面图
     * @return \think\model\relation\HasOne
     */
    public function logo()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\UploadFile", 'logo_image_id','file_id');
    }
    
    public function user(){
        $module = self::getCalledModule() ?: 'common';
        return $this->belongsTo("app\\{$module}\\model\\User",'user_id');
    }
    
    /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function shelf()
    {
        $module = self::getCalledModule() ?: 'common';
        return $this->hasMany("app\\{$module}\\model\\Shelf",'ware_no');
    }
    
    /**
     * 发放加盟商佣金
     * @param $user_id
     * @param $money
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public static function grantMoney($shop_id,$money,$type,$id)
    {
        // 分销商详情
        $model = static::detail($shop_id);
        if (!$model || $model['is_delete']) {
            return false;
        }
        // 累积分销商可提现佣金
        $model->setInc('income', $money);
        // 记录分销商资金明细
        Capital::add([
            'shop_id' => $shop_id,
            'inpack_id' => $id,
            'flow_type' => $type,
            'money' => $money,
            'describe' => '订单'.$id.'佣金结算',
            'wxapp_id' => $model['wxapp_id'],
        ]);
        return true;
    }

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        return [
            'province' => RegionModel::getNameById($data['province_id']),
            'city' => RegionModel::getNameById($data['city_id']),
            'region' => $data['region_id'] == 0 ? '' : RegionModel::getNameById($data['region_id']),
        ];
    }
    /**
     * 仓库名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getShopNameAttr($value)
    {
        strlen($value)>30 && $value = substr($value,0,30).'...';
        return $value;
    }

    /**
     * 门店详情
     * @param $shop_id
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($shop_id)
    {
        return static::get($shop_id, ['logo','user']);
    }

}