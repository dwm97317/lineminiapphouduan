<?php
namespace app\web\controller;

use app\web\model\store\Shop as ShopModel;
use app\web\model\UserAddress;

/**
 * 门店列表
 * Class Shop
 * @package app\web\controller
 */
class Shop extends Controller
{
    /**
     * 门店列表
     * @param string $longitude
     * @param string $latitude
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($longitude = '', $latitude = '')
    {
        $model = new ShopModel;
        $wxappId = $this->wxapp_id;
        $list = $model->getList();
        // dump($list);die;
        return  $this->fetch('order/warehouse',compact('list')); 
    }

    /**
     * 门店详情
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function detail($shop_id)
    {
        $detail = ShopModel::detail($shop_id);
        return $this->renderSuccess(compact('detail'));
    }
    
    /**
     * 自提点
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function pickuppoint(){
        $UserAddress = new UserAddress;
        $list = $UserAddress->getZList();
        return  $this->fetch('guide/pickuppoint',compact('list')); 
    }

}