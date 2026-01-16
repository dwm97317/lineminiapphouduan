<?php
namespace app\web\model;
use app\web\model\User;
use app\common\model\BuyerBind as BuyerBindModel;

use think\Db;
/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class BuyerBind extends BuyerBindModel
{
    protected $name = 'buyer_bind';
    protected $updateTime = false;

    public function saveData($data){
        $data = array_merge($data,['wxapp_id'=>self::$wxapp_id]);
        return $this->save($data);
    }
}
