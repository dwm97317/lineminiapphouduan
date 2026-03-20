<?php

namespace app\api\controller;

use app\api\model\PlatformAccount;
use app\api\service\bot\CustomerVerify as CustomerVerifyService;

/**
 * 账户绑定控制器 (FB/IG Bot)
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
        
        // 检查该 Customer ID 是否已被绑定
        if (PlatformAccount::isCustomerBound($customerId, $wxappId)) {
            return $this->renderError('该 Customer ID 已被其他账户绑定');
        }
        
        // 检查该用户是否已绑定同平台的账户
        if (PlatformAccount::isUserBound($userId, $platformType, $wxappId)) {
            return $this->renderError('您已绑定该平台的账户，一个平台只能绑定一个 Customer ID');
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
            ], '绑定成功！已关联账户：' . $anonymizedName);
            
        } catch (\Exception $e) {
            return $this->renderError('绑定失败：' . $e->getMessage());
        }
    }

    /**
     * 查询已绑定的账户列表
     * GET /api/v1/account/bindings
     * 
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bindings()
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
            $result[] = [
                'id' => $binding['id'],
                'platform_type' => $binding['platform_type'],
                'customer_id' => $binding['customer_id'],
                'customer_name_anonymized' => $binding['is_anonymized'] 
                    ? PlatformAccount::anonymizeName($binding['customer_name'])
                    : $binding['customer_name'],
                'binding_time' => $binding['binding_time'],
                'last_verify_time' => $binding['last_verify_time'],
                'status' => $binding['status'],
            ];
        }
        
        return $this->renderSuccess([
            'list' => $result,
            'total' => count($result),
        ]);
    }

    /**
     * 解绑账户
     * POST /api/v1/account/unbind
     * 
     * @return array
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
        
        // 删除绑定
        if ($binding->delete()) {
            return $this->renderSuccess([], '解绑成功');
        } else {
            return $this->renderError('解绑失败');
        }
    }

    /**
     * 验证 Customer ID (独立接口，用于 Bot 端验证)
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
