<?php

namespace app\api\service\package;

use app\api\service\Basics;
use app\api\model\User as UserModel;
use app\api\model\Inpack as InpackModel;
use app\api\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\enum\recharge\order\PayStatus as PayStatusEnum;

class PaySuccess extends Basics
{
    // 订单模型
    public $model;

    // 当前用户信息
    private $user;

    /**
     * 构造函数
     * PaySuccess constructor.
     * @param $orderNo
     * @throws \think\exception\DbException
     */
    public function __construct($orderNo)
    {
        // 实例化订单模型
        $InpackModel = new InpackModel();
        $this->model = $InpackModel->getInpackSn($orderNo);
        $this->wxappId = $this->model['wxapp_id'];
        // 获取用户信息
        $this->user = UserModel::detail($this->model['member_id']);
    }

    /**
     * 获取订单详情
     * @return OrderModel|null
     */
    public function getOrderInfo()
    {
        return $this->model;
    }

    /**
     * 订单支付成功业务处理
     * @param int $payType 支付类型
     * @param array $payData 支付回调数据
     * @return bool
     */
    public function onPaySuccess($payType, $payData)
    {
       
        return $this->model->transaction(function () use ($payType, $payData) {
//             // 更新订单状态
                if($payType == PayTypeEnum::HANTEPAY){
                      $this->model->save([
                        'is_pay' => 1,
                        'pay_time' => getTime(),
                        'status' =>3,
                        'is_pay_type' => 3,
                     ]);
                }
                
                if($payType == PayTypeEnum::BALANCE){
                    $this->model->save([
                        'is_pay' => 1,
                        'pay_time' => getTime(),
                        'status' =>3,
                        'is_pay_type' => 2,
                    ]);
                }
                
                if($payType == PayTypeEnum::WECHAT){
                    $this->model->save([
                        'is_pay' => 1,
                        'pay_time' => getTime(),
                        'status' =>3,
                        'is_pay_type' => 1,
                    ]);
                }
                if($payType == PayTypeEnum::OMIPAY){
                    $this->model->save([
                        'is_pay' => 1,
                        'pay_time' => getTime(),
                        'status' =>3,
                        'is_pay_type' => 4,
                    ]);
                }
                
                return true;
        });
    }

}