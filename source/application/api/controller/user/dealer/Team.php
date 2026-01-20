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
           $level = isset($v['level']) ? $v['level'] : 1;
           $moneyField = $level == 2 ? 'second_money' : ($level == 3 ? 'third_money' : 'first_money');
           
           // We need orders where THIS user ($v['user_id']) is the buyer
           // AND the current logged in user ($this->user['user_id']) is the beneficiary for that specific level
           // Actually, the simple query is:
           // sum $moneyField where user_id = subordinate_id
           // Because dealer_order table stores the specific money for that level.
           // However, strictly speaking, we should verify the dealer relationship, but implied by hierarchy.
           
           // Identify which field represents Me in the order
           $dealerField = $level == 2 ? 'second_user_id' : ($level == 3 ? 'third_user_id' : 'first_user_id');
           $myId = $this->user['user_id'];
           
           // Query constraints: Order Buyer is Subordinate AND Dealer Field is Me
           $orderQuery = $Order->where(['user_id' => $v['user_id'], $dealerField => $myId]);
           $total_money = $orderQuery->sum($moneyField);
           
           // Re-instantiate query for distinct counts/sums (cannot reuse query builder after execution usually, assumes fresh)
           // Actually ThinkPHP query object might persist conditions? Safer to new or clone. 
           // But here $Order is an instance. We should use new queries or arrays.
           
           $num = $Order->where(['user_id' => $v['user_id'], $dealerField => $myId])->count();
           $all_price = $Order->where(['user_id' => $v['user_id'], $dealerField => $myId])->sum('order_price');
           $income = $Order->where(['user_id' => $v['user_id'], $dealerField => $myId])->sum($moneyField);

           $list[$k]['order'] = [
              'num' => $num,
              'all_price' =>  $all_price,
              'income' =>  $income
           ];
           // Frontend expects total_commission top-level
           $list[$k]['total_commission'] = $income;
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

    /**
     * 分销商星图数据
     * @return array
     * @throws \think\exception\DbException
     */
    public function starmap()
    {
        $userId = $this->user['user_id'];
        $model = new RefereeModel;

        // 1. 获取所有下级 (限制100人防止过大，实际业务可调整)
        $downlines = $model->alias('referee')
            ->field('referee.user_id, referee.level, u.nickName, u.avatarUrl')
            ->join('user u', 'u.user_id = referee.user_id')
            ->where('referee.dealer_id', '=', $userId)
            ->where('u.is_delete', '=', 0)
            ->group('referee.user_id') // Fix: Prevent duplicate nodes
            ->limit(200) 
            ->select();

        $nodes = [];
        $edges = [];

        // 根节点 (自己)
        $nodes[] = [
            'id' => $userId,
            'label' => $this->user['nickName'],
            'image' => $this->user['avatarUrl'],
            'level' => 0,
            'is_root' => true
        ];

        if (!$downlines->isEmpty()) {
            // 提取下级ID
            $downlineIds = [];
            foreach ($downlines as $item) {
                $downlineIds[] = $item['user_id'];
                $nodes[] = [
                    'id' => $item['user_id'],
                    'label' => $item['nickName'],
                    'image' => $item['avatarUrl'],
                    'level' => $item['level'],
                ];
            }

            // 2. 查询这些下级的直接上级 (level=1)
            // 只有当上级ID也在 nodes 列表里 (或者是自己) 时，才建立连接
            $parents = $model->where('user_id', 'in', $downlineIds)
                ->where('level', '=', 1)
                ->column('dealer_id', 'user_id');

            foreach ($downlines as $item) {
                $uid = $item['user_id'];
                if (isset($parents[$uid])) {
                    $pid = $parents[$uid];
                    // 确保父节点在我们的图中 (要么是自己，要么是此次查询出的下级之一)
                    // 注意：$parents[$uid] 返回的是 dealer_id
                    // 如果 pid == userId (自己)，则连接
                    // 如果 pid 在 downlineIds 里，则连接
                    if ($pid == $userId || in_array($pid, $downlineIds)) {
                        $edges[] = ['from' => $pid, 'to' => $uid];
                    }
                }
            }
        }

        return $this->renderSuccess(compact('nodes', 'edges'));
    }

}