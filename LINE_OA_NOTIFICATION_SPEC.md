# LINE OA 通知功能实现说明

> 本文档供AI参考，用于实现LINE Official Account (OA) 的消息通知功能
> 基于现有微信公众号模板消息架构进行适配

## 一、现有微信公众号模板消息架构分析

### 1.1 整体架构

```
┌─────────────────────────────────────────────────────────────┐
│                      业务触发层                              │
│  (Package入库/打包完成/发货/支付等业务事件)                    │
└─────────────────────┬───────────────────────────────────────┘
                      │ Message::send('场景名', $data)
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                   消息分发服务 (Message.php)                  │
│  - 场景路由映射 ($sceneList)                                 │
│  - 动态实例化对应场景类                                       │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                   场景消息类 (Basics子类)                     │
│  - Inwarehouse.php (入库通知)                                │
│  - Sendpack.php (发货通知)                                   │
│  - Payment.php (支付通知)                                    │
│  - Dabaosuccess.php (打包完成)                               │
│  - Payorder.php (付款单生成)                                 │
│  - Toshop.php (到仓通知)                                     │
│  - Outapply.php (出库申请)                                   │
└─────────────────────┬───────────────────────────────────────┘
                      │ sendWxTplMsg() / sendWxTplMsgForH5()
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                   微信API封装层 (WxTplMsg.php)                │
│  - 获取access_token                                         │
│  - 构建请求参数                                              │
│  - 调用微信模板消息API                                        │
└─────────────────────────────────────────────────────────────┘
```

### 1.2 核心文件说明

| 文件路径 | 功能说明 |
|---------|---------|
| `common/service/Message.php` | 消息分发入口，场景路由映射 |
| `common/service/message/Basics.php` | 消息服务基类，封装发送方法 |
| `common/service/message/package/*.php` | 包裹相关消息场景实现 |
| `common/service/message/order/*.php` | 订单相关消息场景实现 |
| `common/library/wechat/WxTplMsg.php` | 微信模板消息API封装 |
| `common/library/wechat/WxBase.php` | 微信API基类，token管理 |
| `common/model/Setting.php` | 系统配置，含模板消息配置 |


### 1.3 消息场景映射表

```php
// Message.php 中的场景映射
private static $sceneList = [
    // 包裹相关
    'package.inwarehouse' => 'Inwarehouse',    // 包裹入库通知
    'package.dabaosuccess' => 'Dabaosuccess',  // 打包完成通知
    'package.outapply' => 'Outapply',          // 出库申请通知
    'package.toshop' => 'Toshop',              // 到仓通知
    'package.sendpack' => 'Sendpack',          // 发货通知
    'package.payorder' => 'Payorder',          // 付款单生成
    
    // 订单相关
    'order.payment' => 'Payment',              // 支付成功通知
    'order.enter' => 'Enter',                  // 入库通知(旧版)
    'order.packageit' => 'Packageit',          // 打包申请通知
    'order.paymessage' => 'Paymessage',        // 支付消息通知管理员
];
```

### 1.4 微信模板消息配置结构

```php
// Setting.php 中的 tplMsg 配置
'tplMsg' => [
    'key' => 'tplMsg',
    'describe' => '模板消息',
    'values' => [
        'is_oldtps' => 1,  // 是否使用旧版模板
        
        // 每个模板配置项
        'inwarehouse' => [
            'is_enable' => '0',           // 是否启用
            'template_id' => '',          // 微信模板ID
            'keywords' => [               // 模板变量映射
                'thing8',                 // 仓库名称
                'character_string2',      // 快递单号
                'time6',                  // 入库时间
                'thing7',                 // 重量
                'thing9'                  // 备注
            ],
        ],
        // ... 其他模板配置
    ],
],
```

### 1.5 微信模板消息发送流程

