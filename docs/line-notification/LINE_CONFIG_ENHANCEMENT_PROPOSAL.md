# LINE 消息通知配置增强方案

> 基于现有 `/store/setting.line_config/index` 配置结构，增强 `line_messaging` 消息通知功能
> 将 LINE_OA_NOTIFICATION_SPEC.md 中的所有消息模板整合到配置中

## 一、现有配置结构分析

### 1.1 当前配置

```php
// Setting.php 中的现有配置
'line_messaging' => [
    'key' => 'line_messaging',
    'describe' => 'LINE消息通知',
    'values' => [
        'is_enable' => '0',
        'channel_id' => '',
        'channel_secret' => '',
        'access_token' => '',
        'template' => [
            'enter' => [
                'is_enable' => '0',
                'content' => '您的包裹${code}已入库，仓库：${warehouse}'
            ],
            'delivery' => [
                'is_enable' => '0',
                'content' => '您的订单${order_no}已发货，物流单号：${express_no}'
            ],
        ]
    ]
],
```

### 1.2 问题分析

- ✅ 已有基础结构（channel_id, access_token）
- ✅ 已有模板概念（template 数组）
- ❌ 模板类型不完整（只有 enter 和 delivery）
- ❌ 缺少 Flex Message 模板定义
- ❌ 缺少 alt_text、优先级等配置

## 二、增强方案

### 2.1 扩展 line_messaging 配置

```php
// Setting.php - 增强后的配置
'line_messaging' => [
    'key' => 'line_messaging',
    'describe' => 'LINE消息通知',
    'values' => [
        // === Channel 配置 ===
        'is_enable' => '0',
        'channel_id' => '',
        'channel_secret' => '',
        'access_token' => '',
        
        // === API 设置 ===
        'api_base_url' => 'https://api.line.me/v2/bot',
        'timeout' => 30,
        'retry_times' => 3,
        'log_enabled' => '1',
        
        // === LIFF 配置（用于消息跳转链接）===
        'liff_id' => '',
        'liff_url' => '',
        
        // === 消息模板配置 ===
        'templates' => [
            // 入库通知
            'inwarehouse' => [
                'is_enable' => '0',
                'name' => '包裹入库通知',
                'alt_text' => '📦 包裹入库通知',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => '📦 包裹入库通知',
                                'weight' => 'bold',
                                'size' => 'lg',
                                'color' => '#1DB446'
                            ]
                        ],
                        'backgroundColor' => '#F0FFF0'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '仓库：{{shop_name}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '快递单号：{{express_num}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '入库时间：{{entering_warehouse_time}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'separator', 'margin' => 'md'],
                            ['type' => 'text', 'text' => '{{remark}}', 'size' => 'sm', 'color' => '#888888', 'margin' => 'md', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => ['type' => 'uri', 'label' => '查看详情', 'uri' => '{{detail_url}}'],
                                'style' => 'primary',
                                'color' => '#1DB446'
                            ]
                        ]
                    ]
                ],
                'variables' => ['shop_name', 'express_num', 'entering_warehouse_time', 'weight', 'remark', 'detail_url']
            ],
            
            // 发货通知
            'sendpack' => [
                'is_enable' => '0',
                'name' => '发货通知',
                'alt_text' => '🚚 发货通知',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '🚚 发货通知', 'weight' => 'bold', 'size' => 'lg', 'color' => '#0066CC']
                        ],
                        'backgroundColor' => '#E6F3FF'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '国际单号：{{t_order_sn}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '线路：{{t_name}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '发货时间：{{send_time}}', 'size' => 'sm', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => ['type' => 'uri', 'label' => '查看物流', 'uri' => '{{tracking_url}}'],
                                'style' => 'primary',
                                'color' => '#0066CC'
                            ]
                        ]
                    ]
                ],
                'variables' => ['order_sn', 't_order_sn', 'weight', 't_name', 'send_time', 'tracking_url']
            ],
            
            // 支付成功通知
            'payment' => [
                'is_enable' => '0',
                'name' => '支付成功通知',
                'alt_text' => '✅ 支付成功',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '✅ 支付成功', 'weight' => 'bold', 'size' => 'lg', 'color' => '#FF6B00']
                        ],
                        'backgroundColor' => '#FFF5E6'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '支付金额：¥{{total_free}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '支付时间：{{pay_time}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'separator', 'margin' => 'md'],
                            ['type' => 'text', 'text' => '{{remark}}', 'size' => 'sm', 'color' => '#888888', 'margin' => 'md', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ]
                ],
                'variables' => ['order_sn', 'total_free', 'pay_time', 'remark']
            ],
            
            // 打包完成通知
            'dabaosuccess' => [
                'is_enable' => '0',
                'name' => '打包完成通知',
                'alt_text' => '📋 打包完成',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '📋 打包完成', 'weight' => 'bold', 'size' => 'lg', 'color' => '#9933FF']
                        ],
                        'backgroundColor' => '#F5E6FF'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '包裹数量：{{pack_count}}件', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '总重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '体积：{{volume}}cm³', 'size' => 'sm', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => ['type' => 'uri', 'label' => '去支付', 'uri' => '{{pay_url}}'],
                                'style' => 'primary',
                                'color' => '#9933FF'
                            ]
                        ]
                    ]
                ],
                'variables' => ['order_sn', 'pack_count', 'weight', 'volume', 'pay_url']
            ],
            
            // 付款单生成通知
            'payorder' => [
                'is_enable' => '0',
                'name' => '付款单生成通知',
                'alt_text' => '💰 付款单生成',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '💰 付款单生成', 'weight' => 'bold', 'size' => 'lg', 'color' => '#FF3366']
                        ],
                        'backgroundColor' => '#FFE6F0'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '订单号：{{order_sn}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '应付金额：¥{{amount}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '生成时间：{{create_time}}', 'size' => 'sm', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => ['type' => 'uri', 'label' => '立即支付', 'uri' => '{{pay_url}}'],
                                'style' => 'primary',
                                'color' => '#FF3366'
                            ]
                        ]
                    ]
                ],
                'variables' => ['order_sn', 'amount', 'create_time', 'pay_url']
            ],
            
            // 到仓通知
            'toshop' => [
                'is_enable' => '0',
                'name' => '到仓通知',
                'alt_text' => '🏪 到仓通知',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '🏪 到仓通知', 'weight' => 'bold', 'size' => 'lg', 'color' => '#00CC99']
                        ],
                        'backgroundColor' => '#E6FFF5'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '仓库：{{shop_name}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '快递单号：{{express_num}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '到仓时间：{{arrival_time}}', 'size' => 'sm', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ]
                ],
                'variables' => ['shop_name', 'express_num', 'arrival_time']
            ],
            
            // 出库申请通知
            'outapply' => [
                'is_enable' => '0',
                'name' => '出库申请通知',
                'alt_text' => '📤 出库申请',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => [
                    'type' => 'bubble',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '📤 出库申请', 'weight' => 'bold', 'size' => 'lg', 'color' => '#FF9900']
                        ],
                        'backgroundColor' => '#FFF5E6'
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            ['type' => 'text', 'text' => '申请单号：{{apply_sn}}', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '包裹数量：{{package_count}}件', 'size' => 'sm', 'wrap' => true],
                            ['type' => 'text', 'text' => '申请时间：{{apply_time}}', 'size' => 'sm', 'wrap' => true]
                        ],
                        'spacing' => 'sm'
                    ]
                ],
                'variables' => ['apply_sn', 'package_count', 'apply_time']
            ]
        ]
    ]
],
```


