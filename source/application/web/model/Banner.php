<?php
namespace app\web\model;
use app\common\model\Banner as BannerModel;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Banner extends BannerModel
{
    protected $updateTime = false;

    public function queryPage(){
        return $this->where(['status'=>1])->order('sort DESC')->field('title,image_id,redirect_type,url')->limit(6)->select();
    }
}