```php
// 1. 业务层调用
Message::send('package.inwarehouse', $packageData);

// 2. 场景类处理 (Inwarehouse.php)
public function send($param) {
    $this->param = $param;
    $this->onSendWxTplMsg();
}

// 3. 构建并发送消息
private function onSendWxTplMsg() {
    // 获取模板配置
    $template = SettingModel::getItem('tplMsg', $wxappId)['inwarehouse'];
    
    // 获取用户的公众号openid
    $touser = $this->getGzhOpenidByUserId($userId);
    
    // 调用基类方法发送
    return $this->sendWxTplMsg($wxappId, [
        'touser' => $touser,
        'template_id' => $template['template_id'],
        'url' => $pageUrl,
        'miniprogram' => ['appid' => '', 'pagepath' => $pagePath],
        'data' => [
            $template['keywords'][0] => ['value' => $shopName],
            $template['keywords'][1] => ['value' => $expressNum],
            // ...
        ]
    ]);
}

// 4. WxTplMsg.php 发送到微信API
public function sendWxTemplateMessage($param) {
    $accessToken = $this->getAccessTokenForWxOpen();
    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$accessToken}";
    
    $params = [
        'touser' => $param['touser'],
        'template_id' => $param['template_id'],
        'url' => $param['url'],
        'miniprogram' => $param['miniprogram'],
        'data' => $this->createData($param['data']),
    ];
    
    return $this->post($url, json_encode($params));
}
```


## 二、LINE OA Messaging API 概述

### 2.1 LINE Messaging API vs 微信模板消息对比

| 特性 | 微信公众号模板消息 | LINE Messaging API |
|-----|------------------|-------------------|
| 认证方式 | access_token (AppID+Secret) | Channel Access Token |
| 用户标识 | openid / gzh_openid | LINE User ID |
| 消息类型 | 模板消息 (固定格式) | Flex Message / Text / Template |
| 模板管理 | 微信后台申请模板ID | 代码中自定义Flex Message |
| API端点 | api.weixin.qq.com | api.line.me |
| 推送限制 | 需用户关注+授权 | 需用户添加好友 |

### 2.2 LINE Messaging API 核心概念

```
┌─────────────────────────────────────────────────────────────┐
│                    LINE OA Channel                          │
│  - Channel ID                                               │
│  - Channel Secret                                           │
│  - Channel Access Token (长期有效)                           │
└─────────────────────────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    消息类型                                  │
│  - Push Message: 主动推送给用户                              │
│  - Reply Message: 回复用户消息                               │
│  - Multicast: 批量推送                                      │
│  - Broadcast: 广播给所有好友                                 │
└─────────────────────────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│                    消息格式                                  │
│  - Text Message: 纯文本                                     │
│  - Flex Message: 自定义卡片布局 (推荐)                       │
│  - Template Message: 按钮/确认/轮播模板                      │
│  - Image/Video/Audio: 多媒体消息                            │
└─────────────────────────────────────────────────────────────┘
```

### 2.3 LINE Push Message API

```
POST https://api.line.me/v2/bot/message/push
Authorization: Bearer {channel_access_token}
Content-Type: application/json

{
    "to": "LINE_USER_ID",
    "messages": [
        {
            "type": "flex",
            "altText": "包裹入库通知",
            "contents": { /* Flex Message JSON */ }
        }
    ]
}
```


## 三、LINE OA 通知功能实现方案

### 3.1 新增文件结构

```
source/application/common/
├── library/
│   └── line/
│       ├── LineBase.php          # LINE API基类
│       ├── LineMessage.php       # LINE消息发送封装
│       └── FlexTemplate.php      # Flex Message模板生成器
├── service/
│   └── message/
│       └── line/                 # LINE消息场景类
│           ├── Basics.php        # LINE消息基类
│           ├── Inwarehouse.php   # 入库通知
│           ├── Sendpack.php      # 发货通知
│           ├── Payment.php       # 支付通知
│           └── ...
```

### 3.2 数据库配置扩展

```sql
-- 在 yoshop_setting 表中添加 LINE 配置
-- key = 'lineMsg'

INSERT INTO yoshop_setting (key, describe, values, wxapp_id) VALUES
('lineMsg', 'LINE消息通知', '{
    "channel_id": "",
    "channel_secret": "",
    "channel_access_token": "",
    "inwarehouse": {
        "is_enable": "0",
        "alt_text": "包裹入库通知"
    },
    "sendpack": {
        "is_enable": "0",
        "alt_text": "发货通知"
    },
    "payment": {
        "is_enable": "0",
        "alt_text": "支付成功通知"
    },
    "dabaosuccess": {
        "is_enable": "0",
        "alt_text": "打包完成通知"
    },
    "payorder": {
        "is_enable": "0",
        "alt_text": "付款单生成通知"
    },
    "toshop": {
        "is_enable": "0",
        "alt_text": "到仓通知"
    }
}', 10001);
```