## 三、代码实现

### 3.1 更新 Setting.php 模型

```php
<?php
// source/application/common/model/Setting.php

// 在 defaultData() 方法中更新 line_messaging 配置
'line_messaging' => [
    'key' => 'line_messaging',
    'describe' => 'LINE消息通知',
    'values' => [
        // Channel 配置
        'is_enable' => '0',
        'channel_id' => '',
        'channel_secret' => '',
        'access_token' => '',
        
        // API 设置
        'api_base_url' => 'https://api.line.me/v2/bot',
        'timeout' => 30,
        'retry_times' => 3,
        'log_enabled' => '1',
        
        // LIFF 配置
        'liff_id' => '',
        'liff_url' => '',
        
        // 消息模板配置
        'templates' => [
            'inwarehouse' => [
                'is_enable' => '0',
                'name' => '包裹入库通知',
                'alt_text' => '📦 包裹入库通知',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}', // 完整的 Flex Message JSON
                'variables' => ['shop_name', 'express_num', 'entering_warehouse_time', 'weight', 'remark', 'detail_url']
            ],
            'sendpack' => [
                'is_enable' => '0',
                'name' => '发货通知',
                'alt_text' => '🚚 发货通知',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}',
                'variables' => ['order_sn', 't_order_sn', 'weight', 't_name', 'send_time', 'tracking_url']
            ],
            'payment' => [
                'is_enable' => '0',
                'name' => '支付成功通知',
                'alt_text' => '✅ 支付成功',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}',
                'variables' => ['order_sn', 'total_free', 'pay_time', 'remark']
            ],
            'dabaosuccess' => [
                'is_enable' => '0',
                'name' => '打包完成通知',
                'alt_text' => '📋 打包完成',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}',
                'variables' => ['order_sn', 'pack_count', 'weight', 'volume', 'pay_url']
            ],
            'payorder' => [
                'is_enable' => '0',
                'name' => '付款单生成通知',
                'alt_text' => '💰 付款单生成',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}',
                'variables' => ['order_sn', 'amount', 'create_time', 'pay_url']
            ],
            'toshop' => [
                'is_enable' => '0',
                'name' => '到仓通知',
                'alt_text' => '🏪 到仓通知',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}',
                'variables' => ['shop_name', 'express_num', 'arrival_time']
            ],
            'outapply' => [
                'is_enable' => '0',
                'name' => '出库申请通知',
                'alt_text' => '📤 出库申请',
                'priority' => 'normal',
                'send_delay' => 0,
                'flex_template' => '{{FLEX_TEMPLATE_JSON}}',
                'variables' => ['apply_sn', 'package_count', 'apply_time']
            ]
        ]
    ]
],
```

### 3.2 创建 LINE 消息服务基类

```php
<?php
namespace app\common\service\message\line;

use app\common\model\Setting as SettingModel;
use app\common\model\User;
use app\common\library\line\LineMessage;

/**
 * LINE 消息通知服务基类
 */
abstract class Basics extends \app\common\service\Basics
{
    protected $param = [];
    
    /**
     * 发送消息通知
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
        // 获取 LINE 消息配置
        $config = SettingModel::getItem('line_messaging', $wxappId);
        
        // 检查是否启用
        if (empty($config['is_enable']) || $config['is_enable'] != '1') {
            return false;
        }
        
        // 检查该消息类型是否启用
        if (empty($config['templates'][$messageType]) || 
            $config['templates'][$messageType]['is_enable'] != '1') {
            return false;
        }
        
        $template = $config['templates'][$messageType];
        
        // 创建 LINE 消息实例
        $lineMessage = new LineMessage(
            $config['channel_id'],
            $config['channel_secret'],
            $config['access_token']
        );
        
        // 渲染模板（替换变量）
        $flexContents = $this->renderTemplate($template['flex_template'], $data);
        $altText = $template['alt_text'];
        
        // 发送消息
        $result = $lineMessage->sendFlexMessage($userId, $altText, $flexContents);
        
        // 记录日志
        $this->logMessageSend($wxappId, $userId, $messageType, $result);
        
        return $result;
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
            $template = json_decode($template, true);
        }
        
        $json = json_encode($template, JSON_UNESCAPED_UNICODE);
        
        // 替换变量 {{variable}}
        foreach ($data as $key => $value) {
            $json = str_replace("{{" . $key . "}}", $value, $json);
        }
        
        return json_decode($json, true);
    }
    
    /**
     * 根据用户ID获取 LINE User ID
     */
    protected function getLineUserIdByUserId($userId)
    {
        return User::where(['user_id' => $userId])->value('line_user_id');
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
        
        $queryString = http_build_query($params);
        return $liffUrl . $path . ($queryString ? '?' . $queryString : '');
    }
    
    /**
     * 记录消息发送日志
     */
    protected function logMessageSend($wxappId, $userId, $messageType, $result)
    {
        log_write([
            'describe' => 'LINE消息发送',
            'wxapp_id' => $wxappId,
            'line_user_id' => $userId,
            'message_type' => $messageType,
            'result' => $result ? 'success' : 'failed',
            'time' => date('Y-m-d H:i:s')
        ]);
    }
}
```

