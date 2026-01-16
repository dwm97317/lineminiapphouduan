<?php

namespace app\api\controller;

use think\Cache;
use app\api\model\User as UserModel;

/**
 * Development Token Generator
 * For local development only
 */
class DevToken extends Controller
{
    /**
     * Generate and store a development token
     * Access: http://localhost:8080/index.php?s=api/dev_token/generate&user_id=31831&wxapp_id=10001
     */
    public function generate()
    {
        // Get parameters
        $userId = $this->request->param('user_id', 31831, 'intval');
        $wxappId = $this->request->param('wxapp_id', 10001, 'intval');
        
        // Get user
        $user = UserModel::useGlobalScope(false)
            ->where(['user_id' => $userId, 'wxapp_id' => $wxappId, 'is_delete' => 0])
            ->find();
        
        if (!$user) {
            // Show available users
            $users = UserModel::useGlobalScope(false)
                ->where(['wxapp_id' => 10001, 'is_delete' => 0])
                ->limit(10)
                ->column('user_id,nickName,mobile');
            
            return $this->renderError('User not found', null, [
                'available_users' => $users
            ]);
        }
        
        // Generate token (same as Login service)
        $guid = get_guid_v4();
        $timeStamp = microtime(true);
        $salt = 'user_salt';
        $token = md5("{$wxappId}_{$timeStamp}_{$userId}_{$guid}_{$salt}");
        
        // Store in cache (30 days)
        $cacheData = [
            'user' => $user,
            'openid' => $user['open_id'],
            'store_id' => $wxappId,
            'is_login' => true,
        ];
        
        Cache::set($token, $cacheData, 86400 * 30);
        
        // Verify
        $cached = Cache::get($token);
        if (!$cached || !isset($cached['user'])) {
            return $this->renderError('Failed to store token in cache');
        }
        
        return $this->renderSuccess([
            'token' => $token,
            'user_id' => $userId,
            'wxapp_id' => $wxappId,
            'user' => [
                'user_id' => $user['user_id'],
                'nickName' => $user['nickName'],
                'mobile' => $user['mobile'],
            ],
            'expires_in' => 86400 * 30,
            'frontend_code' => "localStorage.setItem('token', '{$token}'); localStorage.setItem('userId', '{$userId}'); window.location.reload();",
            'test_urls' => [
                'storage_list' => "http://localhost:8080/index.php?s=api/page/storage_list&wxapp_id={$wxappId}&token={$token}",
                'claim_list' => "http://localhost:8080/index.php?s=api/package/claim_list&wxapp_id={$wxappId}&token={$token}",
            ]
        ], 'Token generated and stored successfully');
    }
}