### 3.3 用户表扩展

```sql
-- 在 yoshop_user 表中添加 LINE User ID 字段
ALTER TABLE yoshop_user ADD COLUMN line_user_id VARCHAR(64) DEFAULT NULL COMMENT 'LINE用户ID';
ALTER TABLE yoshop_user ADD INDEX idx_line_user_id (line_user_id);
```


### 3.4 核心类实现

#### 3.4.1 LineBase.php - LINE API基类

```php
<?php
namespace app\common\library\line;

use think\Cache;
use app\common\exception\BaseException;

class LineBase
{
    protected $channelId;
    protected $channelSecret;
    protected $channelAccessToken;
    protected $error;
    
    const API_BASE_URL = 'https://api.line.me/v2/bot';
    
    public function __construct($channelId, $channelSecret, $channelAccessToken)
    {
        $this->channelId = $channelId;
        $this->channelSecret = $channelSecret;
        $this->channelAccessToken = $channelAccessToken;
    }
    
    /**
     * 发送POST请求到LINE API
     */
    protected function post($endpoint, $data)
    {
        $url = self::API_BASE_URL . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken
        ];
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $this->doLogs([
            'describe' => 'LINE API请求',
            'url' => $url,
            'data' => $data,
            'result' => $result,
            'http_code' => $httpCode
        ]);
        
        return ['http_code' => $httpCode, 'body' => json_decode($result, true)];
    }
    
    protected function doLogs($values)
    {
        return log_write($values);
    }
    
    public function getError()
    {
        return $this->error;
    }
}
```

#### 3.4.2 LineMessage.php - 消息发送封装

```php
<?php
namespace app\common\library\line;

class LineMessage extends LineBase
{
    /**
     * 推送消息给单个用户
     * @param string $userId LINE User ID
     * @param array $messages 消息数组
     * @return bool
     */
    public function pushMessage($userId, $messages)
    {
        if (empty($userId) || empty($messages)) {
            $this->error = '参数不完整';
            return false;
        }
        
        $data = [
            'to' => $userId,
            'messages' => $messages
        ];
        
        $result = $this->post('/message/push', $data);
        
        if ($result['http_code'] !== 200) {
            $this->error = $result['body']['message'] ?? 'LINE API请求失败';
            return false;
        }
        
        return true;
    }
    
    /**
     * 批量推送消息
     * @param array $userIds LINE User ID数组 (最多500个)
     * @param array $messages 消息数组
     * @return bool
     */
    public function multicast($userIds, $messages)
    {
        if (empty($userIds) || empty($messages)) {
            $this->error = '参数不完整';
            return false;
        }
        
        $data = [
            'to' => array_slice($userIds, 0, 500),
            'messages' => $messages
        ];
        
        $result = $this->post('/message/multicast', $data);
        
        return $result['http_code'] === 200;
    }
    
    /**
     * 发送Flex Message
     * @param string $userId
     * @param string $altText 替代文本
     * @param array $contents Flex Message内容
     * @return bool
     */
    public function sendFlexMessage($userId, $altText, $contents)
    {
        $messages = [
            [
                'type' => 'flex',
                'altText' => $altText,
                'contents' => $contents
            ]
        ];
        
        return $this->pushMessage($userId, $messages);
    }
    
    /**
     * 发送文本消息
     * @param string $userId
     * @param string $text
     * @return bool
     */
    public function sendTextMessage($userId, $text)
    {
        $messages = [
            [
                'type' => 'text',
                'text' => $text
            ]
        ];
        
        return $this->pushMessage($userId, $messages);
    }
}
```


#### 3.4.3 FlexTemplate.php - Flex Message模板生成器

