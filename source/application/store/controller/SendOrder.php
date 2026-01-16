<?php
namespace app\store\controller;

use app\api\model\Package;
use app\common\model\Logistics;
use app\store\model\SendOrder as ModelSendOrder;
use app\store\model\SendPreOrder;

/**
 * 订单管理
 * Class Order
 * @package app\store\controller
 */
class SendOrder extends Controller
{
    /**
     * 预发货订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function PreSend_list()
    {
        return $this->getList('预发货订单列表', 'preSend');
    }

    /**
     * 全部订单列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function send_list()
    {
        return $this->getList('发货订单列表', 'send');
    }

    /**
     * 订单详情
     * @param $order_id
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail($order_id)
    {
      
    }

    /**
     * 确认发货
     * @param $order_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delivery($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->delivery($this->postData('order'))) {
            return $this->renderSuccess('发货成功');
        }
        return $this->renderError($model->getError() ?: '发货失败');
    }

    /**
     * 修改订单价格
     * @param $order_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function updatePrice($order_id)
    {
        $model = OrderModel::detail($order_id);
        if ($model->updatePrice($this->postData('order'))) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }

    /**
     * 订单列表
     * @param string $title
     * @param string $dataType
     * @return mixed
     * @throws \think\exception\DbException
     */
    private function getList($title, $dataType)
    {
        if ($dataType=='preSend'){
            // 订单列表
            $model = new SendPreOrder;
            $_template = 'pre_order';
            $list = $model->getList($this->request->param());
        }else{
          // 订单列表
            $model = new ModelSendOrder;
            $_template = 'pre_order';
            $list = $model->getList($this->request->param());
            $_template = 'index';
        }
        //dump($shopList);die;
        return $this->fetch($_template, compact('title','list'));
    }
    
    public function details($send_id){
        $packItem = (new SendPreOrder())->getSendOrderList($send_id);
        $title = '包裹详情';
        return $this->fetch('pre_item', compact('title','packItem'));
    }
    
    public function detailsItem($send_id){
        $packItem = (new ModelSendOrder())->getSendOrderList($send_id);
   
        $title = '包裹详情';
        return $this->fetch('pre_item', compact('title','packItem'));
    }
    
    public function packlist($send_id){
         $packItem = (new SendPreOrder())->getSendOrderList($send_id);
         return $this->renderSuccess('ok','',$packItem);
    }
    
    public function createdsendorder($ids){
         $ids = explode(',',$ids);
         $preOrder = (new SendPreOrder())->whereIn('send_id',$ids)->field('send_id,pack_ids')->select();
         if (!$preOrder){
             return $this->renderError('预发货单错误');
         }
         $packageIds = [];
         foreach ($preOrder as $v){
              $ids = explode(',',$v['pack_ids']);
              $packageIds = array_merge($packageIds,$ids);
         }
         $order = [
           'order_sn' => 'S'.createSn(),
           'pack_ids' => implode(',',$packageIds),
           'num' => count($packageIds),
           'opration_name' => '后台管理员',
           'opration_id' => '0',
         ];
         $res = (new ModelSendOrder())->post($order);
         if (!$res){
              return $this->renderError('发货单创建失败');
         }
         $up = (new Package())->whereIn('id',$packageIds)->update(['status'=>9]);
         (new SendPreOrder())->whereIn('send_id',$ids)->delete(); // 删除预发货单
         foreach($packageIds as $v){
             Logistics::add($v,'包裹已发货,请等待收货');
         } 
         return $this->renderSuccess('发货单创建成功');
    }
    
    /**
     * 物流更新 
     * */
    public function logistics($send_id){
        $sendOrder = (new ModelSendOrder())->details($send_id);
        if (!$this->request->isAjax()){
            return $this->fetch('send_order_logistics', compact('sendOrder'));
        }
        $order_logic = json_decode($sendOrder['logistics'],true);
        $order_logic[] = $this->postData('sendOrder')['logistics'];
        
        $res = (new ModelSendOrder())->where(['send_id'=>$send_id])->update([
            'logistics' => json_encode($order_logic),
        ]);
        $ids = explode(',',$sendOrder['pack_ids']);
        foreach($ids as $v){
             Logistics::add($v,$this->postData('sendOrder')['logistics']);
        } 
        if (!$res){
            return $this->renderError('物流更新失败');
        }
        return $this->renderSuccess('物流更新成功');
    }
}
