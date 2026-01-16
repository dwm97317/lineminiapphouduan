<?php

namespace app\store\controller;

use app\common\library\wechat\WxPay;
use app\common\library\Analysis;
use app\store\model\BuyerOrder;
use app\store\model\Express;
use app\store\model\User;
use app\store\model\Inpack;
use think\Db;
/**
 * 代购页面接口
 * Class Notify
 * @package app\api\controller
 */
class Buyer extends Controller
{
    /**
     * 代购首页
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function verify()
    {
        $map['status'] = 1;
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
        return $this->fetch('verify', compact('list'));
    }
    
    /**
     * 代购首页
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function all()
    {
        $map = [];
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
        foreach($list as $k => $v){
            $list[$k]['total_free'] = $v['price']*$v['num']+$v['free'];
        }
        return $this->fetch('index', compact('list'));
    }
    
    /**
     * 代购首页
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function complete()
    {
        $map['status'] = 6;
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
        return $this->fetch('index', compact('list'));
    }
    
    
    /**
     * 无效首页
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function cancle()
    {
        $map['status'] = [-1,8];
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
        foreach($list as $k => $v){
            $list[$k]['total_free'] = $v['price']*$v['num']+$v['free'];
        }
        return $this->fetch('verify', compact('list'));
    }
    
    public function edit($id){
        $data =  (new BuyerOrder())->find($id);
        $spec = 0;
        return $this->fetch('edit', compact('data'));
    }
    
    public function upsatatus(){
       $ids = $this->postData('selectIds')[0];
       $status = $this->postData('order')['status'];
       $idsArr = explode(',',$ids);
       $model = new BuyerOrder();
       
       foreach ($idsArr as $v){
           $order =  $model->where(['b_order_id'=>$v])->find($v);
           $_up = [
             'status' => $status,
           ];
           
           $_up['updated_time'] = getTime();
           if ($status==5){
               if ($order['status']>=5){
              return $this->renderError('订单已同步到集运单');
           }
              // 同步到集运订单
              $inpack = (new Inpack())->anyicData($order);
           }
           $model->where(['b_order_id'=>$v])->update($_up);
       }    
       return $this->renderSuccess('更新成功');
    }
    
    // 退服务费
    public function refund_service($id){
         $data = $this->postData();
         $model = new BuyerOrder();
         $data = $model->find($id);
         return $this->fetch('refund_service', compact('data'));
    }
    
    public function save(){
        $data = $this->postData();
       // dump($data);
        $buy = (new BuyerOrder());
        $buyOrder = (new BuyerOrder())->find($data['id']);
        $res = $buy->updateData($data);
        $url = url('store/buyer/verify');
        if (isset($data['type']) && $data['type']=='order_update'){
            $url =  url('store/buyer/order');
        }
        if ($data['status']==-1){
            // 不含服务费 的 退款
            $remark =  '代购订单'.$buyOrder['order_sn'].'的支付退款';
            (new User())->banlanceUpdate('add',$buyOrder['member_id'],$buyOrder['real_payment'],$remark);
        } 
        if (isset($data['price']) && $data['status']!=-1){
            $total = $data['price']*$data['num']+$data['free'];
            $amount_2 = $total-$buyOrder['real_payment'];
            $amount_3 =$data['service_free']-$buyOrder['service_free'];
            // dump($buyOrder['real_payment']);die;
            // 价格变动 逻辑
            if ($total!=$buyOrder['real_payment']){
                  $user = (new User())->find($buyOrder['member_id']);
                  if ($user['balance']<$amount_2){
                        return $this->renderError('余额不足,请充值');
                  }
                  Db::startTrans();
                  $update['real_payment'] = $total;
                  $update['status'] = $data['status'];
                  $update['pay_time'] = getTime();
                  $update['updated_time'] = getTime();
                    try {
                         (new BuyerOrder())->where('b_order_id',$buyOrder['b_order_id'])->update($update);
                         $remark =  '代购订单'.$buyOrder['order_sn'].'的支付';
                         if($amount_2>0){
                             (new User())->banlanceUpdate('remove',$buyOrder['member_id'],abs($amount_2),$remark);
                         }
                         if($amount_2<0){
                             (new User())->banlanceUpdate('add',$buyOrder['member_id'],abs($amount_2),$remark);
                         }
                     }catch(\Exception $e){
                         dump($e); die;
                         return $this->renderError('支付失败,请重试');
                     }
                Db::commit();
            }
            //当服务费有变动时，需要额外支付服务费用；
            if($amount_3!=0){
                // 更新该批次的 服务费 
                (new BuyerOrder())->where('batch',$buyOrder['batch'])->update(['service_free'=>$data['service_free']]);
                $remark =  '代购订单'.$buyOrder['order_sn'].'的服务费额外支付';
                (new User())->banlanceUpdate('remove',$buyOrder['member_id'],$amount_3,$remark);
            }
        }
        if ($res) {
            return $this->renderSuccess('编辑成功',$url );
        }
        return $this->renderError($buy->getError()??'编辑失败');
    }
    
    // 详情页
    public function detail($id){
        $detail = (new BuyerOrder())->find($id);
        return $this->fetch('detail', compact('detail'));
    }
    
    // 支付退款到余额
    public function refund(){
        $ids = $this->postData('selectIds');
        $status = $this->postData('order')['status'];
        if (!$status){
            return $this->renderSuccess('编辑成功');
        }
        Db::startTrans();
        foreach ($ids as $v){
            $order = (new BuyerOrder())->find($v);
            $member = (new User())->find($order['member_id']);
            $update['status'] = '8'; // 退款状态
            $update['real_payment'] = 0;
            $update['rufund_step'] = 2;
            $update['updated_time'] = getTime();
            $amount = $order['real_payment'];
            $remark =  '代购订单'.$order['order_sn'].'的支付退款';
            (new User())->banlanceUpdate('add',$order['member_id'],$amount,$remark);
            (new BuyerOrder())->where('b_order_id',$order['b_order_id'])->update($update); 
        }
        Db::commit();
        return $this->renderSuccess('退款成功');
    }
    
    // 退服务费
    public function refund_save(){
        $data = $this->postData();
        $buy = (new BuyerOrder());
        $buyOrder = (new BuyerOrder())->find($data['id']);
        if ($buyOrder['rufund_step']!=2){
             return $this->renderError('该代购单服务费已退');
        }
        $remark =  '代购订单'.$buyOrder['order_sn'].'的服务费退款';
        $service_amount = $data['service'];
        (new User())->banlanceUpdate('add',$buyOrder['member_id'],$service_amount,$remark);
        $res = $buy->where(['b_order_id'=>$buyOrder['b_order_id']])->update([
           'refund_service' => $service_amount,
           'rufund_step' => 3,
           'updated_time' => getTime(),
        ]);
        if ($res) {
            return $this->renderSuccess('退款成功');
        }
        return $this->renderError($buy->getError()??'退款失败');
    } 
     
    // 订单编辑
    public function update($id){
        $data =  (new BuyerOrder())->find($id);
        $expressList = Express::getAll();
        return $this->fetch('order_update', compact('data','expressList'));
    }
    
    public function order(){
        $map['status'] = 2;
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
       
        return $this->fetch('index', compact('list'));
    }
    
    public function traning(){
        $map['status'] = 3;
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
      
        return $this->fetch('index', compact('list'));
    }
    
    public function in(){
        $map['status'] = 4;
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
       
        return $this->fetch('index', compact('list'));
    }
    
    public function anyic(){
        $map['status'] = 5;
        $map2 = \request()->param();
        $map = array_merge($map,$map2);
        $list = (new BuyerOrder())->getList($map);
       
        return $this->fetch('index', compact('list'));
    }
    
    
}
