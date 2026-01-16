<?php

namespace app\common\service\message\line;

use app\common\model\Setting as SettingModel;
use app\common\model\User;
use app\common\library\line\LineMessage;

/**
 * LINE 消息通知服务基类
 * Class Basics
 * @package app\common\service\message\line
 */
abstract class Basics extends \app\common\service\Basics
{
    protected $param = [];
    
    /**
     * 发送消息通知
     * @param array $param 参数
     * @return mixed
     */
    abstract public function send($param);
    
    /**
     * 发送 LINE Flex 消息
     * @param int $wxappId 小程序ID
     * @param string $userId LINE User ID
     * @param string $messageType 消息类型 (inwarehouse, sendpack等)
     * @param array $data 模板数据
     * @return bool
     */
    protected function sendLineFlexMsg($wxappId, $userId, $messageType, $data)
    {
        try {
            // 获取 LINE 消息配置
            $config = SettingModel::getItem('line_messaging', $wxappId);
            
            // 检查是否启用
            if (empty($config['is_enable']) || $config['is_enable'] != '1') {
                $this->logMessageSend($wxappId, $userId, $messageType, false, '全局未启用');
                return false;
            }
            
            // 检查该消息类型是否启用
            if (empty($config['templates'][$messageType]) || 
                $config['templates'][$messageType]['is_enable'] != '1') {
                $this->logMessageSend($wxappId, $userId, $messageType, false, '模板未启用');
                return false;
            }
            
            $template = $config['templates'][$messageType];
            
            // 检查必需的配置
            if (empty($config['channel_id']) || empty($config['access_token'])) {
                $this->logMessageSend($wxappId, $userId, $messageType, false, '配置不完整');
                return false;
            }
            
            // 验证好友关系
            if (!$this->isFriendWithOA($userId, $wxappId)) {
                $this->logMessageSend($wxappId, $userId, $messageType, false, '用户未添加LINE OA为好友', false);
                return false;
            }
            
            // 创建 LINE 消息实例
            $lineMessage = new LineMessage(
                $config['channel_id'],
                $config['channel_secret'] ?? '',
                $config['access_token']
            );
            
            // 应用 API 配置
            if (!empty($config['api_base_url'])) {
                $lineMessage->setApiBaseUrl($config['api_base_url']);
            }
            if (isset($config['timeout'])) {
                $lineMessage->setTimeout($config['timeout']);
            }
            if (isset($config['retry_times'])) {
                $lineMessage->setRetryTimes($config['retry_times']);
            }
            
            // 渲染模板（替换变量）
            $flexContents = $this->renderTemplate($template['flex_template'], $data);
            $altText = $template['alt_text'];
            
            // 如果模板渲染失败，返回false
            if (empty($flexContents)) {
                $this->logMessageSend($wxappId, $userId, $messageType, false, '模板渲染失败');
                return false;
            }
            
            // 准备消息数组
            $messages = [];
            
            // 添加 Flex Message
            $messages[] = [
                'type' => 'flex',
                'altText' => $altText,
                'contents' => $flexContents
            ];
            
            // 检查是否需要发送图片
            if (!empty($template['send_images']) && $template['send_images'] == '1') {
                $images = $this->getMessageImages($data, $template);
                
                // 添加图片消息（最多5张，LINE API限制每次最多5条消息）
                $imageCount = 0;
                foreach ($images as $imageUrl) {
                    if ($imageCount >= 4) break; // 已有1条Flex消息，最多再加4张图片
                    
                    // 确保图片URL是HTTPS
                    $imageUrl = $this->ensureHttpsUrl($imageUrl);
                    
                    if (!empty($imageUrl)) {
                        $messages[] = [
                            'type' => 'image',
                            'originalContentUrl' => $imageUrl,
                            'previewImageUrl' => $imageUrl
                        ];
                        $imageCount++;
                    }
                }
            }
            
            // 发送消息
            $result = $lineMessage->sendMultipleMessages($userId, $messages);
            
            // 记录日志
            $this->logMessageSend($wxappId, $userId, $messageType, $result);
            
            return $result;
            
        } catch (\Exception $e) {
            // 记录错误日志
            $this->logMessageSend($wxappId, $userId, $messageType, false, $e->getMessage());
            log_write([
                'describe' => 'LINE消息发送异常',
                'wxapp_id' => $wxappId,
                'line_user_id' => $userId,
                'message_type' => $messageType,
                'error' => $e->getMessage(),
                'time' => date('Y-m-d H:i:s')
            ]);
            return false;
        }
    }
    
