<?php

namespace app\api\middleware;

use think\Cache;
use app\common\exception\BaseException;

/**
 * Bot API 认证中间件
 * 验证 API Key 的有效性
 * Class BotAuth
 * @package app\api\middleware
 */
class BotAuth
{
    /**
     * 允许的 API Keys (可以从数据库或配置读取)
     * 格式：'api_key' => ['name' => 'Bot Name', 'wxapp_ids' => [10001, 10002]]
     */
    const ALLOWED_API_KEYS = [
        // 示例配置，实际应从数据库或配置文件读取
        // 'sk_test_1234567890abcdef' => [
        //     'name' => 'FB_Bot_Test',
        //     'wxapp_ids' => [10001], // 允许访问的商户 ID
        // ],
    ];

    /**
     * 中间件执行入口
     * @param \think\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws BaseException
     */
    public function handle($request, \Closure $next)
    {
        // 从 Header 获取 API Key
        $apiKey = $request->header('X-Bot-API-Key');
        
        if (!$apiKey) {
            // 尝试从参数获取
            $apiKey = $request->param('api_key');
        }
        
        if (!$apiKey) {
            throw new BaseException([
                'code' => 401,
                'msg' => '缺少 API Key',
            ]);
        }
        
        // 验证 API Key
        if (!$this->validateApiKey($apiKey)) {
            throw new BaseException([
                'code' => 401,
                'msg' => '无效的 API Key',
            ]);
        }
        
        // 检查 API Key 是否有权限访问当前 wxapp_id
        $wxappId = $request->param('wxapp_id');
        if ($wxappId && !$this->checkWxappPermission($apiKey, (int)$wxappId)) {
            throw new BaseException([
                'code' => 403,
                'msg' => 'API Key 无权访问该商户',
            ]);
        }
        
        // 将 API Key 信息存入请求，供后续使用
        $request->bot_auth = $this->getApiKeyInfo($apiKey);
        
        return $next($request);
    }

    /**
     * 验证 API Key
     * @param string $apiKey
     * @return bool
     */
    protected function validateApiKey($apiKey)
    {
        // 1. 检查静态配置
        if (isset(self::ALLOWED_API_KEYS[$apiKey])) {
            return true;
        }
        
        // 2. 从缓存检查 (支持动态添加的 API Keys)
        $cachedKey = Cache::get('bot_api_key:' . $apiKey);
        if ($cachedKey && isset($cachedKey['is_valid']) && $cachedKey['is_valid']) {
            return true;
        }
        
        // 3. 从数据库检查 (可选，需要创建 bot_api_keys 表)
        // $dbKey = Db::name('bot_api_keys')
        //     ->where(['api_key' => $apiKey, 'is_valid' => 1])
        //     ->find();
        // if ($dbKey) {
        //     return true;
        // }
        
        return false;
    }

    /**
     * 检查 API Key 对指定 wxapp_id 的访问权限
     * @param string $apiKey
     * @param int $wxappId
     * @return bool
     */
    protected function checkWxappPermission($apiKey, $wxappId)
    {
        $keyInfo = $this->getApiKeyInfo($apiKey);
        
        if (!isset($keyInfo['wxapp_ids'])) {
            // 没有限制，允许访问所有
            return true;
        }
        
        if (in_array('*', $keyInfo['wxapp_ids'])) {
            // 通配符，允许访问所有
            return true;
        }
        
        return in_array($wxappId, $keyInfo['wxapp_ids']);
    }

    /**
     * 获取 API Key 信息
     * @param string $apiKey
     * @return array
     */
    protected function getApiKeyInfo($apiKey)
    {
        // 优先从缓存获取
        $cached = Cache::get('bot_api_key:' . $apiKey);
        if ($cached) {
            return $cached;
        }
        
        // 返回静态配置
        return self::ALLOWED_API_KEYS[$apiKey] ?? [];
    }
}
