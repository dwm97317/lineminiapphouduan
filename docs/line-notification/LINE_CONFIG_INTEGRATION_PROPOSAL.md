# LINE 配置整合方案

> 将 LINE OA 消息通知的所有配置（模板、开关、Channel 信息等）整合到 `store/setting.line_config` 表中
> 使用标签（tag）区分功能模块，实现统一配置管理

## 一、方案概述

### 1.1 设计目标

- **统一存储**: 所有 LINE 相关配置集中在 `line_config` 表
- **标签分类**: 使用 `tag` 字段区分功能模块（channel、message、liff、webhook 等）
- **灵活扩展**: 支持新增消息类型和配置项
- **向后兼容**: 保持现有 API 调用方式不变

### 1.2 核心优势

| 优势 | 说明 |
|-----|------|
| 集中管理 | 所有配置在一个表中，便于维护和备份 |
| 标签分类 | 通过 tag 快速定位配置类型 |
| 多租户支持 | 通过 wxapp_id 支持多商户 |
| 版本控制 | 可记录配置变更历史 |
| 性能优化 | 支持缓存整个配置树 |

## 二、数据库设计

### 2.1 表结构

```sql
CREATE TABLE IF NOT EXISTS `yoshop_line_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tag` varchar(50) NOT NULL DEFAULT '' COMMENT '配置标签(channel/message/liff/webhook)',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置键名',
  `value` text COMMENT '配置值(JSON格式)',
  `describe` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用(0=禁用 1=启用)',
  `sort` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '排序(数字越小越靠前)',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小程序ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tag` (`tag`),
  KEY `idx_key` (`key`),
  KEY `idx_wxapp_id` (`wxapp_id`),
  KEY `idx_tag_wxapp` (`tag`, `wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='LINE配置表';
```

### 2.2 标签分类体系

| Tag | 说明 | 示例 Key |
|-----|------|---------|
| `channel` | LINE OA Channel 配置 | channel_id, channel_secret, access_token |
| `liff` | LIFF 应用配置 | liff_id, liff_url, redirect_uri |
| `message.inwarehouse` | 入库通知消息配置 | template, is_enable, alt_text |
| `message.sendpack` | 发货通知消息配置 | template, is_enable, alt_text |
| `message.payment` | 支付通知消息配置 | template, is_enable, alt_text |
| `message.dabaosuccess` | 打包完成消息配置 | template, is_enable, alt_text |
| `message.payorder` | 付款单生成消息配置 | template, is_enable, alt_text |
| `message.toshop` | 到仓通知消息配置 | template, is_enable, alt_text |
| `message.outapply` | 出库申请消息配置 | template, is_enable, alt_text |
| `webhook` | Webhook 配置 | webhook_url, verify_token, events |
| `quota` | 消息配额配置 | monthly_limit, used_count, alert_threshold |


## 三、配置数据结构

### 3.1 Channel 配置 (tag: channel)

```json
[
  {
    "tag": "channel",
    "key": "basic",
    "value": {
      "channel_id": "1234567890",
      "channel_secret": "abcdef1234567890",
      "channel_access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
      "channel_name": "集运系统官方账号",
      "bot_basic_id": "@123abcde"
    },
    "describe": "LINE OA Channel 基础配置",
    "is_enable": 1
  },
  {
    "tag": "channel",
    "key": "api_settings",
    "value": {
      "api_base_url": "https://api.line.me/v2/bot",
      "timeout": 30,
      "retry_times": 3,
      "log_enabled": true
    },
    "describe": "API 调用设置",
    "is_enable": 1
  }
]
```

### 3.2 LIFF 配置 (tag: liff)

```json
[
  {
    "tag": "liff",
    "key": "main_app",
    "value": {
      "liff_id": "1234567890-abcdefgh",
      "liff_url": "https://liff.line.me/1234567890-abcdefgh",
      "endpoint_url": "https://yourdomain.com",
      "view_type": "full",
      "description": "集运系统主应用"
    },
    "describe": "LIFF 主应用配置",
    "is_enable": 1
  },
  {
    "tag": "liff",
    "key": "page_paths",
    "value": {
      "package_detail": "/package/detail",
      "order_detail": "/order/detail",
      "payment": "/payment",
      "tracking": "/tracking"
    },
    "describe": "LIFF 页面路径映射",
    "is_enable": 1
  }
]
```

### 3.3 消息模板配置 (tag: message.*)

#### 入库通知 (tag: message.inwarehouse)

```json
[
  {
    "tag": "message.inwarehouse",
    "key": "config",
    "value": {
      "is_enable": true,
      "alt_text": "📦 包裹入库通知",
      "priority": "high",
      "send_delay": 0
    },
    "describe": "入库通知基础配置",
    "is_enable": 1
  },
  {
    "tag": "message.inwarehouse",
    "key": "template",
    "value": {
      "type": "bubble",
      "header": {
        "type": "box",
        "layout": "vertical",
        "contents": [
          {
            "type": "text",
            "text": "📦 包裹入库通知",
            "weight": "bold",
            "size": "lg",
            "color": "#1DB446"
          }
        ],
        "backgroundColor": "#F0FFF0"
      },
      "body": {
        "type": "box",
        "layout": "vertical",
        "contents": [
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "仓库", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{shop_name}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "快递单号", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{express_num}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "入库时间", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{entering_warehouse_time}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "重量", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{weight}}kg", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {"type": "separator", "margin": "md"},
          {
            "type": "text",
            "text": "{{remark}}",
            "size": "sm",
            "color": "#888888",
            "margin": "md",
            "wrap": true
          }
        ],
        "spacing": "sm"
      },
      "footer": {
        "type": "box",
        "layout": "vertical",
        "contents": [
          {
            "type": "button",
            "action": {
              "type": "uri",
              "label": "查看详情",
              "uri": "{{detail_url}}"
            },
            "style": "primary",
            "color": "#1DB446"
          }
        ]
      }
    },
    "describe": "入库通知 Flex Message 模板",
    "is_enable": 1
  },
  {
    "tag": "message.inwarehouse",
    "key": "variables",
    "value": {
      "shop_name": {"type": "string", "required": true, "max_length": 50},
      "express_num": {"type": "string", "required": true, "max_length": 50},
      "entering_warehouse_time": {"type": "datetime", "required": true, "format": "Y-m-d H:i:s"},
      "weight": {"type": "number", "required": true, "unit": "kg"},
      "remark": {"type": "string", "required": false, "max_length": 200},
      "detail_url": {"type": "url", "required": true}
    },
    "describe": "入库通知模板变量定义",
    "is_enable": 1
  }
]
```

#### 发货通知 (tag: message.sendpack)

```json
[
  {
    "tag": "message.sendpack",
    "key": "config",
    "value": {
      "is_enable": true,
      "alt_text": "🚚 发货通知",
      "priority": "high",
      "send_delay": 0
    },
    "describe": "发货通知基础配置",
    "is_enable": 1
  },
  {
    "tag": "message.sendpack",
    "key": "template",
    "value": {
      "type": "bubble",
      "header": {
        "type": "box",
        "layout": "vertical",
        "contents": [
          {
            "type": "text",
            "text": "🚚 发货通知",
            "weight": "bold",
            "size": "lg",
            "color": "#0066CC"
          }
        ],
        "backgroundColor": "#E6F3FF"
      },
      "body": {
        "type": "box",
        "layout": "vertical",
        "contents": [
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "订单号", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{order_sn}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "国际单号", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{t_order_sn}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "重量", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{weight}}kg", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "线路", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{t_name}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          },
          {
            "type": "box",
            "layout": "horizontal",
            "contents": [
              {"type": "text", "text": "发货时间", "size": "sm", "color": "#555555", "flex": 2},
              {"type": "text", "text": "{{send_time}}", "size": "sm", "color": "#111111", "flex": 3, "align": "end"}
            ]
          }
        ],
        "spacing": "sm"
      },
      "footer": {
        "type": "box",
        "layout": "vertical",
        "contents": [
          {
            "type": "button",
            "action": {
              "type": "uri",
              "label": "查看物流",
              "uri": "{{tracking_url}}"
            },
            "style": "primary",
            "color": "#0066CC"
          }
        ]
      }
    },
    "describe": "发货通知 Flex Message 模板",
    "is_enable": 1
  }
]
```

### 3.4 Webhook 配置 (tag: webhook)

```json
[
  {
    "tag": "webhook",
    "key": "config",
    "value": {
      "webhook_url": "https://yourdomain.com/api/line/webhook",
      "verify_token": "your_verify_token_here",
      "is_enable": true
    },
    "describe": "Webhook 基础配置",
    "is_enable": 1
  },
  {
    "tag": "webhook",
    "key": "events",
    "value": {
      "message": true,
      "follow": true,
      "unfollow": true,
      "join": false,
      "leave": false,
      "postback": true,
      "beacon": false
    },
    "describe": "Webhook 事件订阅配置",
    "is_enable": 1
  }
]
```

### 3.5 配额管理配置 (tag: quota)

```json
[
  {
    "tag": "quota",
    "key": "limits",
    "value": {
      "monthly_limit": 500,
      "daily_limit": 50,
      "alert_threshold": 80,
      "alert_email": "admin@example.com"
    },
    "describe": "消息配额限制",
    "is_enable": 1
  },
  {
    "tag": "quota",
    "key": "usage",
    "value": {
      "current_month": "2026-01",
      "used_count": 125,
      "last_reset_time": "2026-01-01 00:00:00",
      "last_check_time": "2026-01-14 10:30:00"
    },
    "describe": "配额使用情况",
    "is_enable": 1
  }
]
```


## 四、PHP 模型实现

### 4.1 LineConfig 模型

```php
<?php
namespace app\common\model;

use think\Cache;

/**
 * LINE 配置模型
 */
class LineConfig extends BaseModel
{
    protected $name = 'line_config';
    protected $pk = 'id';
    
    protected $updateTime = 'update_time';
    protected $createTime = 'create_time';
    
    /**
     * 获取指定标签的所有配置
     * @param string $tag 标签名
     * @param int $wxappId 小程序ID
     * @param bool $useCache 是否使用缓存
     * @return array
     */
    public static function getByTag($tag, $wxappId = null, $useCache = true)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        $cacheKey = "line_config_{$tag}_{$wxappId}";
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $list = self::where('tag', $tag)
            ->where('wxapp_id', $wxappId)
            ->where('is_enable', 1)
            ->order('sort', 'asc')
            ->select();
        
        $result = [];
        foreach ($list as $item) {
            $result[$item['key']] = json_decode($item['value'], true);
        }
        
        if ($useCache) {
            Cache::set($cacheKey, $result, 3600);
        }
        
        return $result;
    }
    
    /**
     * 获取单个配置项
     * @param string $tag 标签名
     * @param string $key 配置键
     * @param int $wxappId 小程序ID
     * @return mixed
     */
    public static function getItem($tag, $key, $wxappId = null)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        
        $item = self::where('tag', $tag)
            ->where('key', $key)
            ->where('wxapp_id', $wxappId)
            ->where('is_enable', 1)
            ->find();
        
        return $item ? json_decode($item['value'], true) : null;
    }
    
    /**
     * 设置配置项
     * @param string $tag 标签名
     * @param string $key 配置键
     * @param mixed $value 配置值
     * @param string $describe 说明
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function setItem($tag, $key, $value, $describe = '', $wxappId = null)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        
        $data = [
            'tag' => $tag,
            'key' => $key,
            'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
            'describe' => $describe,
            'wxapp_id' => $wxappId,
        ];
        
        $exists = self::where('tag', $tag)
            ->where('key', $key)
            ->where('wxapp_id', $wxappId)
            ->find();
        
        if ($exists) {
            $result = $exists->save($data);
        } else {
            $result = self::create($data);
        }
        
        // 清除缓存
        Cache::rm("line_config_{$tag}_{$wxappId}");
        
        return $result !== false;
    }
    
    /**
     * 批量设置配置
     * @param string $tag 标签名
     * @param array $items 配置数组 ['key' => ['value' => ..., 'describe' => ...]]
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function setBatch($tag, $items, $wxappId = null)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        
        self::startTrans();
        try {
            foreach ($items as $key => $item) {
                $value = $item['value'] ?? $item;
                $describe = $item['describe'] ?? '';
                self::setItem($tag, $key, $value, $describe, $wxappId);
            }
            self::commit();
            return true;
        } catch (\Exception $e) {
            self::rollback();
            return false;
        }
    }
    
    /**
     * 获取所有消息模板配置
     * @param int $wxappId 小程序ID
     * @return array
     */
    public static function getAllMessageTemplates($wxappId = null)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        
        $messageTags = [
            'message.inwarehouse',
            'message.sendpack',
            'message.payment',
            'message.dabaosuccess',
            'message.payorder',
            'message.toshop',
            'message.outapply',
        ];
        
        $result = [];
        foreach ($messageTags as $tag) {
            $sceneName = str_replace('message.', '', $tag);
            $result[$sceneName] = self::getByTag($tag, $wxappId);
        }
        
        return $result;
    }
    
    /**
     * 检查消息类型是否启用
     * @param string $messageType 消息类型 (inwarehouse, sendpack等)
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function isMessageEnabled($messageType, $wxappId = null)
    {
        $config = self::getItem("message.{$messageType}", 'config', $wxappId);
        return isset($config['is_enable']) && $config['is_enable'] === true;
    }
    
    /**
     * 获取 Channel Access Token
     * @param int $wxappId 小程序ID
     * @return string|null
     */
    public static function getAccessToken($wxappId = null)
    {
        $basic = self::getItem('channel', 'basic', $wxappId);
        return $basic['channel_access_token'] ?? null;
    }
    
    /**
     * 获取 LIFF URL
     * @param string $path 页面路径
     * @param int $wxappId 小程序ID
     * @return string
     */
    public static function getLiffUrl($path = '', $wxappId = null)
    {
        $mainApp = self::getItem('liff', 'main_app', $wxappId);
        $liffUrl = $mainApp['liff_url'] ?? '';
        
        return $path ? $liffUrl . $path : $liffUrl;
    }
    
    /**
     * 清除所有缓存
     * @param int $wxappId 小程序ID
     */
    public static function clearCache($wxappId = null)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        
        $tags = ['channel', 'liff', 'webhook', 'quota'];
        $messageTags = [
            'message.inwarehouse',
            'message.sendpack',
            'message.payment',
            'message.dabaosuccess',
            'message.payorder',
            'message.toshop',
            'message.outapply',
        ];
        
        foreach (array_merge($tags, $messageTags) as $tag) {
            Cache::rm("line_config_{$tag}_{$wxappId}");
        }
    }
}
```

### 4.2 配置服务类

```php
<?php
namespace app\common\service;

use app\common\model\LineConfig;

/**
 * LINE 配置服务
 */
class LineConfigService
{
    /**
     * 初始化默认配置
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function initDefaultConfig($wxappId)
    {
        $defaultConfigs = [
            // Channel 配置
            [
                'tag' => 'channel',
                'key' => 'basic',
                'value' => [
                    'channel_id' => '',
                    'channel_secret' => '',
                    'channel_access_token' => '',
                    'channel_name' => '',
                    'bot_basic_id' => ''
                ],
                'describe' => 'LINE OA Channel 基础配置',
            ],
            [
                'tag' => 'channel',
                'key' => 'api_settings',
                'value' => [
                    'api_base_url' => 'https://api.line.me/v2/bot',
                    'timeout' => 30,
                    'retry_times' => 3,
                    'log_enabled' => true
                ],
                'describe' => 'API 调用设置',
            ],
            
            // LIFF 配置
            [
                'tag' => 'liff',
                'key' => 'main_app',
                'value' => [
                    'liff_id' => '',
                    'liff_url' => '',
                    'endpoint_url' => '',
                    'view_type' => 'full',
                    'description' => '集运系统主应用'
                ],
                'describe' => 'LIFF 主应用配置',
            ],
            [
                'tag' => 'liff',
                'key' => 'page_paths',
                'value' => [
                    'package_detail' => '/package/detail',
                    'order_detail' => '/order/detail',
                    'payment' => '/payment',
                    'tracking' => '/tracking'
                ],
                'describe' => 'LIFF 页面路径映射',
            ],
            
            // Webhook 配置
            [
                'tag' => 'webhook',
                'key' => 'config',
                'value' => [
                    'webhook_url' => '',
                    'verify_token' => '',
                    'is_enable' => false
                ],
                'describe' => 'Webhook 基础配置',
            ],
            [
                'tag' => 'webhook',
                'key' => 'events',
                'value' => [
                    'message' => true,
                    'follow' => true,
                    'unfollow' => true,
                    'join' => false,
                    'leave' => false,
                    'postback' => true,
                    'beacon' => false
                ],
                'describe' => 'Webhook 事件订阅配置',
            ],
            
            // 配额配置
            [
                'tag' => 'quota',
                'key' => 'limits',
                'value' => [
                    'monthly_limit' => 500,
                    'daily_limit' => 50,
                    'alert_threshold' => 80,
                    'alert_email' => ''
                ],
                'describe' => '消息配额限制',
            ],
        ];
        
        // 消息模板配置
        $messageTypes = [
            'inwarehouse' => ['name' => '入库通知', 'icon' => '📦', 'color' => '#1DB446'],
            'sendpack' => ['name' => '发货通知', 'icon' => '🚚', 'color' => '#0066CC'],
            'payment' => ['name' => '支付成功', 'icon' => '✅', 'color' => '#FF6B00'],
            'dabaosuccess' => ['name' => '打包完成', 'icon' => '📋', 'color' => '#9933FF'],
            'payorder' => ['name' => '付款单生成', 'icon' => '💰', 'color' => '#FF3366'],
            'toshop' => ['name' => '到仓通知', 'icon' => '🏪', 'color' => '#00CC99'],
            'outapply' => ['name' => '出库申请', 'icon' => '📤', 'color' => '#FF9900'],
        ];
        
        foreach ($messageTypes as $type => $info) {
            $defaultConfigs[] = [
                'tag' => "message.{$type}",
                'key' => 'config',
                'value' => [
                    'is_enable' => false,
                    'alt_text' => "{$info['icon']} {$info['name']}",
                    'priority' => 'normal',
                    'send_delay' => 0
                ],
                'describe' => "{$info['name']}基础配置",
            ];
        }
        
        // 批量插入
        foreach ($defaultConfigs as $config) {
            LineConfig::setItem(
                $config['tag'],
                $config['key'],
                $config['value'],
                $config['describe'],
                $wxappId
            );
        }
        
        return true;
    }
    
    /**
     * 导出配置为 JSON
     * @param int $wxappId 小程序ID
     * @return string
     */
    public static function exportConfig($wxappId)
    {
        $allConfigs = LineConfig::where('wxapp_id', $wxappId)
            ->order('tag', 'asc')
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        
        foreach ($allConfigs as &$config) {
            $config['value'] = json_decode($config['value'], true);
        }
        
        return json_encode($allConfigs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    /**
     * 从 JSON 导入配置
     * @param string $json JSON 字符串
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function importConfig($json, $wxappId)
    {
        $configs = json_decode($json, true);
        if (!is_array($configs)) {
            return false;
        }
        
        foreach ($configs as $config) {
            LineConfig::setItem(
                $config['tag'],
                $config['key'],
                $config['value'],
                $config['describe'] ?? '',
                $wxappId
            );
        }
        
        return true;
    }
}
```


## 五、消息发送服务改造

### 5.1 LINE 消息基类改造

```php
<?php
namespace app\common\service\message\line;

use app\common\model\LineConfig;
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
        // 检查消息类型是否启用
        if (!LineConfig::isMessageEnabled($messageType, $wxappId)) {
            return false;
        }
        
        // 获取 Channel 配置
        $channelConfig = LineConfig::getByTag('channel', $wxappId);
        if (empty($channelConfig['basic']['channel_access_token'])) {
            return false;
        }
        
        // 获取消息配置
        $messageConfig = LineConfig::getByTag("message.{$messageType}", $wxappId);
        
        // 创建 LINE 消息实例
        $lineMessage = new LineMessage(
            $channelConfig['basic']['channel_id'],
            $channelConfig['basic']['channel_secret'],
            $channelConfig['basic']['channel_access_token']
        );
        
        // 生成 Flex Message 内容
        $template = $messageConfig['template'] ?? [];
        $flexContents = $this->renderTemplate($template, $data);
        
        // 获取 alt_text
        $altText = $messageConfig['config']['alt_text'] ?? '您有新的通知';
        
        // 发送消息
        $result = $lineMessage->sendFlexMessage($userId, $altText, $flexContents);
        
        // 记录发送日志
        $this->logMessageSend($wxappId, $userId, $messageType, $result);
        
        return $result;
    }
    
    /**
     * 渲染模板 (替换变量)
     * @param array $template 模板数组
     * @param array $data 数据
     * @return array
     */
    protected function renderTemplate($template, $data)
    {
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
    
    /**
     * 构建 LIFF 跳转链接
     * @param string $path 页面路径
     * @param array $params 查询参数
     * @param int $wxappId 小程序ID
     * @return string
     */
    protected function buildLiffUrl($path, $params = [], $wxappId = null)
    {
        $liffUrl = LineConfig::getLiffUrl('', $wxappId);
        $queryString = http_build_query($params);
        
        return $liffUrl . $path . ($queryString ? '?' . $queryString : '');
    }
}
```

### 5.2 入库通知场景类改造

```php
<?php
namespace app\common\service\message\line;

use app\common\model\LineConfig;

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

### 5.3 发货通知场景类改造

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


## 六、后台管理界面

### 6.1 控制器实现

```php
<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\common\model\LineConfig;
use app\common\service\LineConfigService;

/**
 * LINE 配置管理控制器
 */
class LineConfig extends Controller
{
    /**
     * 获取 Channel 配置
     */
    public function channel()
    {
        if ($this->request->isGet()) {
            $config = \app\common\model\LineConfig::getByTag('channel');
            return $this->renderSuccess('', ['config' => $config]);
        }
        
        $data = $this->request->post();
        if (\app\common\model\LineConfig::setBatch('channel', $data)) {
            return $this->renderSuccess('保存成功');
        }
        return $this->renderError('保存失败');
    }
    
    /**
     * 获取 LIFF 配置
     */
    public function liff()
    {
        if ($this->request->isGet()) {
            $config = \app\common\model\LineConfig::getByTag('liff');
            return $this->renderSuccess('', ['config' => $config]);
        }
        
        $data = $this->request->post();
        if (\app\common\model\LineConfig::setBatch('liff', $data)) {
            return $this->renderSuccess('保存成功');
        }
        return $this->renderError('保存失败');
    }
    
    /**
     * 获取所有消息模板配置
     */
    public function messageTemplates()
    {
        if ($this->request->isGet()) {
            $templates = \app\common\model\LineConfig::getAllMessageTemplates();
            return $this->renderSuccess('', ['templates' => $templates]);
        }
        
        // 批量更新消息模板配置
        $data = $this->request->post();
        
        foreach ($data as $messageType => $config) {
            $tag = "message.{$messageType}";
            \app\common\model\LineConfig::setBatch($tag, $config);
        }
        
        return $this->renderSuccess('保存成功');
    }
    
    /**
     * 更新单个消息模板配置
     */
    public function updateMessageConfig()
    {
        $messageType = $this->request->post('message_type');
        $config = $this->request->post('config');
        
        if (empty($messageType) || empty($config)) {
            return $this->renderError('参数错误');
        }
        
        $tag = "message.{$messageType}";
        if (\app\common\model\LineConfig::setBatch($tag, $config)) {
            return $this->renderSuccess('保存成功');
        }
        
        return $this->renderError('保存失败');
    }
    
    /**
     * 获取 Webhook 配置
     */
    public function webhook()
    {
        if ($this->request->isGet()) {
            $config = \app\common\model\LineConfig::getByTag('webhook');
            return $this->renderSuccess('', ['config' => $config]);
        }
        
        $data = $this->request->post();
        if (\app\common\model\LineConfig::setBatch('webhook', $data)) {
            return $this->renderSuccess('保存成功');
        }
        return $this->renderError('保存失败');
    }
    
    /**
     * 获取配额信息
     */
    public function quota()
    {
        $config = \app\common\model\LineConfig::getByTag('quota');
        return $this->renderSuccess('', ['config' => $config]);
    }
    
    /**
     * 初始化默认配置
     */
    public function initDefault()
    {
        if (LineConfigService::initDefaultConfig($this->wxapp_id)) {
            return $this->renderSuccess('初始化成功');
        }
        return $this->renderError('初始化失败');
    }
    
    /**
     * 导出配置
     */
    public function export()
    {
        $json = LineConfigService::exportConfig($this->wxapp_id);
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="line_config_' . date('YmdHis') . '.json"');
        echo $json;
        exit;
    }
    
    /**
     * 导入配置
     */
    public function import()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            return $this->renderError('请上传配置文件');
        }
        
        $json = file_get_contents($file->getPathname());
        
        if (LineConfigService::importConfig($json, $this->wxapp_id)) {
            return $this->renderSuccess('导入成功');
        }
        
        return $this->renderError('导入失败');
    }
    
    /**
     * 清除缓存
     */
    public function clearCache()
    {
        \app\common\model\LineConfig::clearCache($this->wxapp_id);
        return $this->renderSuccess('缓存已清除');
    }
    
    /**
     * 测试消息发送
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
        
        $service = new $className();
        $result = $service->send(array_merge($testData, [
            'wxapp_id' => $this->wxapp_id,
            'member_id' => 0, // 测试时直接使用 line_user_id
        ]));
        
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
                'shop_name' => '泰国仓库',
                'express_num' => 'TEST123456789',
                'entering_warehouse_time' => date('Y-m-d H:i:s'),
                'weight' => 1.5,
                'remark' => '这是一条测试消息',
            ],
            'sendpack' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                't_order_sn' => 'INT' . date('YmdHis'),
                'weight' => 2.5,
                't_name' => '标准快递',
                'send_time' => date('Y-m-d H:i:s'),
            ],
            'payment' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                'total_free' => 150.00,
                'pay_time' => date('Y-m-d H:i:s'),
                'remark' => '支付成功，感谢您的使用',
            ],
            'dabaosuccess' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                'pack_count' => 3,
                'weight' => 5.2,
                'volume' => 12000,
            ],
        ];
        
        return $testDataMap[$messageType] ?? [];
    }
}
```

### 6.2 前端页面示例 (Vue)

```vue
<template>
  <div class="line-config-container">
    <!-- 标签页导航 -->
    <el-tabs v-model="activeTab" @tab-click="handleTabClick">
      <el-tab-pane label="Channel 配置" name="channel">
        <channel-config :config="channelConfig" @save="saveChannelConfig" />
      </el-tab-pane>
      
      <el-tab-pane label="LIFF 配置" name="liff">
        <liff-config :config="liffConfig" @save="saveLiffConfig" />
      </el-tab-pane>
      
      <el-tab-pane label="消息模板" name="message">
        <message-templates :templates="messageTemplates" @save="saveMessageTemplates" />
      </el-tab-pane>
      
      <el-tab-pane label="Webhook 配置" name="webhook">
        <webhook-config :config="webhookConfig" @save="saveWebhookConfig" />
      </el-tab-pane>
      
      <el-tab-pane label="配额管理" name="quota">
        <quota-info :config="quotaConfig" />
      </el-tab-pane>
    </el-tabs>
    
    <!-- 工具栏 -->
    <div class="toolbar">
      <el-button @click="initDefault">初始化默认配置</el-button>
      <el-button @click="exportConfig">导出配置</el-button>
      <el-button @click="importConfig">导入配置</el-button>
      <el-button @click="clearCache">清除缓存</el-button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LineConfig',
  data() {
    return {
      activeTab: 'channel',
      channelConfig: {},
      liffConfig: {},
      messageTemplates: {},
      webhookConfig: {},
      quotaConfig: {}
    }
  },
  created() {
    this.loadConfig()
  },
  methods: {
    async loadConfig() {
      // 根据当前标签加载对应配置
      const api = `/store/setting/lineConfig/${this.activeTab}`
      const res = await this.$http.get(api)
      if (res.status === 200) {
        this[`${this.activeTab}Config`] = res.data.config
      }
    },
    
    handleTabClick(tab) {
      this.loadConfig()
    },
    
    async saveChannelConfig(config) {
      const res = await this.$http.post('/store/setting/lineConfig/channel', config)
      if (res.status === 200) {
        this.$message.success('保存成功')
      }
    },
    
    async saveLiffConfig(config) {
      const res = await this.$http.post('/store/setting/lineConfig/liff', config)
      if (res.status === 200) {
        this.$message.success('保存成功')
      }
    },
    
    async saveMessageTemplates(templates) {
      const res = await this.$http.post('/store/setting/lineConfig/messageTemplates', templates)
      if (res.status === 200) {
        this.$message.success('保存成功')
      }
    },
    
    async saveWebhookConfig(config) {
      const res = await this.$http.post('/store/setting/lineConfig/webhook', config)
      if (res.status === 200) {
        this.$message.success('保存成功')
      }
    },
    
    async initDefault() {
      const res = await this.$http.post('/store/setting/lineConfig/initDefault')
      if (res.status === 200) {
        this.$message.success('初始化成功')
        this.loadConfig()
      }
    },
    
    exportConfig() {
      window.location.href = '/store/setting/lineConfig/export'
    },
    
    async importConfig() {
      // 文件上传逻辑
    },
    
    async clearCache() {
      const res = await this.$http.post('/store/setting/lineConfig/clearCache')
      if (res.status === 200) {
        this.$message.success('缓存已清除')
      }
    }
  }
}
</script>
```

### 6.3 消息模板配置组件

```vue
<template>
  <div class="message-templates">
    <el-card v-for="(template, type) in templates" :key="type" class="template-card">
      <div slot="header" class="card-header">
        <span>{{ getTemplateName(type) }}</span>
        <el-switch v-model="template.config.is_enable" @change="handleEnableChange(type)" />
      </div>
      
      <el-form :model="template.config" label-width="120px">
        <el-form-item label="替代文本">
          <el-input v-model="template.config.alt_text" />
        </el-form-item>
        
        <el-form-item label="优先级">
          <el-select v-model="template.config.priority">
            <el-option label="高" value="high" />
            <el-option label="普通" value="normal" />
            <el-option label="低" value="low" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="发送延迟(秒)">
          <el-input-number v-model="template.config.send_delay" :min="0" />
        </el-form-item>
        
        <el-form-item label="模板预览">
          <el-button @click="previewTemplate(type)">预览</el-button>
          <el-button @click="editTemplate(type)">编辑模板</el-button>
          <el-button @click="testSend(type)">发送测试</el-button>
        </el-form-item>
      </el-form>
    </el-card>
    
    <div class="save-btn">
      <el-button type="primary" @click="saveAll">保存所有配置</el-button>
    </div>
    
    <!-- 模板编辑对话框 -->
    <el-dialog :visible.sync="editDialogVisible" title="编辑模板" width="80%">
      <json-editor v-model="currentTemplate" />
      <span slot="footer">
        <el-button @click="editDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="saveTemplate">保存</el-button>
      </span>
    </el-dialog>
    
    <!-- 测试发送对话框 -->
    <el-dialog :visible.sync="testDialogVisible" title="发送测试消息" width="50%">
      <el-form :model="testForm" label-width="120px">
        <el-form-item label="LINE User ID">
          <el-input v-model="testForm.line_user_id" placeholder="输入测试用户的 LINE User ID" />
        </el-form-item>
      </el-form>
      <span slot="footer">
        <el-button @click="testDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="sendTest">发送</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
export default {
  name: 'MessageTemplates',
  props: {
    templates: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      editDialogVisible: false,
      testDialogVisible: false,
      currentType: '',
      currentTemplate: {},
      testForm: {
        line_user_id: ''
      },
      templateNames: {
        inwarehouse: '📦 入库通知',
        sendpack: '🚚 发货通知',
        payment: '✅ 支付成功',
        dabaosuccess: '📋 打包完成',
        payorder: '💰 付款单生成',
        toshop: '🏪 到仓通知',
        outapply: '📤 出库申请'
      }
    }
  },
  methods: {
    getTemplateName(type) {
      return this.templateNames[type] || type
    },
    
    handleEnableChange(type) {
      this.$message.success(`${this.getTemplateName(type)} 已${this.templates[type].config.is_enable ? '启用' : '禁用'}`)
    },
    
    previewTemplate(type) {
      // 在 LINE Flex Message Simulator 中预览
      const template = this.templates[type].template
      const url = 'https://developers.line.biz/flex-simulator/'
      window.open(url, '_blank')
    },
    
    editTemplate(type) {
      this.currentType = type
      this.currentTemplate = JSON.parse(JSON.stringify(this.templates[type].template))
      this.editDialogVisible = true
    },
    
    saveTemplate() {
      this.templates[this.currentType].template = this.currentTemplate
      this.editDialogVisible = false
      this.$message.success('模板已更新，请点击"保存所有配置"以保存更改')
    },
    
    testSend(type) {
      this.currentType = type
      this.testDialogVisible = true
    },
    
    async sendTest() {
      const res = await this.$http.post('/store/setting/lineConfig/testMessage', {
        message_type: this.currentType,
        line_user_id: this.testForm.line_user_id
      })
      
      if (res.status === 200) {
        this.$message.success('测试消息已发送')
        this.testDialogVisible = false
      }
    },
    
    saveAll() {
      this.$emit('save', this.templates)
    }
  }
}
</script>

<style scoped>
.message-templates {
  padding: 20px;
}

.template-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.save-btn {
  text-align: center;
  margin-top: 20px;
}
</style>
```


## 七、数据迁移方案

### 7.1 从旧配置迁移到新表

```php
<?php
namespace app\common\service;

use app\common\model\Setting as SettingModel;
use app\common\model\LineConfig;

/**
 * LINE 配置迁移服务
 */