    /**
     * 获取消息关联的图片
     * @param array $data 消息数据
     * @param array $template 模板配置
     * @return array 图片URL数组
     */
    protected function getMessageImages($data, $template)
    {
        $images = [];
        
        // 从数据中提取图片
        // 支持多种图片字段格式
        if (!empty($data['images'])) {
            // 格式1: 直接的图片URL数组
            if (is_array($data['images'])) {
                $images = array_merge($images, $data['images']);
            }
        }
        
        if (!empty($data['packageimage'])) {
            // 格式2: PackageImage 模型数组
            if (is_array($data['packageimage'])) {
                foreach ($data['packageimage'] as $img) {
                    if (isset($img['file']['file_path'])) {
                        $images[] = $img['file']['file_path'];
                    } elseif (isset($img['file_path'])) {
                        $images[] = $img['file_path'];
                    }
                }
            }
        }
        
        if (!empty($data['image_url'])) {
            // 格式3: 单个图片URL
            $images[] = $data['image_url'];
        }
        
        // 检查模板配置的最大图片数量
        $maxImages = isset($template['max_images']) ? (int)$template['max_images'] : 3;
        
        // 限制图片数量
        if (count($images) > $maxImages) {
            $images = array_slice($images, 0, $maxImages);
        }
        
        return $images;
    }
    
    /**
     * 确保URL是HTTPS
     * @param string $url URL
     * @return string HTTPS URL
     */
    protected function ensureHttpsUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        
        // 如果是相对路径，添加域名
        if (strpos($url, 'http') !== 0) {
            // 获取当前域名
            $domain = request()->domain();
            $url = $domain . $url;
        }
        
        // 强制使用HTTPS（LINE API要求）
        $url = str_replace('http://', 'https://', $url);
        