### 3.3 入库通知场景类

```php
<?php
namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 包裹入库
 */
class Inwarehouse extends Basics
{
    protected $param = [];
    
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        $wxappId = $orderInfo['wxapp_id'];
        
        // 获取用户 LINE ID
        $lineUserId = $this->getLineUserIdByUserId($orderInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        // 构建详情页链接
        $detailUrl = $this->buildLiffUrl(
            '/package/detail',
            ['id' => $orderInfo['id'], 'rtype' => 10],
            $wxappId
        );
        
        // 构建模板数据
        $data = [
            'shop_name' => $orderInfo['shop_name'] ?? '',
            'express_num' => $orderInfo['express_num'] ?? '',
            'entering_warehouse_time' => $orderInfo['entering_warehouse_time'] ?? '',
            'weight' => $orderInfo['weight'] ?? 0,
            'remark' => $orderInfo['remark'] ?? '包裹已入库，可提交打包',
            'detail_url' => $detailUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'inwarehouse', $data);
    }
}
```

### 3.4 发货通知场景类

```php
<?php
namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 发货通知
 */
class Sendpack extends Basics
{
    protected $param = [];
    
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        $wxappId = $orderInfo['wxapp_id'];
        
        // 获取用户 LINE ID
        $lineUserId = $this->getLineUserIdByUserId($orderInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        // 构建物流跟踪链接
        $trackingUrl = $this->buildLiffUrl(
            '/tracking',
            ['order_sn' => $orderInfo['order_sn']],
            $wxappId
        );
        
        // 构建模板数据
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            't_order_sn' => $orderInfo['t_order_sn'] ?? '',
            'weight' => $orderInfo['weight'] ?? 0,
            't_name' => $orderInfo['t_name'] ?? '默认',
            'send_time' => $orderInfo['send_time'] ?? date('Y-m-d H:i:s'),
            'tracking_url' => $trackingUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'sendpack', $data);
    }
}
```

### 3.5 更新 Message 分发服务

```php
<?php
namespace app\common\service;

/**
 * 消息通知服务
 */
class Message extends Basics
{
    // 微信场景列表（保持不变）
    private static $wxSceneList = [
        'package.inwarehouse' => 'app\common\service\message\package\Inwarehouse',
        // ... 其他微信场景
    ];
    
    // LINE 场景列表（新增）
    private static $lineSceneList = [
        'package.inwarehouse' => 'app\common\service\message\line\Inwarehouse',
        'package.sendpack' => 'app\common\service\message\line\Sendpack',
        'package.payment' => 'app\common\service\message\line\Payment',
        'package.dabaosuccess' => 'app\common\service\message\line\Dabaosuccess',
        'package.payorder' => 'app\common\service\message\line\Payorder',
        'package.toshop' => 'app\common\service\message\line\Toshop',
        'package.outapply' => 'app\common\service\message\line\Outapply',
    ];
    
    /**
     * 发送消息通知（同时发送微信和LINE）
     */
    public static function send($sceneName, $param)
    {
        $wxResult = self::sendWx($sceneName, $param);
        $lineResult = self::sendLine($sceneName, $param);
        
        return $wxResult || $lineResult;
    }
    
    /**
     * 发送微信消息
     */
    public static function sendWx($sceneName, $param)
    {
        if (!isset(self::$wxSceneList[$sceneName])) {
            return false;
        }
        $className = self::$wxSceneList[$sceneName];
        return class_exists($className) ? (new $className)->send($param) : false;
    }
    
    /**
     * 发送 LINE 消息
     */
    public static function sendLine($sceneName, $param)
    {
        if (!isset(self::$lineSceneList[$sceneName])) {
            return false;
        }
        $className = self::$lineSceneList[$sceneName];
        return class_exists($className) ? (new $className)->send($param) : false;
    }
}
```


## 四、后台管理界面增强

### 4.1 更新控制器（保持现有结构）

