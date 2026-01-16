<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\Capital as CapitalModel;
use app\api\model\dealer\Order as OrderModel;

/**
 * 分销商订单
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Capital extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $dealer;
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
      
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 分销商订单列表
     * @param int $settled
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($type = -1)
    {
        $model = new CapitalModel;
        return $this->renderSuccess([
            'total' => [
                'income'=> $this->user['income'],
                'allIncome' => $model->where(['user_id'=>$this->user['user_id'],'flow_type'=>10])->sum('money'),
            ],
            // 提现明细列表
            'list' => $model->getList($this->user['user_id'], (int)$type),
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

}