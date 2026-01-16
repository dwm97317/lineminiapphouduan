<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\store\shop\Setting;
use app\api\model\store\shop\Capital as CapitalModel;
use app\api\model\dealer\Order as OrderModel;
use app\api\model\store\shop\Clerk as ClerkModel;
use app\api\model\store\Shop as ShopModel;
/**
 * 加盟商订单
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Capital extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;
    private $clerk;
    private $setting;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 用户信息
        $this->user = $this->getUser();
        // 员工信息
        $this->clerk = ClerkModel::detail(['user_id' => $this->getUser()['user_id']]);
        // 加盟商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 加盟商订单列表
     * @param int $settled
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($type = -1)
    {
        $model = new CapitalModel;
        $ShopModel = new ShopModel;
        $shop = $ShopModel::detail($this->clerk['shop_id']);

        return $this->renderSuccess([
            'total' => [
                'sendincome'=> $model->where(['shop_id'=>$this->clerk['shop_id'],'flow_type'=>10])->sum('money'),
                'pickncome' => $model->where(['shop_id'=>$this->clerk['shop_id'],'flow_type'=>20])->sum('money'),
            ],
            // 提现明细列表
            'list' => $model->getList($this->clerk['shop_id'], (int)$type),
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

}