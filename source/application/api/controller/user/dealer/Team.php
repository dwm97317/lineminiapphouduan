<?php

namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Setting;
use app\api\model\dealer\User as DealerUserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\dealer\Order;

/**
 * 我的团队
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Team extends Controller
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
        // 分销商用户信息
        $this->dealer = DealerUserModel::detail($this->user['user_id']);
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    /**
     * 我的团队列表
     * @param int $level
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists($level = -1)
    {
        $model = new RefereeModel;
        $filterMap = [
           '' => 'all',
           1 => 'all',
           2 => 'today',
           3 => 'week'
        ];
        $list = $model->getList($this->user['user_id'], (int)$level,$filterMap[$this->request->param('filter')]);
        $Order = (new Order());
        foreach ($list as $k => $v){
           $list[$k]['order'] = [
              'num' => $Order->where(['user_id'=>$v['user_id']])->count(),
              'all_price' =>  $Order->where(['user_id'=>$v['user_id']])->sum('order_price'),
              'income' =>  $Order->where(['user_id'=>$v['user_id']])->sum('first_money')
           ];   
        }
        return $this->renderSuccess([
            // 分销商用户信息
            'dealer' => $this->dealer,
            // 我的团队列表
            'list' => $list,
            // 基础设置
            'setting' => $this->setting['basic']['values'],
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

}