```php
<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;

/**
 * LINE小程序配置
 */
class LineConfig extends Controller
{
    /**
     * LINE配置页面
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            // 获取当前配置（保持不变）
            $line_config = SettingModel::getItem('line_config');
            $line_messaging = SettingModel::getItem('line_messaging');
            $line_pay = SettingModel::getItem('line_pay');
            
            return $this->fetch('index', compact('line_config', 'line_messaging', 'line_pay'));
        }
        
        $data = $this->postData();
        $model = new SettingModel;
        
        // 根据提交的数据类型保存
        $key = key($data);
        if ($model->edit($key, $data[$key])) {
            return $this->renderSuccess('保存成功');
        }
        return $this->renderError($model->getError() ?: '保存失败');
    }
    
    /**
     * 测试消息发送（新增）
     */
    public function testMessage()
    {
        $messageType = $this->request->post('message_type');
        $lineUserId = $this->request->post('line_user_id');
        
        if (empty($messageType) || empty($lineUserId)) {
            return $this->renderError('参数错误');
        }
        
        // 构建测试数据
        $testData = $this->getTestData($messageType);
        
        // 发送测试消息
        $className = "app\\common\\service\\message\\line\\" . ucfirst($messageType);
        if (!class_exists($className)) {
            return $this->renderError('消息类型不存在');
        }
        
        // 临时设置 line_user_id 用于测试
        $testData['wxapp_id'] = $this->wxapp_id;
        $testData['line_user_id_override'] = $lineUserId;
        
        $service = new $className();
        $result = $service->send($testData);
        
        if ($result) {
            return $this->renderSuccess('测试消息发送成功');
        }
        
        return $this->renderError('测试消息发送失败');
    }
    
    /**
     * 获取测试数据
     */
    private function getTestData($messageType)
    {
        $testDataMap = [
            'inwarehouse' => [
                'member_id' => 0,
                'shop_name' => '泰国仓库',
                'express_num' => 'TEST' . date('YmdHis'),
                'entering_warehouse_time' => date('Y-m-d H:i:s'),
                'weight' => 1.5,
                'remark' => '这是一条测试消息',
                'id' => 999
            ],
            'sendpack' => [
                'member_id' => 0,
                'order_sn' => 'ORD' . date('YmdHis'),
                't_order_sn' => 'INT' . date('YmdHis'),
                'weight' => 2.5,
                't_name' => '标准快递',
                'send_time' => date('Y-m-d H:i:s'),
            ],
            'payment' => [
                'member_id' => 0,
                'order_sn' => 'ORD' . date('YmdHis'),
                'total_free' => 150.00,
                'pay_time' => date('Y-m-d H:i:s'),
                'remark' => '支付成功，感谢您的使用',
            ],
            'dabaosuccess' => [
                'member_id' => 0,
                'order_sn' => 'ORD' . date('YmdHis'),
                'pack_count' => 3,
                'weight' => 5.2,
                'volume' => 12000,
            ],
        ];
        
        return $testDataMap[$messageType] ?? [];
    }
    
    /**
     * 预览 Flex Message 模板（新增）
     */
    public function previewTemplate()
    {
        $messageType = $this->request->get('type');
        
        $config = SettingModel::getItem('line_messaging');
        $template = $config['templates'][$messageType] ?? null;
        
        if (!$template) {
            return $this->renderError('模板不存在');
        }
        
        return $this->renderSuccess('', [
            'template' => $template,
            'flex_simulator_url' => 'https://developers.line.biz/flex-simulator/'
        ]);
    }
}
```

### 4.2 更新视图文件（增强消息通知标签页）

```php
<!-- source/application/store/view/setting/line_config/index.php -->

<!-- 消息通知标签页 -->
<div class="am-tab-panel am-fade" id="tab2">
    <form action="<?= url('setting.line_config/index') ?>" class="am-form tpl-form-line-form" method="post">
        <fieldset>
            <div class="widget-head am-cf">
                <div class="widget-title am-fl">消息通知配置</div>
            </div>
            
            <!-- Channel 配置 -->
            <div class="widget-body am-fr">
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">启用消息通知</label>
                    <div class="am-u-sm-9">
                        <label class="am-radio-inline">
                            <input type="radio" name="line_messaging[is_enable]" value="1" 
                                <?= isset($line_messaging['is_enable']) && $line_messaging['is_enable'] == '1' ? 'checked' : '' ?>> 启用
                        </label>
                        <label class="am-radio-inline">
                            <input type="radio" name="line_messaging[is_enable]" value="0" 
                                <?= !isset($line_messaging['is_enable']) || $line_messaging['is_enable'] == '0' ? 'checked' : '' ?>> 禁用
                        </label>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">Channel ID</label>
                    <div class="am-u-sm-9">
                        <input type="text" class="tpl-form-input" 
                            name="line_messaging[channel_id]" 
                            value="<?= $line_messaging['channel_id'] ?? '' ?>" required>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">Channel Secret</label>
                    <div class="am-u-sm-9">
                        <input type="text" class="tpl-form-input" 
                            name="line_messaging[channel_secret]" 
                            value="<?= $line_messaging['channel_secret'] ?? '' ?>" required>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label form-require">Access Token</label>
                    <div class="am-u-sm-9">
                        <input type="text" class="tpl-form-input" 
                            name="line_messaging[access_token]" 
                            value="<?= $line_messaging['access_token'] ?? '' ?>" required>
                        <small>Channel Access Token (长期有效)</small>
                    </div>
                </div>
                
                <div class="am-form-group">
                    <label class="am-u-sm-3 am-form-label">LIFF URL</label>
                    <div class="am-u-sm-9">
                        <input type="text" class="tpl-form-input" 
                            name="line_messaging[liff_url]" 
                            value="<?= $line_messaging['liff_url'] ?? '' ?>">
                        <small>用于消息中的跳转链接，例如：https://liff.line.me/1234567890-abcdefgh</small>
                    </div>
                </div>
            </div>
            
            <!-- 消息模板配置 -->
            <div class="widget-body am-fr" style="margin-top: 20px;">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">消息模板配置</div>
                </div>
                
                <?php 
                $templates = [
                    'inwarehouse' => ['name' => '📦 包裹入库通知', 'color' => '#1DB446'],
                    'sendpack' => ['name' => '🚚 发货通知', 'color' => '#0066CC'],
                    'payment' => ['name' => '✅ 支付成功通知', 'color' => '#FF6B00'],
                    'dabaosuccess' => ['name' => '📋 打包完成通知', 'color' => '#9933FF'],
                    'payorder' => ['name' => '💰 付款单生成通知', 'color' => '#FF3366'],
                    'toshop' => ['name' => '🏪 到仓通知', 'color' => '#00CC99'],
                    'outapply' => ['name' => '📤 出库申请通知', 'color' => '#FF9900'],
                ];
                
                foreach ($templates as $type => $info): 
                    $template = $line_messaging['templates'][$type] ?? [];
                ?>
                
                <div class="am-panel am-panel-default" style="margin-bottom: 15px; border-left: 3px solid <?= $info['color'] ?>;">
                    <div class="am-panel-hd" style="background-color: #f5f5f5;">
                        <h4 class="am-panel-title">
                            <?= $info['name'] ?>
                            <label class="am-checkbox-inline" style="float: right; margin-right: 10px;">
                                <input type="checkbox" 
                                    name="line_messaging[templates][<?= $type ?>][is_enable]" 
                                    value="1" 
                                    <?= isset($template['is_enable']) && $template['is_enable'] == '1' ? 'checked' : '' ?>>
                                启用
                            </label>
                        </h4>
                    </div>
                    <div class="am-panel-bd">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">替代文本</label>
                            <div class="am-u-sm-9">
                                <input type="text" class="tpl-form-input" 
                                    name="line_messaging[templates][<?= $type ?>][alt_text]" 
                                    value="<?= $template['alt_text'] ?? $info['name'] ?>">
                                <small>当用户无法查看 Flex Message 时显示的文本</small>
                            </div>
                        </div>
                        
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">优先级</label>
                            <div class="am-u-sm-9">
                                <select class="am-form-field tpl-form-input" 
                                    name="line_messaging[templates][<?= $type ?>][priority]">
                                    <option value="high" <?= isset($template['priority']) && $template['priority'] == 'high' ? 'selected' : '' ?>>高</option>
                                    <option value="normal" <?= !isset($template['priority']) || $template['priority'] == 'normal' ? 'selected' : '' ?>>普通</option>
                                    <option value="low" <?= isset($template['priority']) && $template['priority'] == 'low' ? 'selected' : '' ?>>低</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">发送延迟(秒)</label>
                            <div class="am-u-sm-9">
                                <input type="number" class="tpl-form-input" 
                                    name="line_messaging[templates][<?= $type ?>][send_delay]" 
                                    value="<?= $template['send_delay'] ?? 0 ?>" min="0">
                                <small>延迟发送时间，0表示立即发送</small>
                            </div>
                        </div>
                        
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">模板变量</label>
                            <div class="am-u-sm-9">
                                <code style="background: #f5f5f5; padding: 5px; display: block;">
                                    <?= implode(', ', $template['variables'] ?? []) ?>
                                </code>
                            </div>
                        </div>
                        
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label">操作</label>
                            <div class="am-u-sm-9">
                                <button type="button" class="am-btn am-btn-secondary am-btn-xs" 
                                    onclick="previewTemplate('<?= $type ?>')">
                                    <i class="am-icon-eye"></i> 预览模板
                                </button>
                                <button type="button" class="am-btn am-btn-primary am-btn-xs" 
                                    onclick="testMessage('<?= $type ?>')">
                                    <i class="am-icon-send"></i> 发送测试
                                </button>
                            </div>
                        </div>
                        
                        <!-- 隐藏字段：保存完整的 flex_template -->
                        <input type="hidden" 
                            name="line_messaging[templates][<?= $type ?>][flex_template]" 
                            value='<?= json_encode($template['flex_template'] ?? []) ?>'>
                        <input type="hidden" 
                            name="line_messaging[templates][<?= $type ?>][variables]" 
                            value='<?= json_encode($template['variables'] ?? []) ?>'>
                        <input type="hidden" 
                            name="line_messaging[templates][<?= $type ?>][name]" 
                            value="<?= $template['name'] ?? $info['name'] ?>">
                    </div>
                </div>
                
                <?php endforeach; ?>
            </div>
            
            <div class="am-form-group">
                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交保存</button>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<script>
// 预览模板
function previewTemplate(type) {
    layer.open({
        type: 2,
        title: '预览 Flex Message 模板',
        area: ['80%', '80%'],
        content: '<?= url('setting.line_config/previewTemplate') ?>?type=' + type
    });
}

// 发送测试消息
function testMessage(type) {
    layer.prompt({
        title: '请输入测试用户的 LINE User ID',
        formType: 0
    }, function(lineUserId, index) {
        layer.close(index);
        
        $.ajax({
            url: '<?= url('setting.line_config/testMessage') ?>',
            type: 'POST',
            data: {
                message_type: type,
                line_user_id: lineUserId
            },
            dataType: 'json',
            success: function(result) {
                if (result.code === 1) {
                    layer.msg(result.msg, {icon: 1});
                } else {
                    layer.msg(result.msg, {icon: 2});
                }
            }
        });
    });
}
</script>
```