class LineConfigMigration
{
    /**
     * 从 Setting 表迁移到 LineConfig 表
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function migrateFromSetting($wxappId)
    {
        // 获取旧的 lineMsg 配置
        $oldConfig = SettingModel::getItem('lineMsg', $wxappId);
        
        if (empty($oldConfig)) {
            return false;
        }
        
        // 迁移 Channel 配置
        if (isset($oldConfig['channel_id'])) {
            LineConfig::setItem('channel', 'basic', [
                'channel_id' => $oldConfig['channel_id'] ?? '',
                'channel_secret' => $oldConfig['channel_secret'] ?? '',
                'channel_access_token' => $oldConfig['channel_access_token'] ?? '',
                'channel_name' => '',
                'bot_basic_id' => ''
            ], 'LINE OA Channel 基础配置', $wxappId);
        }
        
        // 迁移 LIFF 配置
        if (isset($oldConfig['liff_id'])) {
            LineConfig::setItem('liff', 'main_app', [
                'liff_id' => $oldConfig['liff_id'] ?? '',
                'liff_url' => $oldConfig['liff_url'] ?? '',
                'endpoint_url' => '',
                'view_type' => 'full',
                'description' => '集运系统主应用'
            ], 'LIFF 主应用配置', $wxappId);
        }
        
        // 迁移消息模板配置
        $messageTypes = [
            'inwarehouse', 'sendpack', 'payment', 
            'dabaosuccess', 'payorder', 'toshop', 'outapply'
        ];
        
        foreach ($messageTypes as $type) {
            if (isset($oldConfig[$type])) {
                $tag = "message.{$type}";
                
                // 基础配置
                LineConfig::setItem($tag, 'config', [
                    'is_enable' => $oldConfig[$type]['is_enable'] == '1',
                    'alt_text' => $oldConfig[$type]['alt_text'] ?? '',
                    'priority' => 'normal',
                    'send_delay' => 0
                ], "{$type} 基础配置", $wxappId);
            }
        }
        
        return true;
    }
    
    /**
     * 检查是否需要迁移
     * @param int $wxappId 小程序ID
     * @return bool
     */
    public static function needMigration($wxappId)
    {
        // 检查新表是否有数据
        $hasNewConfig = LineConfig::where('wxapp_id', $wxappId)->count() > 0;
        
        // 检查旧表是否有配置
        $oldConfig = SettingModel::getItem('lineMsg', $wxappId);
        $hasOldConfig = !empty($oldConfig);
        
        return !$hasNewConfig && $hasOldConfig;
    }
}
```

### 7.2 迁移 SQL 脚本

```sql
-- 创建 LINE 配置表
CREATE TABLE IF NOT EXISTS `yoshop_line_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tag` varchar(50) NOT NULL DEFAULT '' COMMENT '配置标签',
  `key` varchar(100) NOT NULL DEFAULT '' COMMENT '配置键名',
  `value` text COMMENT '配置值(JSON格式)',
  `describe` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `is_enable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用',
  `sort` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '排序',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小程序ID',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_tag` (`tag`),
  KEY `idx_key` (`key`),
  KEY `idx_wxapp_id` (`wxapp_id`),
  KEY `idx_tag_wxapp` (`tag`, `wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='LINE配置表';

-- 添加 line_user_id 字段到用户表（如果不存在）
ALTER TABLE `yoshop_user` 
ADD COLUMN `line_user_id` VARCHAR(64) DEFAULT NULL COMMENT 'LINE用户ID' AFTER `open_id`;

ALTER TABLE `yoshop_user` 
ADD INDEX `idx_line_user_id` (`line_user_id`);
```

## 八、使用示例

### 8.1 获取配置

```php
<?php
use app\common\model\LineConfig;

// 获取 Channel Access Token
$accessToken = LineConfig::getAccessToken();

// 获取 LIFF URL
$liffUrl = LineConfig::getLiffUrl('/package/detail');

// 检查消息是否启用
$isEnabled = LineConfig::isMessageEnabled('inwarehouse');

// 获取整个标签的配置
$channelConfig = LineConfig::getByTag('channel');

// 获取单个配置项
$basicConfig = LineConfig::getItem('channel', 'basic');
```

### 8.2 设置配置

```php
<?php
use app\common\model\LineConfig;

// 设置单个配置项
LineConfig::setItem('channel', 'basic', [
    'channel_id' => '1234567890',
    'channel_secret' => 'abcdef1234567890',
    'channel_access_token' => 'your_token_here'
], 'Channel 基础配置');

// 批量设置配置
LineConfig::setBatch('liff', [
    'main_app' => [
        'value' => [
            'liff_id' => '1234567890-abcdefgh',
            'liff_url' => 'https://liff.line.me/1234567890-abcdefgh'
        ],
        'describe' => 'LIFF 主应用配置'
    ]
]);
```

### 8.3 发送消息

```php
<?php
use app\common\service\Message;

// 发送入库通知
Message::send('package.inwarehouse', [
    'wxapp_id' => 10001,
    'member_id' => 123,
    'shop_name' => '泰国仓库',
    'express_num' => 'SF1234567890',
    'entering_warehouse_time' => date('Y-m-d H:i:s'),
    'weight' => 1.5,
    'remark' => '包裹已入库'
]);

// 发送发货通知
Message::send('package.sendpack', [
    'wxapp_id' => 10001,
    'member_id' => 123,
    'order_sn' => 'ORD20260114001',
    't_order_sn' => 'INT20260114001',
    'weight' => 2.5,
    't_name' => '标准快递',
    'send_time' => date('Y-m-d H:i:s')
]);
```

## 九、优势对比

### 9.1 旧方案 vs 新方案

| 对比项 | 旧方案 (Setting 表) | 新方案 (LineConfig 表) |
|-------|-------------------|---------------------|
| 存储方式 | 单个 JSON 字段 | 多行记录，每行一个配置项 |
| 查询效率 | 需解析整个 JSON | 可按 tag/key 精确查询 |
| 更新灵活性 | 需更新整个 JSON | 可单独更新某个配置项 |
| 扩展性 | 需修改 JSON 结构 | 直接插入新记录 |
| 版本控制 | 困难 | 可记录每次变更 |
| 标签分类 | 无 | 支持 tag 分类 |
| 缓存粒度 | 整体缓存 | 可按 tag 分别缓存 |
| 配置导入导出 | 困难 | 简单 |

### 9.2 性能优化

```php
<?php
// 旧方案：每次都要解析整个 JSON
$config = SettingModel::getItem('lineMsg');
$isEnabled = $config['inwarehouse']['is_enable'] == '1';

// 新方案：直接查询需要的配置，支持缓存
$isEnabled = LineConfig::isMessageEnabled('inwarehouse'); // 有缓存
```

## 十、实施步骤

### Phase 1: 数据库准备 (1天)
- [ ] 创建 `yoshop_line_config` 表
- [ ] 添加 `line_user_id` 字段到用户表
- [ ] 创建索引

### Phase 2: 模型和服务层 (2天)
- [ ] 实现 `LineConfig` 模型
- [ ] 实现 `LineConfigService` 服务类
- [ ] 实现配置迁移服务
- [ ] 编写单元测试

### Phase 3: 消息服务改造 (2天)
- [ ] 改造 `LINE\Basics` 基类
- [ ] 改造各消息场景类
- [ ] 测试消息发送功能

### Phase 4: 后台管理界面 (3天)
- [ ] 实现后台控制器
- [ ] 实现前端配置页面
- [ ] 实现配置导入导出功能
- [ ] 实现测试消息发送功能

### Phase 5: 数据迁移和测试 (2天)
- [ ] 执行数据迁移
- [ ] 全面测试各项功能
- [ ] 性能测试
- [ ] 文档完善

## 十一、注意事项

### 11.1 兼容性处理

在迁移期间，可以同时支持新旧两种配置方式：

```php
<?php
namespace app\common\model;

class LineConfig extends BaseModel
{
    /**
     * 获取配置（兼容旧方案）
     */
    public static function getAccessTokenCompat($wxappId = null)
    {
        // 优先从新表获取
        $token = self::getAccessToken($wxappId);
        
        // 如果新表没有，从旧表获取
        if (empty($token)) {
            $oldConfig = Setting::getItem('lineMsg', $wxappId);
            $token = $oldConfig['channel_access_token'] ?? null;
        }
        
        return $token;
    }
}
```

### 11.2 缓存策略

```php
<?php
// 推荐的缓存时间
// - Channel 配置: 1小时（不常变）
// - 消息模板: 30分钟（可能调整）
// - 配额信息: 5分钟（需实时）

Cache::set("line_config_channel_{$wxappId}", $config, 3600);
Cache::set("line_config_message_{$type}_{$wxappId}", $config, 1800);
Cache::set("line_config_quota_{$wxappId}", $config, 300);
```

### 11.3 安全建议

1. **敏感信息加密**: Channel Secret 和 Access Token 应加密存储
2. **权限控制**: 后台配置页面需要管理员权限
3. **操作日志**: 记录所有配置变更操作
4. **备份机制**: 定期备份配置数据

```php
<?php
// 敏感信息加密示例
use think\facade\Env;

class LineConfig extends BaseModel
{
    // 加密字段
    protected $encrypted = ['channel_secret', 'channel_access_token'];
    
    public function setValueAttr($value, $data)
    {
        if (in_array($data['key'], $this->encrypted)) {
            return encrypt($value, Env::get('app.encrypt_key'));
        }
        return $value;
    }
    
    public function getValueAttr($value, $data)
    {
        if (in_array($data['key'], $this->encrypted)) {
            return decrypt($value, Env::get('app.encrypt_key'));
        }
        return $value;
    }
}
```

## 十二、总结

### 12.1 方案核心要点

1. **统一存储**: 所有 LINE 配置集中在 `line_config` 表
2. **标签分类**: 使用 `tag` 字段区分功能模块
3. **灵活扩展**: 支持动态添加新配置项
4. **性能优化**: 支持按标签缓存，减少数据库查询
5. **易于维护**: 配置导入导出，版本控制

### 12.2 预期收益

- **开发效率**: 新增消息类型只需插入配置记录
- **维护成本**: 配置管理更直观，减少出错
- **性能提升**: 精确查询和缓存策略提升响应速度
- **扩展性**: 支持未来更多 LINE 功能集成

### 12.3 后续优化方向

1. 配置版本管理（记录变更历史）
2. 配置审批流程（多人协作）
3. A/B 测试支持（不同用户不同模板）
4. 消息发送统计和分析
5. 智能配额管理和预警

---

**文档版本**: 1.0  
**创建日期**: 2026-01-14  
**作者**: AI Assistant  
**适用项目**: LINE Mini App 集运系统

