<?php

namespace app\api\controller\bot;

use app\api\model\PlatformAccount;
use app\api\model\User as UserModel;
use app\common\exception\BaseException;

/**
 * Bot 客户验证控制器
 * Class Customer
 * @package app\api\controller\bot
 */
class Customer extends \app\api\controller\Controller
{
    /**
     * 验证 Customer ID
     * GET /api/bot/customer/verify
     * 
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function verify()
    {
        // 获取参数
        $customerId = $this->request->param('customer_id');
        $platform = $this->request->param('platform', 'facebook');
        
        if (!$customerId) {
            throw new BaseException([
                'code' => 400,
                'msg' => '缺少参数：customer_id',
            ]);
        }
        
        // 验证平台类型
        $allowedPlatforms = ['facebook', 'instagram'];
        if (!in_array(strtolower($platform), $allowedPlatforms)) {
            throw new BaseException([
                'code' => 400,
                'msg' => '不支持的平台类型',
            ]);
        }
        
        // 查询 Customer ID 是否存在于系统中
        // 这里假设 Customer ID 对应用户的 mobile 或 user_id
        // 实际业务逻辑可能需要调整
        
        $user = $this->findUserByCustomerId($customerId);
        
        if (!$user) {
            return $this->renderError([
                'success' => false,
                'message' => 'Customer ID 不存在或无效',
            ]);
        }
        
        // 检查该 Customer ID 是否已被绑定
        $wxappId = $this->wxapp_id;
        $existingBinding = PlatformAccount::getByCustomerId($customerId, $wxappId);
        
        if ($existingBinding && $existingBinding['status'] == 1) {
            // 已被其他用户绑定
            if ($existingBinding['user_id'] != $user['user_id']) {
                return $this->renderError([
                    'success' => false,
                    'message' => '该 Customer ID 已被其他账户绑定',
                ]);
            }
        }
        
        // 匿名化显示用户名
        $anonymizedName = PlatformAccount::anonymizeName($user['nickName']);
        
        // 返回成功响应
        return $this->renderSuccess([
            'success' => true,
            'message' => '验证成功',
            'data' => [
                'customer_id' => $customerId,
                'customer_name' => $user['nickName'],
                'customer_name_anonymized' => $anonymizedName,
                'platform' => ucfirst(strtolower($platform)),
                'is_valid' => true,
                'user_id' => $user['user_id'],
            ],
        ]);
    }

    /**
     * 根据 Customer ID 查找用户
     * Customer ID 可能对应手机号、user_id 或其他标识
     * 
     * @param string $customerId
     * @return array|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function findUserByCustomerId($customerId)
    {
        // 尝试多种方式查找用户
        
        // 1. 如果 Customer ID 是手机号
        if (preg_match('/^\d{9,15}$/', $customerId)) {
            $user = UserModel::useGlobalScope(false)
                ->where(['mobile' => $customerId, 'wxapp_id' => $this->wxapp_id])
                ->find();
            
            if ($user) {
                return $user;
            }
        }
        
        // 2. 如果 Customer ID 包含用户 ID 信息 (例如：CUST_123456)
        if (preg_match('/^CUST_(\d+)$/i', $customerId, $matches)) {
            $userId = (int)$matches[1];
            $user = UserModel::useGlobalScope(false)
                ->where(['user_id' => $userId, 'wxapp_id' => $this->wxapp_id])
                ->find();
            
            if ($user) {
                return $user;
            }
        }
        
        // 3. 从 platform_account 表反查
        $binding = PlatformAccount::getByCustomerId($customerId, $this->wxapp_id);
        if ($binding && $binding['status'] == 1) {
            $user = UserModel::useGlobalScope(false)
                ->where(['user_id' => $binding['user_id'], 'wxapp_id' => $this->wxapp_id])
                ->find();
            
            if ($user) {
                return $user;
            }
        }
        
        // 4. 自定义匹配逻辑 (可根据实际需求扩展)
        // 例如：匹配 email、openid 等
        
        return null;
    }

    /**
     * 获取客户详细信息
     * GET /api/bot/customer/info
     * 
     * @return array
     * @throws BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function info()
    {
        $customerId = $this->request->param('customer_id');
        
        if (!$customerId) {
            throw new BaseException([
                'code' => 400,
                'msg' => '缺少参数：customer_id',
            ]);
        }
        
        $user = $this->findUserByCustomerId($customerId);
        
        if (!$user) {
            return $this->renderError([
                'success' => false,
                'message' => '客户不存在',
            ]);
        }
        
        // 返回脱敏后的用户信息
        return $this->renderSuccess([
            'success' => true,
            'data' => [
                'customer_id' => $customerId,
                'nickname' => $user['nickName'],
                'avatar' => $user['avatarUrl'] ?? '',
                'mobile' => $this->maskMobile($user['mobile'] ?? ''),
                'platform_type' => 'FACEBOOK', // 默认
            ],
        ]);
    }

    /**
     * 隐藏手机号码中间位
     * 
     * @param string $mobile
     * @return string
     */
    protected function maskMobile($mobile)
    {
        if (strlen($mobile) >= 7) {
            return substr_replace($mobile, '****', 3, 4);
        }
        return $mobile;
    }
}