## 五、使用示例

### 5.1 业务代码中发送消息

```php
<?php
use app\common\service\Message;

// 包裹入库时发送通知
Message::send('package.inwarehouse', [
    'wxapp_id' => 10001,
    'member_id' => 123,
    'shop_name' => '泰国仓库',
    'express_num' => 'SF1234567890',
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
    'remark' => '包裹已入库',
    'id' => 456
]);

// 发货时发送通知
Message::send('package.sendpack', [
    'wxapp_id' => 10001,
    'member_id' => 123,
    'order_sn' => 'ORD20260114001',
    't_order_sn' => 'INT20260114001',
    'weight' => 2.5,
    't_name' => '标准快递',
    'send_time' => date('Y-m-d H:i:s')
]);

// 支付成功时发送通知
Message::send('package.payment', [
    'wxapp_id' => 10001,
    'member_id' => 123,
    'order_sn' => 'ORD20260114001',
    'total_free' => 150.00,
    'pay_time' => date('Y-m-d H:i:s'),
    'remark' => '订单已支付，即将安排发货'
]);
```

### 5.2 获取配置

```php
<?php
use app\common\model\Setting as SettingModel;

// 获取整个消息配置
$config = SettingModel::getItem('line_messaging');

// 检查是否启用
if ($config['is_enable'] == '1') {
    // 获取入库通知模板配置
    $template = $config['templates']['inwarehouse'];
    
    if ($template['is_enable'] == '1') {
        // 发送消息
    }
}

// 获取 Access Token
$accessToken = $config['access_token'];

// 获取 LIFF URL
$liffUrl = $config['liff_url'];
```

## 六、配置迁移

### 6.1 从旧配置迁移

如果之前有简单的 `line_messaging` 配置，需要迁移到新结构：

