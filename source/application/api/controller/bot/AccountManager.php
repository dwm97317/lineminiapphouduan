<?php

namespace app\api\controller\bot;

use app\api\model\PlatformAccount;
use app\api\model\User as UserModel;
use app\common\exception\BaseException;
use think\Db;

/**
 * Bot 账户管理控制器
 * 处理 Bot 端的账户管理和查询命令
 * Class AccountManager
 * @package app\api\controller\bot
 */
class AccountManager extends \app\api\controller\Controller
{
    /**
     * 查看已关联的账户列表
     * GET /api/bot/account/list-linked
     * 
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function listLinked()
    {
        // 获取参数
        $customerId = $this->request->param('customer_id');
        
        if (!$customerId) {
            throw new BaseException([
                'code' => 400,
                'msg' => '缺少参数：customer_id',
            ]);
        }
        
        $wxappId = $this->wxapp_id;
        
        // 获取该 Customer ID 下的所有绑定账户
        $bindings = PlatformAccount::getByCustomerId($customerId, $wxappId);
        
        if ($bindings->isEmpty()) {
            return $this->renderSuccess([
                'list' => [],
                'total' => 0,
                'message' => '该 Customer ID 尚未关联任何账户',
            ], '暂无关联账户');
        }
        
        // 格式化返回数据
        $result = [];
        foreach ($bindings as $binding) {
            $userInfo = $this->getUserInfo($binding['user_id']);
            
            $result[] = [
                'id' => $binding['id'],
                'platform_type' => $binding['platform_type'],
                'user_info' => $userInfo,
                'binding_time' => $binding['binding_time'],
                'last_verify_time' => $binding['last_verify_time'],
            ];
        }
        
        return $this->renderSuccess([
            'list' => $result,
            'total' => count($result),
            'max_allowed' => PlatformAccount::MAX_BINDINGS_PER_CUSTOMER,
            'remaining' => PlatformAccount::MAX_BINDINGS_PER_CUSTOMER - count($result),
        ], '找到 ' . count($result) . ' 个关联账户');
    }

    /**
     * 获取待入库包裹列表（等待处理的包裹）
     * GET /api/bot/package/waiting-list
     * 
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function waitingList()
    {
        // 获取参数
        $customerId = $this->request->param('customer_id');
        
        if (!$customerId) {
            throw new BaseException([
                'code' => 400,
                'msg' => '缺少参数：customer_id',
            ]);
        }
        
        $wxappId = $this->wxapp_id;
        
        // 获取该 Customer ID 关联的所有用户 ID
        $bindings = PlatformAccount::getByCustomerId($customerId, $wxappId);
        
        if ($bindings->isEmpty()) {
            return $this->renderSuccess([
                'list' => [],
                'total' => 0,
            ], '请先关联账户');
        }
        
        $userIdList = $bindings->column('user_id');
        
        // 查询这些用户的待入库包裹（status=1 表示待入库）
        $waitingPackages = Db::name('package')
            ->whereIn('member_id', $userIdList)
            ->where(['status' => 1, 'wxapp_id' => $wxappId])
            ->where('is_delete', 0)
            ->field([
                'id',
                'express_num as package_code',
                'weight',
                'volume',
                'created_time',
                'remark',
            ])
            ->order('created_time', 'DESC')
            ->select();
        
        // 格式化数据
        $result = [];
        foreach ($waitingPackages as $pkg) {
            $result[] = [
                'package_id' => $pkg['id'],
                'package_code' => $pkg['package_code'],
                'weight' => $pkg['weight'] ?? 0,
                'volume' => $pkg['volume'] ?? 0,
                'created_time' => $pkg['created_time'],
                'remark' => $pkg['remark'] ?? '',
            ];
        }
        
        return $this->renderSuccess([
            'list' => $result,
            'total' => count($result),
        ], '找到 ' . count($result) . ' 个待入库包裹');
    }

    /**
     * 获取用户订单历史列表
     * GET /api/bot/order/history
     * 
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function orderHistory()
    {
        // 获取参数
        $customerId = $this->request->param('customer_id');
        $limit = (int)$this->request->param('limit', 20);
        
        if (!$customerId) {
            throw new BaseException([
                'code' => 400,
                'msg' => '缺少参数：customer_id',
            ]);
        }
        
        $wxappId = $this->wxapp_id;
        
        // 获取该 Customer ID 关联的所有用户 ID
        $bindings = PlatformAccount::getByCustomerId($customerId, $wxappId);
        
        if ($bindings->isEmpty()) {
            return $this->renderSuccess([
                'list' => [],
                'total' => 0,
            ], '请先关联账户');
        }
        
        $userIdList = $bindings->column('user_id');
        
        // 查询这些用户的订单（集运订单 type=30）
        $orders = Db::name('order')
            ->whereIn('user_id', $userIdList)
            ->where(['wxapp_id' => $wxappId])
            ->where('is_delete', 0)
            ->field([
                'order_id',
                'order_sn',
                'order_status',
                'express_num',
                'real_payment',
                'created_time',
                'pay_time',
                'shipping_time',
            ])
            ->order('created_time', 'DESC')
            ->limit($limit)
            ->select();
        
        // 订单状态映射
        $statusMap = [
            1 => '待付款',
            2 => '待发货',
            3 => '待收货',
            4 => '已完成',
            5 => '已取消',
        ];
        
        // 格式化数据
        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'order_id' => $order['order_id'],
                'order_sn' => $order['order_sn'],
                'order_status' => $order['order_status'],
                'order_status_text' => $statusMap[$order['order_status']] ?? '未知',
                'express_num' => $order['express_num'] ?? '',
                'payment' => $order['real_payment'],
                'created_time' => $order['created_time'],
                'pay_time' => $order['pay_time'],
                'shipping_time' => $order['shipping_time'],
            ];
        }
        
        return $this->renderSuccess([
            'list' => $result,
            'total' => count($result),
            'limit' => $limit,
        ], '找到 ' . count($result) . ' 个订单');
    }

    /**
     * 获取用户基本信息（脱敏）
     * 
     * @param int $userId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function getUserInfo($userId)
    {
        $user = UserModel::useGlobalScope(false)
            ->where(['user_id' => $userId])
            ->field(['user_id', 'nickName', 'avatarUrl', 'mobile'])
            ->find();
        
        if (!$user) {
            return [
                'nickname' => 'Unknown',
                'avatar' => '',
                'mobile_masked' => '',
            ];
        }
        
        // 脱敏处理
        $mobileMasked = '';
        if (!empty($user['mobile'])) {
            $mobileMasked = preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $user['mobile']);
        }
        
        return [
            'nickname' => PlatformAccount::anonymizeName($user['nickName']),
            'avatar' => $user['avatarUrl'] ?? '',
            'mobile_masked' => $mobileMasked,
        ];
    }
}
