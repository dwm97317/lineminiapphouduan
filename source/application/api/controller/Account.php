<?php

namespace app\api\controller;

use app\api\model\PlatformAccount;
use app\api\service\bot\CustomerVerify as CustomerVerifyService;
use app\common\exception\BaseException;

/**
 * 账户绑定控制器 (FB/IG Bot)
 * 支持多账户绑定：一个 Customer ID 可以绑定多个用户账户（最多 10 个）
 * Class Account
 * @package app\api\controller
 */
class Account extends Controller
{
    /**
     * 绑定平台账户 (FB/IG Bot Customer ID)
     * POST /api/v1/account/bind
     * 
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\Exception
     */
    public function bind()
    {
        // 获取请求参数
        $params = $this->postData();
        
        // 验证必要参数
        if (empty($params['customer_id'])) {
            return $this->renderError('请输入 Customer ID');
        }
        
        if (empty($params['platform_type'])) {
            $params['platform_type'] = 'FACEBOOK'; // 默认 Facebook
        }
        
        // 获取当前用户
        $user = $this->getUser(false);
        if (!$user) {
            return $this->renderError('请先登录', -1);
        }
        
        $userId = $user['user_id'];
        $wxappId = $this->wxapp_id;
        $customerId = trim($params['customer_id']);
        $platformType = strtoupper($params['platform_type']);
        
        // 验证平台类型
        $allowedPlatforms = ['FACEBOOK', 'INSTAGRAM'];
        if (!in_array($platformType, $allowedPlatforms)) {
            return $this->renderError('不支持的平台类型');
        }
        
        // 检查该用户是否已经绑定过这个 Customer ID
        if (PlatformAccount::isCustomerBoundByUser($customerId, $userId, $wxappId)) {
            return $this->renderError('您已绑定该 Customer ID');
        }
        
        // 检查 Customer ID 的绑定数量是否已达上限
        if (PlatformAccount::isBindingLimitReached($customerId, $wxappId)) {
            return $this->renderError([
                'need_support' => true,
                'message' => '该 Customer ID 已达到最大绑定数量限制（10 个），请联系客服处理'
            ]);
        }
        
        // 调用 Bot API 验证 Customer ID
        $verifyResult = CustomerVerifyService::verifyCustomerId($customerId, $platformType);
        
        if (!$verifyResult['success']) {
            return $this->renderError($verifyResult['message']);
        }
        
        // 获取客户信息
        $customerName = $verifyResult['data']['customer_name'] ?? '';
        $anonymizedName = PlatformAccount::anonymizeName($customerName);
        
        // 创建绑定关系
        try {
            PlatformAccount::createBinding([
                'user_id' => $userId,
                'platform_type' => $platformType,
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'is_anonymized' => 1,
                'wxapp_id' => $wxappId,
            ]);
            
            // 返回成功响应，包含匿名化的用户名
            return $this->renderSuccess([
                'customer_id' => $customerId,
                'platform_type' => $platformType,
                'customer_name_anonymized' => $anonymizedName,
                'binding_time' => date('Y-m-d H:i:s'),
                'binding_count' => PlatformAccount::getBindingCount($customerId, $wxappId),
            ], '绑定成功！已关联账户：' . $anonymizedName);
            
        } catch (\Exception $e) {
            return $this->renderError('绑定失败：' . $e->getMessage());
        }
    }

    /**
     * 查询已绑定的账户列表
     * GET /api/v1/account/list
     * 
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list()
    {
        // 获取当前用户
        $user = $this->getUser(false);
        if (!$user) {
            return $this->renderError('请先登录', -1);
        }
        
        $userId = $user['user_id'];
        $wxappId = $this->wxapp_id;
        
        // 获取所有绑定记录
        $bindings = PlatformAccount::getByUserId($userId, $wxappId);
        
        // 格式化返回数据
        $result = [];
        foreach ($bindings as $binding) {
            $result[] = PlatformAccount::formatBindingInfo($binding);
        }
        
        return $this->renderSuccess([
            'list' => $result,
            'total' => count($result),
            'max_allowed' => PlatformAccount::MAX_BINDINGS_PER_CUSTOMER,
        ]);
    }

    /**
     * 解绑账户（需要确认）
     * POST /api/v1/account/unbind
     * 
     * @return array
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function unbind()
    {
        // 获取请求参数
        $params = $this->postData();
        
        if (empty($params['id'])) {
            return $this->renderError('请指定要解绑的记录 ID');
        }
        
        // 可选的确认码（用于二次确认）
        $confirmCode = $params['confirm_code'] ?? null;
        
        // 获取当前用户
        $user = $this->getUser(false);
        if (!$user) {
            return $this->renderError('请先登录', -1);
        }
        
        $userId = $user['user_id'];
        $recordId = $params['id'];
        
        // 查找记录
        $binding = PlatformAccount::get($recordId);
        if (!$binding) {
            return $this->renderError('未找到该绑定记录');
        }
        
        // 验证是否属于当前用户
        if ($binding['user_id'] != $userId) {
            return $this->renderError('无权操作该记录');
        }
        
        // 如果需要确认码但未提供
        if ($confirmCode === null) {
            // 返回确认信息，要求用户提供确认码
            $anonymizedName = PlatformAccount::anonymizeName($binding['customer_name']);
            return $this->renderSuccess([
                'require_confirmation' => true,
                'binding_info' => [
                    'id' => $binding['id'],
                    'platform_type' => $binding['platform_type'],
                    'customer_name_anonymized' => $anonymizedName,
                    'binding_time' => $binding['binding_time'],
                ],
                'message' => '请提供确认码以完成解绑操作（发送 confirm_code 参数）',
            ], '确定要解绑账户 ' . $anonymizedName . ' 吗？此操作不可恢复。');
        }
        
        // 验证确认码（简单示例，实际可以使用更复杂的逻辑）
        if ($confirmCode !== 'CONFIRM' && $confirmCode !== 'YES') {
            return $this->renderError('确认码不正确，解绑已取消');
        }
        
        // 删除绑定
        if ($binding->delete()) {
            return $this->renderSuccess([], '解绑成功');
        } else {
            return $this->renderError('解绑失败');
        }
    }

    /**
     * 验证 Customer ID（独立接口，用于 Bot 端验证）
     * POST /api/v1/account/verify-customer
     * 
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function verifyCustomer()
    {
        $params = $this->postData();
        
        if (empty($params['customer_id'])) {
            return $this->renderError('请输入 Customer ID');
        }
        
        $platformType = strtoupper($params['platform_type'] ?? 'FACEBOOK');
        
        // 调用验证服务
        $verifyResult = CustomerVerifyService::verifyCustomerId($params['customer_id'], $platformType);
        
        if (!$verifyResult['success']) {
            return $this->renderError($verifyResult['message']);
        }
        
        // 返回客户信息（匿名化）
        $customerName = $verifyResult['data']['customer_name'] ?? '';
        $anonymizedName = PlatformAccount::anonymizeName($customerName);
        
        return $this->renderSuccess([
            'customer_id' => $params['customer_id'],
            'platform_type' => $platformType,
            'customer_name_anonymized' => $anonymizedName,
            'verified' => true,
        ], '验证成功');
    }
}