```php
<?php
namespace app\common\service;

use app\common\model\Setting as SettingModel;

class LineConfigMigration
{
    /**
     * 迁移旧配置到新结构
     */
    public static function migrate($wxappId)
    {
        $oldConfig = SettingModel::getItem('line_messaging', $wxappId);
        
        // 如果已经是新结构，跳过
        if (isset($oldConfig['templates']) && is_array($oldConfig['templates'])) {
            return true;
        }
        
        // 构建新配置
        $newConfig = [
            'is_enable' => $oldConfig['is_enable'] ?? '0',
            'channel_id' => $oldConfig['channel_id'] ?? '',
            'channel_secret' => $oldConfig['channel_secret'] ?? '',
            'access_token' => $oldConfig['access_token'] ?? '',
            'api_base_url' => 'https://api.line.me/v2/bot',
            'timeout' => 30,
            'retry_times' => 3,
            'log_enabled' => '1',
            'liff_id' => '',
            'liff_url' => '',
            'templates' => self::getDefaultTemplates()
        ];
        
        // 保存新配置
        $model = new SettingModel();
        return $model->edit('line_messaging', $newConfig, $wxappId);
    }
    
    /**
     * 获取默认模板配置
     */
    private static function getDefaultTemplates()
    {
        return [
            'inwarehouse' => [
                'is_enable' => '0',
                'name' => '包裹入库通知',
                'alt_text' => '📦 包裹入库通知',
                'priority' => 'high',
                'send_delay' => 0,
                'flex_template' => self::getInwarehouseTemplate(),
                'variables' => ['shop_name', 'express_num', 'entering_warehouse_time', 'weight', 'remark', 'detail_url']
            ],
            // ... 其他模板
        ];
    }
    
    /**
     * 获取入库通知模板
     */
    private static function getInwarehouseTemplate()
    {
        return [
            'type' => 'bubble',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '📦 包裹入库通知',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#1DB446'
                    ]
                ],
                'backgroundColor' => '#F0FFF0'
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    ['type' => 'text', 'text' => '仓库：{{shop_name}}', 'size' => 'sm', 'wrap' => true],
                    ['type' => 'text', 'text' => '快递单号：{{express_num}}', 'size' => 'sm', 'wrap' => true],
                    ['type' => 'text', 'text' => '入库时间：{{entering_warehouse_time}}', 'size' => 'sm', 'wrap' => true],
                    ['type' => 'text', 'text' => '重量：{{weight}}kg', 'size' => 'sm', 'wrap' => true],
                    ['type' => 'separator', 'margin' => 'md'],
                    ['type' => 'text', 'text' => '{{remark}}', 'size' => 'sm', 'color' => '#888888', 'margin' => 'md', 'wrap' => true]
                ],
                'spacing' => 'sm'
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => ['type' => 'uri', 'label' => '查看详情', 'uri' => '{{detail_url}}'],
                        'style' => 'primary',
                        'color' => '#1DB446'
                    ]
                ]
            ]
        ];
    }
}
```

## 七、实施步骤

### Phase 1: 配置结构更新 (1天)
- [x] 更新 `Setting.php` 中的 `line_messaging` 配置结构
- [x] 添加所有消息模板的默认配置
- [x] 编写配置迁移脚本

### Phase 2: 服务层实现 (2天)
- [x] 创建 LINE 消息服务基类 `Basics.php`
- [x] 实现各消息场景类（Inwarehouse, Sendpack, Payment 等）
- [x] 更新 `Message.php` 分发服务

### Phase 3: 后台界面增强 (2天)
- [x] 更新 `LineConfig` 控制器
- [x] 增强消息通知标签页视图
- [x] 添加测试消息发送功能
- [x] 添加模板预览功能

### Phase 4: 测试和优化 (1天)
- [ ] 测试各消息类型发送
- [ ] 测试配置保存和读取
- [ ] 性能优化
- [ ] 文档完善

## 八、优势总结

### 8.1 相比原方案的优势

| 对比项 | 原方案 | 增强方案 |
|-------|--------|---------|
| 配置位置 | `line_messaging` | 保持不变 ✅ |
| 配置结构 | 简单的 template 数组 | 完整的 templates 配置 |
| 模板类型 | 2个（enter, delivery） | 7个完整消息类型 |
| 模板格式 | 简单文本 | Flex Message JSON |
| 扩展性 | 需修改代码 | 配置驱动，易扩展 |
| 管理界面 | 基础表单 | 完整的模板管理 |
| 测试功能 | 无 | 支持测试发送 |

### 8.2 核心特点

1. **保持现有结构**: 基于现有 `line_messaging` 配置扩展，不破坏现有代码
2. **配置驱动**: 所有模板在配置中定义，无需修改代码
3. **完整功能**: 支持 7 种消息类型，覆盖所有业务场景
4. **易于管理**: 后台界面可视化配置，支持测试发送
5. **灵活扩展**: 新增消息类型只需添加配置项

### 8.3 与 LINE_OA_NOTIFICATION_SPEC.md 的对应关系

| SPEC 中的功能 | 实现方式 |
|-------------|---------|
| Channel 配置 | `line_messaging` 顶层字段 |
| LIFF 配置 | `liff_id`, `liff_url` 字段 |
| 消息模板 | `templates` 数组 |
| Flex Message | `flex_template` 字段 |
| 变量替换 | `renderTemplate()` 方法 |
| 消息发送 | `sendLineFlexMsg()` 方法 |
| 场景路由 | `Message::send()` 分发 |

## 九、注意事项

1. **配置大小**: Flex Message JSON 较大，建议使用 TEXT 类型存储
2. **缓存策略**: 配置变更后需清除缓存
3. **向后兼容**: 保持与现有代码的兼容性
4. **测试环境**: 先在测试环境验证所有消息类型
5. **日志记录**: 记录所有消息发送日志，便于排查问题

---

**文档版本**: 1.0  
**创建日期**: 2026-01-14  
**基于**: 现有 `/store/setting.line_config/index` 配置  
**适用项目**: LINE Mini App 集运系统



## 十、深层链接（Deep Link）支持

### 10.1 LINE Mini App 深层链接原理

LINE 消息中的按钮可以直接跳转到 LIFF 应用的特定页面，实现深层链接功能。

```
用户点击消息按钮
    ↓
打开 LIFF URL + 页面路径 + 参数
    ↓
LIFF 应用加载并导航到指定页面
    ↓
显示包裹详情/订单详情等
```

### 10.2 深层链接 URL 构建

#### 方案中已实现的 `buildLiffUrl()` 方法

