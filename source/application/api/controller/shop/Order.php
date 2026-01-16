<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\common\service\Order as OrderService;
use app\common\enum\OrderType as OrderTypeEnum;
use app\api\model\store\shop\Clerk as ClerkModel;
use app\api\model\store\shop\Setting;
use app\api\model\Inpack;
use app\api\model\store\Shop;
use app\common\model\store\shop\ShopBonus;
/**
 * 自提订单管理
 * Class Order
 * @package app\api\controller\shop
 */
class Order extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->user = $this->getUser();   // 用户信息
        $this->setting = Setting::getAll();
    }

     /**
     * 分销商订单列表
     * @param int $settled
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($settled = -1,$type=1)
    {
        $Clerk = new ClerkModel;
        $model = new Inpack;
        $Shop = new Shop;
        $ShopBonus = new ShopBonus;
        $shopclerk =$Clerk::detail(['user_id'=> $this->user['user_id']]);
        //根据shop_id来查询出这个shop的基础分成比例；
        $shopdata = $Shop->getDetails($shopclerk['shop_id']);
        if($type==1){
            switch ($settled) {
                case '0':
                    $list = $model->getList(['shop_id' => $shopclerk['shop_id']]);
                    break;
                case '1':
                    $list = $model->getList(['shop_id' => $shopclerk['shop_id'],'is_settled'=> (int)$settled]);
                    break;
                case '2':
                    $list = $model->getList(['shop_id' => $shopclerk['shop_id'],'is_settled'=> 0]);
                    break;
                default:
                    $list = $model->getList(['shop_id' => $shopclerk['shop_id'],'is_settled'=> (int)$settled]);
                    break;
            }
            //计算一个集运单的收益
            foreach ($list as $k => $v){
                $bonus = $ShopBonus->where(['line_id'=> $v['line_id'],'shop_id' => $shopclerk['shop_id'],'sr_type' => 1])->find();
                $list[$k]['bonus'] = 0;
                if(!empty($bonus)){
                    //0固定金额 1总金额比例
                    if($bonus['bonus_type']==1){
                        $list[$k]['bonus'] = round($v['real_payment']*$bonus['proportion']/100,2);
                    }else{
                        $list[$k]['bonus'] = round($bonus['proportion'],2);
                    }
                }
            }
        }elseif($type==2){
            switch ($settled) {
                case '0':
                    $list = $model->getList(['storage_id' => $shopclerk['shop_id']]);
                    break;
                case '1':
                    $list = $model->getList(['storage_id' => $shopclerk['shop_id'],'is_settled'=> (int)$settled]);
                    break;
                case '2':
                    $list = $model->getList(['storage_id' => $shopclerk['shop_id'],'is_settled'=> 0]);
                    break;
                default:
                    $list = $model->getList(['storage_id' => $shopclerk['shop_id'],'is_settled'=> (int)$settled]);
                    break;
            }
            //计算一个集运单的收益
            foreach ($list as $k => $v){
                $bonus = $ShopBonus->where(['line_id'=> $v['line_id'],'shop_id' => $shopclerk['shop_id'],'sr_type' => 0])->find();
                $list[$k]['bonus'] = 0;
                if(!empty($bonus)){
                    //0固定金额 1总金额比例
                    if($bonus['bonus_type']==1){
                        $list[$k]['bonus'] = round($v['real_payment']*$bonus['proportion']/100,2);
                    }else{
                        $list[$k]['bonus'] = round($bonus['proportion'],2);
                    }
                }
            }
            
        }
        return $this->renderSuccess([
            // 提现明细列表
            'list' => $list,
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }


    /**
     * 核销订单详情
     * @param $order_id
     * @param int $order_type
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($order_id, $order_type = OrderTypeEnum::MASTER)
    {
        // 订单详情
        $model = OrderService::getOrderDetail($order_id, $order_type);
        // 验证是否为该门店的核销员
        $clerkModel = ClerkModel::detail(['user_id' => $this->user['user_id']]);
        return $this->renderSuccess([
            'order' => $model,  // 订单详情
            'clerkModel' => $clerkModel,
            'setting' => [
                // 积分名称
                'points_name' => SettingModel::getPointsName(),
            ],
        ]);
    }

    /**
     * 确认核销
     * @param $order_id
     * @param int $order_type
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function extract($order_id, $order_type = OrderTypeEnum::MASTER)
    {
        // 订单详情
        $order = OrderService::getOrderDetail($order_id, $order_type);
        // 验证是否为该门店的核销员
        $ClerkModel = ClerkModel::detail(['user_id' => $this->user['user_id']]);
        if (!$ClerkModel->checkUser($order['extract_shop_id'])) {
            return $this->renderError($ClerkModel->getError());
        }
        // 确认核销
        if ($order->verificationOrder($ClerkModel['clerk_id'])) {
            return $this->renderSuccess([], '订单核销成功');
        }
        return $this->renderError($order->getError() ?: '核销失败');
    }

}