```php
<?php
namespace app\common\library\line;

/**
 * LINE Flex Message 模板生成器
 * 用于生成各种通知场景的卡片消息
 */
class FlexTemplate
{
    /**
     * 包裹入库通知模板
     */
    public static function inwarehouse($data)
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
                    self::createInfoRow('仓库', $data['shop_name'] ?? ''),
                    self::createInfoRow('快递单号', $data['express_num'] ?? ''),
                    self::createInfoRow('入库时间', $data['entering_warehouse_time'] ?? ''),
                    self::createInfoRow('重量', ($data['weight'] ?? 0) . 'kg'),
                    [
                        'type' => 'separator',
                        'margin' => 'md'
                    ],
                    [
                        'type' => 'text',
                        'text' => $data['remark'] ?? '包裹已入库，可提交打包',
                        'size' => 'sm',
                        'color' => '#888888',
                        'margin' => 'md',
                        'wrap' => true
                    ]
                ],
                'spacing' => 'sm'
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
                            'uri' => $data['detail_url'] ?? 'https://liff.line.me/YOUR_LIFF_ID'
                        ],
                        'style' => 'primary',
                        'color' => '#1DB446'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 发货通知模板
     */
    public static function sendpack($data)
    {
        return [
            'type' => 'bubble',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '🚚 发货通知',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#0066CC'
                    ]
                ],
                'backgroundColor' => '#E6F3FF'
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    self::createInfoRow('订单号', $data['order_sn'] ?? ''),
                    self::createInfoRow('国际单号', $data['t_order_sn'] ?? ''),
                    self::createInfoRow('重量', ($data['weight'] ?? 0) . 'kg'),
                    self::createInfoRow('线路', $data['t_name'] ?? '默认'),
                    self::createInfoRow('发货时间', $data['send_time'] ?? date('Y-m-d H:i:s')),
                ],
                'spacing' => 'sm'
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => '查看物流',
                            'uri' => $data['tracking_url'] ?? 'https://liff.line.me/YOUR_LIFF_ID'
                        ],
                        'style' => 'primary',
                        'color' => '#0066CC'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 支付成功通知模板
     */
    public static function payment($data)
    {
        return [
            'type' => 'bubble',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '✅ 支付成功',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#FF6B00'
                    ]
                ],
                'backgroundColor' => '#FFF5E6'
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    self::createInfoRow('订单号', $data['order_sn'] ?? ''),
                    self::createInfoRow('支付金额', '¥' . ($data['total_free'] ?? 0)),
                    self::createInfoRow('支付时间', $data['pay_time'] ?? date('Y-m-d H:i:s')),
                    [
                        'type' => 'separator',
                        'margin' => 'md'
                    ],
                    [
                        'type' => 'text',
                        'text' => $data['remark'] ?? '订单已支付，即将安排发货',
                        'size' => 'sm',
                        'color' => '#888888',
                        'margin' => 'md',
                        'wrap' => true
                    ]
                ],
                'spacing' => 'sm'
            ]
        ];
    }
    
    /**
     * 打包完成通知模板
     */
    public static function dabaosuccess($data)
    {
        return [
            'type' => 'bubble',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => '📋 打包完成',
                        'weight' => 'bold',
                        'size' => 'lg',
                        'color' => '#9933FF'
                    ]
                ],
                'backgroundColor' => '#F5E6FF'
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    self::createInfoRow('订单号', $data['order_sn'] ?? ''),
                    self::createInfoRow('包裹数量', ($data['pack_count'] ?? 1) . '件'),
                    self::createInfoRow('总重量', ($data['weight'] ?? 0) . 'kg'),
                    self::createInfoRow('体积', ($data['volume'] ?? 0) . 'cm³'),
                ],
                'spacing' => 'sm'
            ],
            'footer' => [
                'type' => 'box',
                'layout' => 'vertical',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => [
                            'type' => 'uri',
                            'label' => '去支付',
                            'uri' => $data['pay_url'] ?? 'https://liff.line.me/YOUR_LIFF_ID'
                        ],
                        'style' => 'primary',
                        'color' => '#9933FF'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * 创建信息行
     */
    private static function createInfoRow($label, $value)
    {
        return [
            'type' => 'box',
            'layout' => 'horizontal',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => $label,
                    'size' => 'sm',
                    'color' => '#555555',
                    'flex' => 2
                ],
                [
                    'type' => 'text',
                    'text' => (string)$value,
                    'size' => 'sm',
                    'color' => '#111111',
                    'flex' => 3,
                    'align' => 'end'
                ]
            ]
        ];
    }
}
```