```php
<?php
/**
 * 构建 LIFF 深层链接
 * @param string $path 页面路径
 * @param array $params 查询参数
 * @param int $wxappId 小程序ID
 * @return string
 */
protected function buildLiffUrl($path, $params = [], $wxappId = null)
{
    $config = SettingModel::getItem('line_messaging', $wxappId);
    $liffUrl = $config['liff_url'] ?? '';
    
    // 构建完整的深层链接
    // 格式: https://liff.line.me/{liff_id}/package/detail?id=123&rtype=10
    $queryString = http_build_query($params);
    return $liffUrl . $path . ($queryString ? '?' . $queryString : '');
}
```

#### 实际使用示例

```php
<?php
// 包裹入库通知 - 跳转到包裹详情页
$detailUrl = $this->buildLiffUrl(
    '/package/detail',
    ['id' => 456, 'rtype' => 10],
    $wxappId
);
// 结果: https://liff.line.me/1234567890-abcdefgh/package/detail?id=456&rtype=10

// 发货通知 - 跳转到物流跟踪页
$trackingUrl = $this->buildLiffUrl(
    '/tracking',
    ['order_sn' => 'ORD20260114001'],
    $wxappId
);
// 结果: https://liff.line.me/1234567890-abcdefgh/tracking?order_sn=ORD20260114001

// 打包完成 - 跳转到支付页
$payUrl = $this->buildLiffUrl(
    '/payment',
    ['order_id' => 789],
    $wxappId
);
// 结果: https://liff.line.me/1234567890-abcdefgh/payment?order_id=789
```

### 10.3 Flex Message 中的深层链接配置

#### 包裹入库通知示例（完整配置）

```php
'inwarehouse' => [
    'is_enable' => '1',
    'name' => '包裹入库通知',
    'alt_text' => '📦 包裹入库通知',
    'flex_template' => [
        'type' => 'bubble',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => '📦 包裹入库通知',
                    'weight' => 'bold',
                    'size' => 'lg',
                    'color' => '#1DB446'
                ]
            ],
            'backgroundColor' => '#F0FFF0'
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                ['type' => 'text', 'text' => '仓库：{{shop_name}}', 'size' => 'sm'],
                ['type' => 'text', 'text' => '快递单号：{{express_num}}', 'size' => 'sm'],
                ['type' => 'text', 'text' => '入库时间：{{entering_warehouse_time}}', 'size' => 'sm'],
                ['type' => 'text', 'text' => '重量：{{weight}}kg', 'size' => 'sm']
            ]
        ],
        'footer' => [
            'type' => 'box',
            'layout' => 'vertical',
            'contents' => [
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'uri',
                        'label' => '查看详情',
                        'uri' => '{{detail_url}}'  // 深层链接变量
                    ],
                    'style' => 'primary',
                    'color' => '#1DB446'
                ]
            ]
        ]
    ]
]
```

### 10.4 前端 LIFF 应用路由处理

#### React Router 配置（前端）

```javascript
// src/App.jsx
import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, useNavigate, useLocation } from 'react-router-dom';
import liff from '@line/liff';

function App() {
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    // 初始化 LIFF
    liff.init({ liffId: 'YOUR_LIFF_ID' }).then(() => {
      if (!liff.isLoggedIn()) {
        liff.login();
      } else {
        // LIFF 已登录，处理深层链接
        handleDeepLink();
      }
    });
  }, []);

  const handleDeepLink = () => {
    // 获取当前路径和参数
    const path = location.pathname;
    const params = new URLSearchParams(location.search);
    
    console.log('Deep Link Path:', path);
    console.log('Deep Link Params:', Object.fromEntries(params));
    
    // React Router 会自动处理路由
    // 例如: /package/detail?id=456 会自动导航到包裹详情页
  };

  return (
    <Routes>
      <Route path="/" element={<Home />} />
      <Route path="/package/detail" element={<PackageDetail />} />
      <Route path="/tracking" element={<Tracking />} />
      <Route path="/payment" element={<Payment />} />
      <Route path="/order/detail" element={<OrderDetail />} />
    </Routes>
  );
}
```

#### 包裹详情页接收参数

```javascript
// src/pages/Package/Detail.jsx
import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { getPackageDetail } from '@/api/package';

function PackageDetail() {
  const [searchParams] = useSearchParams();
  const [packageInfo, setPackageInfo] = useState(null);
  
  useEffect(() => {
    // 从 URL 参数获取包裹 ID
    const packageId = searchParams.get('id');
    const rtype = searchParams.get('rtype');
    
    console.log('从深层链接接收到的参数:', { packageId, rtype });
    
    if (packageId) {
      // 加载包裹详情
      loadPackageDetail(packageId);
    }
  }, [searchParams]);
  
  const loadPackageDetail = async (id) => {
    try {
      const res = await getPackageDetail({ id });
      setPackageInfo(res.data);
    } catch (error) {
      console.error('加载包裹详情失败:', error);
    }
  };
  
  return (
    <div className="package-detail">
      {packageInfo ? (
        <>
          <h2>包裹详情</h2>
          <p>快递单号: {packageInfo.express_num}</p>
          <p>重量: {packageInfo.weight}kg</p>
          {/* 其他详情 */}
        </>
      ) : (
        <div>加载中...</div>
      )}
    </div>
  );
}
```

### 10.5 完整的深层链接流程示例

#### 场景：用户收到包裹入库通知并点击查看详情

```
1. 后端发送消息
   ↓
   PHP: Message::send('package.inwarehouse', [
     'id' => 456,
     'express_num' => 'SF1234567890',
     ...
   ])
   
2. 构建深层链接
   ↓
   PHP: $detailUrl = buildLiffUrl('/package/detail', ['id' => 456])
   结果: https://liff.line.me/1234567890-abcdefgh/package/detail?id=456
   
3. 发送 Flex Message
   ↓
   LINE API: 发送包含深层链接按钮的消息
   
4. 用户点击按钮
   ↓
   LINE App: 打开 LIFF URL
   
5. LIFF 应用加载
   ↓
   React: 初始化 LIFF，检查登录状态
   
6. 路由导航
   ↓
   React Router: 导航到 /package/detail?id=456
   
7. 页面加载数据
   ↓
   PackageDetail 组件: 从 URL 获取 id=456，调用 API 加载详情
   
8. 显示包裹详情
   ↓
   用户看到完整的包裹信息
```

