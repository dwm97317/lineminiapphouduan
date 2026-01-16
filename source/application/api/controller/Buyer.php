<?php

namespace app\api\controller;

use app\common\library\wechat\WxPay;
use app\common\library\Analysis;
use app\api\model\BuyerOrder;
use app\api\model\User;
use app\api\model\Setting;
use app\api\model\BuyerBind;
use app\api\model\user\BalanceLog;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
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
    public function order()
    {
        // 微信支付组件：验证异步通知
        $WxPay = new WxPay();
        $WxPay->notify();
    }

    // url分析支持 淘宝 / 天猫 / jd
    public function analysis(){
        $url = $this->postData('url')[0];
        if (!$url){
             return $this->renderError('未检测到url哦');
        }
        $checkRes = (new Analysis())->check($url);
        if (!$checkRes){
            return $this->renderSuccess('抱歉您输入url暂不支持解析');   
        }
        $data = Analysis::init();
    }
    
    // 计算服务费
    public function checkOut(){
        $dataList = $this->postData('items');
        $user = $this->getUser();
        $buy = (new BuyerOrder());
        $res = $buy->checkData($dataList,$user['user_id']);
        if (!$res){
            return $this->renderError($buy->getError()??'预报失败');
        }
        // 计算服务费
        $num = count($dataList);
       
        $service = Setting::getItem('service');
        $price = 0;
        foreach ($service as $v){
            if ($num >= $v['num']['min'] && $num <= $v['num']['max'] ){
                $price = $v['price'];
                continue;
            }
        } 
        if (empty($price)){
            $price = $service[count($service)-1]['price'];
        }
        return $this->renderSuccess(['service_free'=>$price]); 
        
    }
    
    // 代购订单预报
    public function reportBuyer(){
        $dataList = $this->postData('items');
        $remark = $this->postData('remark')[0];
        $user = $this->getUser();
        $buy = (new BuyerOrder());
        $res = $buy->checkData($dataList,$user['user_id']);
        if (!$res){
            return $this->renderError($buy->getError()??'预报失败');
        }
        // 计算服务费
        $num = count($dataList);
       
        $service = Setting::getItem('service');
        foreach ($service as $v){
            if ($num >= $v['num']['min'] && $num <= $v['num']['max'] ){
                $price = $v['price'];
                continue;
            }
        } 
        $count = 0;
        $ids = []; 
        
        $batch = date("Ymdhis").rand(0,999);
        foreach ($dataList as $v){
            $v['service_free'] = $price;
            $v['remark'] = $remark;
            $v['batch'] = $batch;
            $v['member_id'] = $user['user_id'];
            if(!$id = $buy->add($v)){
                $count++;
                $ids[] = $id;
            }
        }
        if ($price){
             $user = (new User())->find($user['user_id']);
             $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
               'balance'=>$user['balance']-$price,
               'pay_money' => $user['pay_money']+ $price,
             ]);
             if (!$memberUp){
                 Db::rollback();
                 return false;
             }
              // 新增余额变动记录
             BalanceLog::add(SceneEnum::CONSUME, [
              'user_id' => $user['user_id'],
              'money' => $price,
              'remark' => '代购订单服务费的支付',
              'sence_type' => 2,
          ], [$user['nickName']]);
        }
        (new BuyerBind())->saveData(['service_free'=>$price,'buyer_ids'=>$ids,'batch'=>$batch]);
        return $this->renderSuccess('预报成功');  
    }
    
    // 我的代购单
    public function BuyOrderList(){
        $user = $this->getUser();
        $where['member_id'] = $user['user_id'];
        $status = request()->param('type');
        if ($status){
            $where['status'] = $status;
            if ($status==-1){
                $where['status'] = [-1,8];
            }
            if ($status==3){
                $where['status'] = [3,4,5];
            }
        }
        $buy = (new BuyerOrder());
        
        $data = $buy->getList($where);
        foreach ($data as $k => $v){
            $data[$k]['total_free'] = $v['price']*$v['num']+$v['free']; 
            
        }
        return $this->renderSuccess($data);
    }
    
    // 取消代购单
    public function cancle(){
        $order_id = $this->postData('order_id');
        $buy = (new BuyerOrder())->find($order_id);
        if (!$buy){
            return $this->renderError('代购订单不存在');
        }
        if ($buy['status']>=3){
            return $this->renderError('代购订单不能取消');
        }
        $update['is_close'] = 1;
        $update['status'] = -1;
        $update['rufund_step'] = 2;
        $update['real_payment'] = 0;
        $res =(new BuyerOrder())->where('b_order_id',$buy['b_order_id'])->update($update);
        if (!$res){
            return $this->renderError('取消失败,请重试');
        }
        $remark =  '代购订单'.$buy['order_sn'].'的支付退款';
        (new User())->banlanceUpdate('add',$buy['member_id'],$buy['real_payment'],$remark);
        return $this->renderSuccess('取消成功');
    }
    
    public function feedBack(){
        $order_id = $this->postData('order_id');
        $content = $this->postData('content')[0];
        $buy = (new BuyerOrder())->find($order_id);
        if (!$buy){
            return $this->renderError('代购订单不存在');
        }
        if ($buy['status']!=5){
            return $this->renderError('未处在已完成状态');
        }
        $update['feedback'] = $content;
        $update['is_feed'] = 1;
        $res =(new BuyerOrder())->where('b_order_id',$buy['b_order_id'])->update($update);
        if (!$res){
            return $this->renderError('反馈失败,请重试');
        }
        return $this->renderSuccess('反馈成功');
    }
    
    // 确认完成代购单
    public function confirm(){
        $order_id = $this->postData('order_id')[0];
        $buy = (new BuyerOrder())->find($order_id);
        if (!$buy){
            return $this->renderError('代购订单不存在');
        }
        if ($buy['status']!=5){
            return $this->renderError('代购订单不能完成');
        }
        $update['status'] = 6;
        $res =(new BuyerOrder())->where('b_order_id',$buy['b_order_id'])->update($update);
        if (!$res){
            return $this->renderError('操作失败,请重试');
        }
        return $this->renderSuccess('已确认');
    }
    
    // 代购单支付
    public function BuyOrderPay(){
        $order_id = $this->postData('order_id');
       
        $buy = (new BuyerOrder())->find($order_id);
        if (!$buy){
            return $this->renderError('代购订单不存在');
        }
        if ($buy['status']!=2){
            return $this->renderError('代购订单未处在待支付状态');
        }
        $price = $buy['price']*$buy['num']+$buy['free'];
        $user = $this->getUser();
        $amount = $price;
        if ($user['balance']<$amount){
            return $this->renderError('余额不足,请充值');
        }
        Db::startTrans();
        $update['real_payment'] = $amount;
        $update['is_pay'] = 1;
        $update['status'] = 3;
        $update['pay_time'] = getTime();
        try {
             (new BuyerOrder())->where('b_order_id',$buy['b_order_id'])->update($update);
             $memberUp = (new User())->where(['user_id'=>$user['user_id']])->update([
               'balance'=>$user['balance']-$amount,
               'pay_money' => $user['pay_money']+ $amount,
             ]);
             if (!$memberUp){
                 Db::rollback();
                 return $this->renderError('支付失败,请重试');
             }
              // 新增余额变动记录
             BalanceLog::add(SceneEnum::CONSUME, [
              'user_id' => $user['user_id'],
              'money' => $amount,
              'remark' => '代购订单'.$buy['order_sn'].'的支付',
              'sence_type' => 2,
          ], [$user['nickName']]);
         }catch(\Exception $e){
             dump($e); die;
             return $this->renderError('支付失败,请重试');
         }
         Db::commit();
         return $this->renderSuccess('支付成功');
    }
    
}