        return $url;
    }
    
    /**
     * 渲染模板（替换变量）
     * @param array|string $template 模板
     * @param array $data 数据
     * @return array
     */
    protected function renderTemplate($template, $data)
    {
        // 如果是字符串，先转为数组
        if (is_string($template)) {
            // 先解码 HTML 实体（数据库中可能存储为 HTML 编码）
            $template = html_entity_decode($template);
            $template = json_decode($template, true);
        }
        
        // 如果解码失败，返回空数组
        if (!is_array($template)) {
            log_write([
                'describe' => 'LINE模板解析失败',
                'template_type' => gettype($template),
                'time' => date('Y-m-d H:i:s')
            ]);
            return [];
        }
        
        $json = json_encode($template, JSON_UNESCAPED_UNICODE);
        
        // 替换变量 {{variable}}
        foreach ($data as $key => $value) {
            // 跳过数组和对象类型的值（这些不应该直接替换到模板中）
            if (is_array($value) || is_object($value)) {
                continue;
            }
            
            // 确保值是字符串
            $value = (string)$value;
            
            $json = str_replace("{{" . $key . "}}", $value, $json);
        }
        
        $rendered = json_decode($json, true);
        
        // 清理空文本字段（LINE API不允许空文本）
        $rendered = $this->removeEmptyTextFields($rendered);
        
        return $rendered;
    }
    
    /**
     * 递归移除空文本字段
     * @param array $arr 数组
     * @return array
     */
    private function removeEmptyTextFields($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }
        
        $result = [];
        
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                // 检查是否是文本组件且文本为空
                if (isset($value['type']) && $value['type'] === 'text' && 
                    isset($value['text']) && trim($value['text']) === '') {
                    // 跳过空文本组件
                    continue;
                }
                
                // 递归处理
                $processed = $this->removeEmptyTextFields($value);
                
                // 如果是contents数组，需要重新索引
                if ($key === 'contents' && !empty($processed)) {
                    $result[$key] = array_values($processed);
                } else {
                    $result[$key] = $processed;
                }
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    /**
     * 根据用户ID获取 LINE User ID
     * @param int $userId 用户ID
     * @return string|null
     */
    protected function getLineUserIdByUserId($userId)
    {
        $user = User::where(['user_id' => $userId])->find();
        
        if (!$user) {
            return null;
        }
        
        // 优先使用 line_openid 字段
        if (!empty($user['line_openid'])) {
            return $user['line_openid'];
        }
        
        // 兼容旧字段 line_user_id
        if (!empty($user['line_user_id'])) {
            return $user['line_user_id'];
        }
        
        return null;
    }
    
    /**
     * 验证用户是否已添加LINE OA为好友
     * @param string $lineUserId LINE User ID
     * @param int $wxappId 小程序ID
     * @return bool
     */
    protected function isFriendWithOA($lineUserId, $wxappId)
    {
        // 检查缓存
        $cacheKey = "line_friendship_{$wxappId}_{$lineUserId}";
        $cached = cache($cacheKey);
        
        if ($cached !== null && $cached !== false) {
            return $cached === 'yes';
        }
        
        try {
            // 获取 LINE 配置
            $config = SettingModel::getItem('line_messaging', $wxappId);
            
            if (empty($config['channel_id']) || empty($config['access_token'])) {
                return false;
            }
            
            // 创建 LINE 消息实例
            $lineMessage = new LineMessage(
                $config['channel_id'],
                $config['channel_secret'] ?? '',
                $config['access_token']
            );
            
            // 应用 API 配置
            if (!empty($config['api_base_url'])) {
                $lineMessage->setApiBaseUrl($config['api_base_url']);
            }
            if (isset($config['timeout'])) {
                $lineMessage->setTimeout($config['timeout']);
            }
            
            // 调用 LINE API 获取用户资料（如果用户是好友才能获取）
            $profile = $lineMessage->getUserProfile($lineUserId);
            
            if ($profile && isset($profile['userId'])) {
                // 用户是好友，缓存24小时
                cache($cacheKey, 'yes', 86400);
                return true;
            } else {
                // 用户不是好友，缓存1小时（较短时间，因为用户可能随时添加）
                cache($cacheKey, 'no', 3600);
                return false;
            }
            
        } catch (\Exception $e) {
            // API调用失败，记录日志
            log_write([
                'describe' => 'LINE好友关系验证失败',
                'wxapp_id' => $wxappId,
                'line_user_id' => $lineUserId,
                'error' => $e->getMessage(),
                'time' => date('Y-m-d H:i:s')
            ]);
            
            // 验证失败时，假设不是好友（安全策略）
            return false;
        }
    }
    
    /**
     * 构建 LIFF 跳转链接
     * @param string $path 页面路径
     * @param array $params 查询参数
     * @param int $wxappId 小程序ID
     * @return string
     */
    protected function buildLiffUrl($path, $params = [], $wxappId = null)
    {
        $config = SettingModel::getItem('line_messaging', $wxappId);
        $liffUrl = $config['liff_url'] ?? '';
        
        // 如果没有配置 LIFF URL，返回空字符串
        if (empty($liffUrl)) {
            return '';
        }
        
        // 添加来源追踪参数
        if (!isset($params['from'])) {
            $params['from'] = 'notification';
        }
        
        $queryString = http_build_query($params);
        return $liffUrl . $path . ($queryString ? '?' . $queryString : '');
    }
    
    /**
     * 记录消息发送日志
     * @param int $wxappId 小程序ID
     * @param string $userId LINE User ID
     * @param string $messageType 消息类型
     * @param bool $result 发送结果
     * @param string $error 错误信息
     * @param bool|null $isFriend 是否好友（null表示未验证）
     */
    protected function logMessageSend($wxappId, $userId, $messageType, $result, $error = '', $isFriend = null)
    {
        // 获取配置检查是否启用日志
        $config = SettingModel::getItem('line_messaging', $wxappId);
        if (isset($config['log_enabled']) && $config['log_enabled'] != '1') {
            return;
        }
        
        $logData = [
            'describe' => 'LINE消息发送',
            'wxapp_id' => $wxappId,
            'line_user_id' => $userId,
            'message_type' => $messageType,
            'result' => $result ? 'success' : 'failed',
            'time' => date('Y-m-d H:i:s')
        ];
        
        if ($isFriend !== null) {
            $logData['is_friend'] = $isFriend ? 'yes' : 'no';
        }
        
        if (!empty($error)) {
            $logData['error'] = $error;
        }
        
        log_write($logData);
    }
}