### 10.6 各消息类型的深层链接配置

```php
<?php
// 入库通知 - 跳转到包裹详情
class Inwarehouse extends Basics
{
    private function onSendLineMsg()
    {
        $detailUrl = $this->buildLiffUrl(
            '/package/detail',
            [
                'id' => $orderInfo['id'],
                'rtype' => 10,
                'from' => 'notification'  // 标记来源
            ],
            $wxappId
        );
        
        $data = [
            // ... 其他数据
            'detail_url' => $detailUrl
        ];
    }
}

// 发货通知 - 跳转到物流跟踪
class Sendpack extends Basics
{
    private function onSendLineMsg()
    {
        $trackingUrl = $this->buildLiffUrl(
            '/tracking',
            [
                'order_sn' => $orderInfo['order_sn'],
                'from' => 'notification'
            ],
            $wxappId
        );
        
        $data = [
            // ... 其他数据
            'tracking_url' => $trackingUrl
        ];
    }
}

// 打包完成 - 跳转到支付页
class Dabaosuccess extends Basics
{
    private function onSendLineMsg()
    {
        $payUrl = $this->buildLiffUrl(
            '/payment',
            [
                'order_id' => $orderInfo['order_id'],
                'amount' => $orderInfo['amount'],
                'from' => 'notification'
            ],
            $wxappId
        );
        
        $data = [
            // ... 其他数据
            'pay_url' => $payUrl
        ];
    }
}

// 支付成功 - 跳转到订单详情
class Payment extends Basics
{
    private function onSendLineMsg()
    {
        $orderUrl = $this->buildLiffUrl(
            '/order/detail',
            [
                'order_sn' => $orderInfo['order_sn'],
                'from' => 'notification'
            ],
            $wxappId
        );
        
        $data = [
            // ... 其他数据
            'order_url' => $orderUrl
        ];
    }
}
```

### 10.7 深层链接最佳实践

#### 1. URL 参数设计

```php
// 推荐：使用清晰的参数名
$url = buildLiffUrl('/package/detail', [
    'id' => 456,              // 包裹ID
    'rtype' => 10,            // 类型标识
    'from' => 'notification', // 来源标记
    'timestamp' => time()     // 时间戳（可选，用于缓存控制）
]);

// 避免：使用模糊的参数名
$url = buildLiffUrl('/page', ['a' => 456, 'b' => 10]);
```

#### 2. 前端参数验证

```javascript
// src/pages/Package/Detail.jsx
useEffect(() => {
  const packageId = searchParams.get('id');
  
  // 验证参数
  if (!packageId || isNaN(packageId)) {
    console.error('无效的包裹ID');
    navigate('/'); // 返回首页
    return;
  }
  
  loadPackageDetail(packageId);
}, [searchParams]);
```

#### 3. 添加来源追踪

```javascript
// 记录用户从哪里进入
const from = searchParams.get('from');

if (from === 'notification') {
  // 从通知进入，可以显示特殊提示或统计
  console.log('用户从 LINE 通知进入');
  trackEvent('notification_click', { type: 'package_detail' });
}
```

#### 4. 处理 LIFF 关闭

```javascript
// 添加关闭按钮
import liff from '@line/liff';

function PackageDetail() {
  const handleClose = () => {
    if (liff.isInClient()) {
      liff.closeWindow(); // 关闭 LIFF 窗口
    } else {
      navigate('/'); // 浏览器中返回首页
    }
  };
  
  return (
    <div>
      <button onClick={handleClose}>关闭</button>
      {/* 包裹详情内容 */}
    </div>
  );
}
```

### 10.8 测试深层链接

#### 测试步骤

1. **配置 LIFF URL**
   ```
   后台设置 -> LINE 配置 -> 消息通知
   LIFF URL: https://liff.line.me/1234567890-abcdefgh
   ```

2. **发送测试消息**
   ```
   后台 -> 消息模板 -> 包裹入库通知 -> 发送测试
   输入测试用户的 LINE User ID
   ```

3. **点击消息按钮**
   ```
   在 LINE App 中打开消息
   点击"查看详情"按钮
   ```

4. **验证跳转**
   ```
   检查是否正确打开 LIFF 应用
   检查是否导航到正确页面
   检查参数是否正确传递
   检查数据是否正确加载
   ```

### 10.9 常见问题和解决方案

#### 问题 1: 点击按钮无反应

**原因**: LIFF URL 配置错误

**解决**:
```php
// 检查配置
$config = SettingModel::getItem('line_messaging');
var_dump($config['liff_url']); // 应该是完整的 LIFF URL

// 正确格式: https://liff.line.me/1234567890-abcdefgh
// 错误格式: 1234567890-abcdefgh (缺少前缀)
```

#### 问题 2: 打开了 LIFF 但没有导航到指定页面

**原因**: 前端路由配置问题

**解决**:
```javascript
// 确保路由配置正确
<Route path="/package/detail" element={<PackageDetail />} />

// 确保 BrowserRouter 的 basename 配置正确
<BrowserRouter basename="/">
  <Routes>...</Routes>
</BrowserRouter>
```

#### 问题 3: 参数丢失

**原因**: URL 编码问题

**解决**:
```php
// 使用 http_build_query 自动处理编码
$params = [
    'id' => 456,
    'name' => '测试包裹', // 中文会自动编码
];
$queryString = http_build_query($params);
```

### 10.10 总结

✅ **方案完全支持深层链接**，包括：

1. **后端构建深层链接**: `buildLiffUrl()` 方法
2. **消息中嵌入链接**: Flex Message 的 button action
3. **前端接收参数**: React Router 的 useSearchParams
4. **页面加载数据**: 根据参数调用 API

用户点击消息后，可以直接跳转到小程序的特定页面（包裹详情、订单详情、支付页等），实现完整的深层链接功能！

