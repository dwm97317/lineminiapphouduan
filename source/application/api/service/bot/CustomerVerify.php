<?php

namespace app\api\service\bot;

use app\common\service\BaseService;
use cores\exception\BaseException;

/**
 * Bot Customer 验证服务
 * Class CustomerVerify
 * @package app\api\service\bot
 */
class CustomerVerify extends BaseService
{
    /**
     * Bot API 基础 URL
     * 可以在配置中自定义
     */
    const BOT_API_BASE_URL = 'http://localhost:3000/api/bot'; // TODO: 改为实际 Bot 服务地址
    
    /**
     * 验证 Customer ID
     * 调用 Bot API: GET /api/bot/customer/verify
     * 
     * @param string $customerId
     * @param string $platformType
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public static function verifyCustomerId($customerId, $platformType = 'FACEBOOK')
    {
        try {
            // 构建请求 URL
            $url = self::getBotApiUrl() . '/customer/verify';
            
            // 准备请求参数
            $params = [
                'customer_id' => $customerId,
                'platform' => strtolower($platformType),
            ];
            
            // 发送 HTTP GET 请求
            $response = self::sendGetRequest($url, $params);
            
            // 解析响应
            if ($response === false) {
                return [
                    'success' => false,
                    'message' => 'Bot 服务连接失败，请稍后重试',
                    'data' => [],
                ];
            }
            
            $result = json_decode($response, true);
            
            // 检查 Bot API 响应
            if (!isset($result['success']) || !$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message'] ?? 'Customer ID 验证失败',
                    'data' => [],
                ];
            }
            
            // 验证成功，返回客户信息
            return [
                'success' => true,
                'message' => '验证成功',
                'data' => [
                    'customer_id' => $result['data']['customer_id'] ?? $customerId,
                    'customer_name' => $result['data']['customer_name'] ?? '',
                    'platform' => $platformType,
                    'is_valid' => true,
                ],
            ];
            
        } catch (\Exception $e) {
            // 记录错误日志
            \think\Log::record(sprintf(
                '[Bot Customer Verify] Error: %s, CustomerID: %s, Platform: %s',
                $e->getMessage(),
                $customerId,
                $platformType
            ), 'error');
            
            return [
                'success' => false,
                'message' => '验证服务异常：' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * 获取 Bot API 基础 URL
     * 可以从配置中读取
     * 
     * @return string
     */
    private static function getBotApiUrl()
    {
        // TODO: 从配置文件读取 Bot API 地址
        // 示例：return config('app.bot_api_url');
        return self::BOT_API_BASE_URL;
    }

    /**
     * 发送 HTTP GET 请求
     * 
     * @param string $url
     * @param array $params
     * @param int $timeout
     * @return bool|string
     */
    private static function sendGetRequest($url, $params = [], $timeout = 10)
    {
        try {
            // 构建查询字符串
            $queryString = http_build_query($params);
            $fullUrl = $url . '?' . $queryString;
            
            // 初始化 cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 生产环境应设为 true
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            // 设置请求头
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
            ]);
            
            // 执行请求
            $response = curl_exec($ch);
            
            // 检查错误
            if (curl_errno($ch)) {
                \think\Log::record('[Bot HTTP Request] cURL Error: ' . curl_error($ch), 'error');
                curl_close($ch);
                return false;
            }
            
            // 检查 HTTP 状态码
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                \think\Log::record('[Bot HTTP Request] HTTP Error: ' . $httpCode, 'error');
                curl_close($ch);
                return false;
            }
            
            curl_close($ch);
            return $response;
            
        } catch (\Exception $e) {
            \think\Log::record('[Bot HTTP Request] Exception: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 发送 HTTP POST 请求 (备用方法)
     * 
     * @param string $url
     * @param array $data
     * @param int $timeout
     * @return bool|string
     */
    private static function sendPostRequest($url, $data = [], $timeout = 10)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            // 设置请求头
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen(json_encode($data)),
            ]);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                \think\Log::record('[Bot HTTP Request] cURL Error: ' . curl_error($ch), 'error');
                curl_close($ch);
                return false;
            }
            
            curl_close($ch);
            return $response;
            
        } catch (\Exception $e) {
            \think\Log::record('[Bot HTTP Request] Exception: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}
