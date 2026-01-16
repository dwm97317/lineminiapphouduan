<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\store\shop\Setting;
use app\api\model\store\Shop as ShopModel;
use app\api\model\store\shop\Withdraw as WithdrawModel;
use app\api\model\store\shop\Clerk as ClerkModel;
/**
 * 分销商提现
 * Class Withdraw
 * @package app\api\controller\user\dealer
 */
class Withdraw extends Controller
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
        // 加盟商用户信息
         $this->clerk = ClerkModel::detail(['user_id' => $this->getUser()['user_id']]);
        // 加盟商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 提交提现申请
     * @param $data
     * @return array
     * @throws \app\common\exception\BaseException
     */
    public function submit()
    {
        $formData = $this->request->param();
        $ShopModel = new ShopModel;
        $shop = $ShopModel::detail($this->clerk['shop_id']);
        $model = new WithdrawModel;
        if ($model->submit($shop, $formData)) {
            return $this->renderSuccess([], '提现申请已提交成功，请等待审核');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }

    /**
     * 分销商提现明细
     * @param int $status
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($status = -1)
    {
        $model = new WithdrawModel;
        return $this->renderSuccess([
            // 提现明细列表
            'list' => $model->getList($this->clerk['shop_id'], (int)$status),
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

}