<?php

namespace app\common\library\line;

/**
 * LINE Messaging API 客户端
 * Class LineMessage
 * @package app\common\library\line
 */
class LineMessage
{
    private $channelId;
    private $channelSecret;
    private $accessToken;
    private $apiBaseUrl = 'https://api.line.me/v2/bot';
    private $timeout = 30;
    private $retryTimes = 3;
    
    /**
     * 构造函数
     * @param string $channelId Channel ID
     * @param string $channelSecret Channel Secret
     * @param string $accessToken Channel Access Token
     */
    public function __construct($channelId, $channelSecret, $accessToken)
    {
        $this->channelId = $channelId;
        $this->channelSecret = $channelSecret;
        $this->accessToken = $accessToken;
    }
    
    /**
     * 设置 API Base URL
     * @param string $url API Base URL
     */
    public function setApiBaseUrl($url)
    {
        $this->apiBaseUrl = rtrim($url, '/');
    }
    
    /**
     * 设置超时时间
     * @param int $timeout 超时时间（秒）
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }
    
    /**
     * 设置重试次数
     * @param int $retryTimes 重试次数
     */
    public function setRetryTimes($retryTimes)
    {
        $this->retryTimes = $retryTimes;
    }
    
    /**
     * 发送 Flex Message
     * @param string $userId LINE User ID
     * @param string $altText 替代文本
     * @param array $contents Flex Message 内容
     * @return bool
     */
    public function sendFlexMessage($userId, $altText, $contents)
    {
        $message = [
            'type' => 'flex',
            'altText' => $altText,
            'contents' => $contents
        ];
        
        return $this->pushMessage($userId, [$message]);
    }
    
    /**
     * 发送文本消息
     * @param string $userId LINE User ID
     * @param string $text 文本内容
     * @return bool
     */
    public function sendTextMessage($userId, $text)
    {
        $message = [
            'type' => 'text',
            'text' => $text
        ];
        
        return $this->pushMessage($userId, [$message]);
    }
    
    /**
     * 发送图片消息
     * @param string $userId LINE User ID
     * @param string $originalContentUrl 原始图片URL（必须是HTTPS）
     * @param string $previewImageUrl 预览图片URL（必须是HTTPS）
     * @return bool
     */
    public function sendImageMessage($userId, $originalContentUrl, $previewImageUrl = null)
    {
        // 如果没有提供预览图，使用原图
        if (empty($previewImageUrl)) {
            $previewImageUrl = $originalContentUrl;
        }
        
        $message = [
            'type' => 'image',
            'originalContentUrl' => $originalContentUrl,
            'previewImageUrl' => $previewImageUrl
        ];
        
        return $this->pushMessage($userId, [$message]);
    }
    
    /**
     * 发送多条消息（Flex Message + 图片）
     * @param string $userId LINE User ID
     * @param array $messages 消息数组
     * @return bool
     */
    public function sendMultipleMessages($userId, $messages)
    {
        return $this->pushMessage($userId, $messages);
    }
    
    /**
     * 获取用户资料
     * 注意：只有当用户是LINE OA的好友时，才能成功获取资料
     * @param string $userId LINE User ID
     * @return array|false 用户资料或false
     */
    public function getUserProfile($userId)
    {
        $url = $this->apiBaseUrl . "/profile/{$userId}";
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        // 如果返回404或403，说明用户不是好友
        return false;
    }
    
    /**
     * 推送消息
     * @param string $userId LINE User ID
     * @param array $messages 消息数组
     * @return bool
     */
    private function pushMessage($userId, $messages)
    {
        $url = $this->apiBaseUrl . '/message/push';
        
        $data = [
            'to' => $userId,
            'messages' => $messages
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];
        
        $response = $this->httpPost($url, json_encode($data), $headers);
        
        // 检查响应
        if ($response === false) {
            return false;
        }
        
        $result = json_decode($response, true);
        
        // LINE API 成功时返回空响应或包含 sentMessages 的响应
        // 如果有错误，会返回包含 message 字段的 JSON
        if (isset($result['message'])) {
            // 记录错误
            log_write([
                'describe' => 'LINE API 错误',
                'error' => $result['message'],
                'details' => $result['details'] ?? '',
                'time' => date('Y-m-d H:i:s')
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * HTTP POST 请求（带重试机制）
     * @param string $url 请求URL
     * @param string $data 请求数据
     * @param array $headers 请求头
     * @return string|false
     */
    private function httpPost($url, $data, $headers = [])
    {
        $attempt = 0;
        $lastError = '';
        
        while ($attempt < $this->retryTimes) {
            $attempt++;
            
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            curl_close($ch);
            
            // 成功返回
            if ($httpCode == 200 && !$error) {
                return $response;
            }
            
            // 记录错误信息
            $lastError = $error ?: "HTTP $httpCode";
            
            // 如果不是最后一次尝试，等待后重试
            if ($attempt < $this->retryTimes) {
                usleep(500000); // 等待 0.5 秒
            }
        }
        
        // 所有重试都失败，记录日志
        log_write([
            'describe' => 'LINE API 请求失败（已重试 ' . $this->retryTimes . ' 次）',
            'url' => $url,
            'last_error' => $lastError,
            'time' => date('Y-m-d H:i:s')
        ]);
        
        return false;
    }
}