#### 3.4.4 LINE消息服务基类

```php
<?php
namespace app\common\service\message\line;

use app\common\model\Wxapp as WxappModel;
use app\common\model\Setting as SettingModel;
use app\common\model\User;
use app\common\library\line\LineMessage;
use app\common\library\line\FlexTemplate;

/**
 * LINE消息通知服务基类
 */
abstract class Basics extends \app\common\service\Basics
{
    protected $param = [];
    
    /**
     * 发送消息通知
     */
    abstract public function send($param);
    
    /**
     * 发送LINE Flex消息
     * @param int $wxappId
     * @param string $userId LINE User ID
     * @param string $templateType 模板类型
     * @param array $data 模板数据
     * @return bool
     */
    protected function sendLineFlexMsg($wxappId, $userId, $templateType, $data)
    {
        // 获取LINE配置
        $lineConfig = SettingModel::getItem('lineMsg', $wxappId);
        
        if (empty($lineConfig['channel_access_token'])) {
            return false;
        }
        
        // 检查该消息类型是否启用
        if (isset($lineConfig[$templateType]) && $lineConfig[$templateType]['is_enable'] != '1') {
            return false;
        }
        
        // 创建LINE消息实例
        $lineMessage = new LineMessage(
            $lineConfig['channel_id'],
            $lineConfig['channel_secret'],
            $lineConfig['channel_access_token']
        );
        
        // 生成Flex Message内容
        $flexContents = $this->generateFlexContents($templateType, $data);
        $altText = $lineConfig[$templateType]['alt_text'] ?? '您有新的通知';
        
        // 发送消息
        return $lineMessage->sendFlexMessage($userId, $altText, $flexContents);
    }
    
    /**
     * 根据模板类型生成Flex内容
     */
    protected function generateFlexContents($templateType, $data)
    {
        switch ($templateType) {
            case 'inwarehouse':
                return FlexTemplate::inwarehouse($data);
            case 'sendpack':
                return FlexTemplate::sendpack($data);
            case 'payment':
                return FlexTemplate::payment($data);
            case 'dabaosuccess':
                return FlexTemplate::dabaosuccess($data);
            default:
                return FlexTemplate::inwarehouse($data);
        }
    }
    
    /**
     * 根据用户ID获取LINE User ID
     */
    protected function getLineUserIdByUserId($userId)
    {
        return User::where(['user_id' => $userId])->value('line_user_id');
    }
    
    /**
     * 字符串截取
     */
    protected function getSubstr($content, $length = 20)
    {
        return str_substr($content, $length);
    }
}
```

#### 3.4.5 LINE入库通知场景类示例

```php
<?php
namespace app\common\service\message\line;

use app\common\model\Setting as SettingModel;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * LINE消息通知 - 包裹入库
 */
class Inwarehouse extends Basics
{
    protected $param = [
        'order' => [],
        'order_type' => OrderTypeEnum::MASTER,
    ];
    
    private $pageUrl = 'pages/indexs/dairuku_xq/dairuku_xq';
    
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        
        // 获取LINE配置
        $lineConfig = SettingModel::getItem('lineMsg', $orderInfo['wxapp_id']);
        
        if (empty($lineConfig) || $lineConfig['inwarehouse']['is_enable'] != '1') {
            return false;
        }
        
        // 获取用户LINE ID
        $lineUserId = $this->getLineUserIdByUserId($this->param['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        // 获取通知设置
        $noticesetting = SettingModel::getItem('notice');
        
        // 构建模板数据
        $data = [
            'shop_name' => $orderInfo['shop_name'] ?? '',
            'express_num' => $orderInfo['express_num'] ?? '',
            'entering_warehouse_time' => $orderInfo['entering_warehouse_time'] ?? '',
            'weight' => $orderInfo['weight'] ?? 0,
            'remark' => $noticesetting['enter']['describe'] ?? '包裹已入库，可提交打包',
            'detail_url' => "https://liff.line.me/YOUR_LIFF_ID/{$this->pageUrl}?id={$orderInfo['id']}&rtype=10"
        ];
        
        return $this->sendLineFlexMsg(
            $orderInfo['wxapp_id'],
            $lineUserId,
            'inwarehouse',
            $data
        );
    }
}
```


### 3.5 消息分发服务扩展

```php
<?php
// 修改 Message.php，添加LINE消息支持

namespace app\common\service;

class Message extends Basics
{
    // 微信场景列表 (保持不变)
    private static $wxSceneList = [
        'package.inwarehouse' => 'app\common\service\message\package\Inwarehouse',
        'package.sendpack' => 'app\common\service\message\package\Sendpack',
        // ... 其他微信场景
    ];
    
    // LINE场景列表 (新增)
    private static $lineSceneList = [
        'package.inwarehouse' => 'app\common\service\message\line\Inwarehouse',
        'package.sendpack' => 'app\common\service\message\line\Sendpack',
        'package.payment' => 'app\common\service\message\line\Payment',
        'package.dabaosuccess' => 'app\common\service\message\line\Dabaosuccess',
        'package.payorder' => 'app\common\service\message\line\Payorder',
        'package.toshop' => 'app\common\service\message\line\Toshop',
    ];
    
    /**
     * 发送消息通知 (同时发送微信和LINE)
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
     * 发送LINE消息
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


## 四、配置管理

### 4.1 Setting模型扩展

在 `Setting.php` 的 `defaultData()` 方法中添加LINE配置：

```php
// LINE消息通知配置
'lineMsg' => [
    'key' => 'lineMsg',
    'describe' => 'LINE消息通知',
    'values' => [
        // LINE OA Channel配置
        'channel_id' => '',
        'channel_secret' => '',
        'channel_access_token' => '',
        
        // LIFF配置 (用于消息中的跳转链接)
        'liff_id' => '',
        'liff_url' => 'https://liff.line.me/',
        
        // 各场景配置
        'inwarehouse' => [
            'is_enable' => '0',
            'alt_text' => '📦 包裹入库通知',
        ],
        'sendpack' => [
            'is_enable' => '0',
            'alt_text' => '🚚 发货通知',
        ],
        'payment' => [
            'is_enable' => '0',
            'alt_text' => '✅ 支付成功通知',
        ],
        'dabaosuccess' => [
            'is_enable' => '0',
            'alt_text' => '📋 打包完成通知',
        ],
        'payorder' => [
            'is_enable' => '0',
            'alt_text' => '💰 付款单生成通知',
        ],
        'toshop' => [
            'is_enable' => '0',
            'alt_text' => '🏪 到仓通知',
        ],
        'outapply' => [
            'is_enable' => '0',
            'alt_text' => '📤 出库申请通知',
        ],
    ],
],
```

### 4.2 后台管理界面配置项

```php
// 后台设置控制器中添加LINE配置保存
public function lineMsg()
{
    if ($this->request->isGet()) {
        return $this->renderSuccess('', [
            'values' => SettingModel::getItem('lineMsg')
        ]);
    }
    
    $model = new SettingModel;
    if ($model->edit('lineMsg', $this->postData('lineMsg'))) {
        return $this->renderSuccess('操作成功');
    }
    return $this->renderError($model->getError() ?: '操作失败');
}
```


## 五、LINE Messaging API 参考

### 5.1 API端点汇总

| 功能 | 方法 | 端点 |
|-----|------|-----|
| 推送消息 | POST | `/v2/bot/message/push` |
| 批量推送 | POST | `/v2/bot/message/multicast` |
| 广播消息 | POST | `/v2/bot/message/broadcast` |
| 回复消息 | POST | `/v2/bot/message/reply` |
| 获取用户资料 | GET | `/v2/bot/profile/{userId}` |
| 获取消息配额 | GET | `/v2/bot/message/quota` |

### 5.2 请求头格式

```
Authorization: Bearer {channel_access_token}
Content-Type: application/json
```

### 5.3 Push Message 请求体

```json
{
    "to": "U4af4980629...",
    "messages": [
        {
            "type": "flex",
            "altText": "通知消息",
            "contents": {
                "type": "bubble",
                "body": {
                    "type": "box",
                    "layout": "vertical",
                    "contents": [...]
                }
            }
        }
    ],
    "notificationDisabled": false,
    "customAggregationUnits": ["promotion"]
}
```

### 5.4 响应状态码

| 状态码 | 说明 |
|-------|-----|
| 200 | 成功 |
| 400 | 请求参数错误 |
| 401 | 认证失败 (Token无效) |
| 403 | 无权限 |
| 429 | 请求过于频繁 |
| 500 | LINE服务器错误 |

### 5.5 错误响应格式

```json
{
    "message": "The request body has 1 error(s)",
    "details": [
        {
            "message": "May not be empty",
            "property": "messages[0].text"
        }
    ]
}
```


## 六、实现步骤清单

### 6.1 Phase 1: 基础设施

- [ ] 创建 `source/application/common/library/line/` 目录
- [ ] 实现 `LineBase.php` - API基类
- [ ] 实现 `LineMessage.php` - 消息发送封装
- [ ] 实现 `FlexTemplate.php` - Flex模板生成器
- [ ] 数据库添加 `line_user_id` 字段到用户表
- [ ] Setting模型添加 `lineMsg` 默认配置

### 6.2 Phase 2: 消息场景类

- [ ] 创建 `source/application/common/service/message/line/` 目录
- [ ] 实现 `Basics.php` - LINE消息基类
- [ ] 实现 `Inwarehouse.php` - 入库通知
- [ ] 实现 `Sendpack.php` - 发货通知
- [ ] 实现 `Payment.php` - 支付通知
- [ ] 实现 `Dabaosuccess.php` - 打包完成通知
- [ ] 实现 `Payorder.php` - 付款单生成通知
- [ ] 实现 `Toshop.php` - 到仓通知
- [ ] 实现 `Outapply.php` - 出库申请通知

### 6.3 Phase 3: 集成与配置

- [ ] 修改 `Message.php` 添加LINE消息分发
- [ ] 后台添加LINE配置管理界面
- [ ] LINE登录时保存 `line_user_id`
- [ ] 测试各场景消息发送

### 6.4 Phase 4: 优化与监控

- [ ] 添加消息发送日志记录
- [ ] 添加发送失败重试机制
- [ ] 添加消息发送统计
- [ ] 性能优化 (异步发送)


## 七、注意事项

### 7.1 LINE API 限制

1. **消息配额**: 免费方案每月500条推送消息，超出需付费
2. **批量推送**: multicast 单次最多500个用户
3. **消息大小**: 单条消息最大5MB
4. **Flex Message**: 最多12个气泡(bubble)

### 7.2 用户ID获取

LINE User ID 需要在以下场景获取并保存：
1. 用户通过LIFF登录时
2. 用户关注LINE OA时 (通过Webhook)
3. 用户发送消息时 (通过Webhook)

### 7.3 与微信的差异处理

| 差异点 | 微信 | LINE | 处理方式 |
|-------|-----|------|---------|
| 模板管理 | 后台申请模板ID | 代码定义Flex | 使用FlexTemplate类 |
| 用户标识 | gzh_openid | line_user_id | 用户表新增字段 |
| 跳转链接 | 小程序页面路径 | LIFF URL | 配置LIFF ID |
| 消息格式 | 固定模板变量 | 自定义JSON | Flex Message |

### 7.4 安全建议

1. Channel Access Token 应存储在数据库，不要硬编码
2. 使用HTTPS进行所有API调用
3. 验证Webhook签名防止伪造请求
4. 定期轮换Channel Access Token

## 八、参考文档

- [LINE Messaging API 官方文档](https://developers.line.biz/en/docs/messaging-api/)
- [Flex Message Simulator](https://developers.line.biz/flex-simulator/)
- [LINE Developers Console](https://developers.line.biz/console/)
- [LIFF 文档](https://developers.line.biz/en/docs/liff/)

---

*文档版本: 1.0*
*创建日期: 2026-01-14*
*适用于: LINE Mini App 集运系统*
