<?php

namespace app\common\library\express;

use app\common\model\Setting as SettingModel;

/**
 * 顺丰快递类
 * Class ShunfengExpress
 * @package app\common\library\express
 */
class ShunfengExpress implements ExpressInterface
{
    private $config;
    private $wxapp_id;

    public function __construct($wxapp_id = null)
    {
        $this->wxapp_id = $wxapp_id ?: self::getWxappId();
        $this->loadConfig();
    }

    /**
     * 加载配置
     */
    private function loadConfig()
    {
        $expressConfig = SettingModel::getItem('express_api_config', $this->wxapp_id);
        $this->config = $expressConfig['shunfeng'] ?? [];
    }

    /**
     * 创建快递订单
     * @param array $orderData
     * @return array
     */
    public function createOrder($orderData)
    {
        // 1. 获取配置
        $this->loadConfig();
        if (empty($this->config['api_key']) || empty($this->config['api_secret'])) {
            return ['success' => false, 'message' => '请先要在后台配置顺丰API信息(PartnerID和Checkword)'];
        }

        // 2. 准备API参数
        $serviceCode = "EXP_RECE_CREATE_ORDER";
        $partnerID = $this->config['api_key']; // 对应 PartnerID
        $checkword = $this->config['api_secret']; // 对应 Checkword
        $custid = $this->config['custid'] ?? ''; // 月结卡号
        $payMethod = $this->config['pay_method'] ?? 1; // 1:寄付 2:收付 3:第三方

        // 3. 构建 msgData
        // 注意：这里需要根据 orderData 映射到顺丰的字段
        // ExpressService 映射: 
        // $orderData['express_code'] -> expressTypeId (如 T4)
        // $orderData['vas'] -> addedServices (如 INSURE)
        
        $msgDataArray = [
            'orderId' => $orderData['order_sn'],
            'expressTypeId' => $orderData['express_code'] ?? '1', // 默认标准快递
            'payMethod' => (int)$payMethod, 
            'custid' => $custid, // 顺丰文档要求：月结支付时必填
            'isOneselfPickup' => 0,
            
            // 寄件人信息 (读取配置或默认仓库)
            'contactInfoList' => [
                [
                    'contactType' => 1, // 寄件人
                    'company' => mb_substr($orderData['sender']['company'] ?? '集运中心', 0, 30),
                    'contact' => mb_substr($orderData['sender']['name'] ?? '发货员', 0, 30),
                    'mobile' => $orderData['sender']['mobile'] ?? '',
                    'province' => $orderData['sender']['province'] ?? '',
                    'city' => $orderData['sender']['city'] ?? '',
                    'county' => $orderData['sender']['region'] ?? '',
                    'address' => mb_substr($orderData['sender']['address'] ?? '', 0, 100),
                ],
                [
                    'contactType' => 2, // 收件人
                    'company' => '',
                    'contact' => mb_substr($orderData['receiver']['name'], 0, 30),
                    'mobile' => $orderData['receiver']['phone'],
                    'province' => $orderData['receiver']['province'],
                    'city' => $orderData['receiver']['city'],
                    'county' => $orderData['receiver']['region'],
                    'address' => mb_substr($orderData['receiver']['detail'], 0, 100),
                ]
            ],
            
            // 货物信息
            'cargoDetails' => [
                [
                    'name' => '集运包裹', // 默认统称，后续可优化为真实商品
                    'count' => 1,
                    'unit' => '件',
                    'weight' => (float)($orderData['weight'] ?? 1),
                    'amount' => 1, // 声明价值，默认1
                    'currency' => 'CNY',
                ]
            ],
        ];

        // 自动填充月结卡号 (如果是寄付)
        if ($payMethod == 1 && !empty($custid)) {
            $msgDataArray['monthlyCard'] = $custid;
        }

        // 处理增值服务
        if (!empty($orderData['vas']) && is_array($orderData['vas'])) {
            $msgDataArray['addedServices'] = [];
            foreach ($orderData['vas'] as $vas) {
                if (!empty($vas['code'])) {
                    $msgDataArray['addedServices'][] = [
                        'name' => $vas['code'],
                        'value' => (string)($vas['value'] ?? ''),
                    ];
                }
            }
        }

        $msgData = json_encode($msgDataArray, JSON_UNESCAPED_UNICODE);
        
        // 4. 发起请求
        $timestamp = time();
        $requestID = $this->createUuid();
        $msgDigest = base64_encode(md5((urlencode($msgData .$timestamp. $checkword)), TRUE));

        $postData = [
            'partnerID' => $partnerID,
            'requestID' => $requestID,
            'serviceCode' => $serviceCode,
            'timestamp' => $timestamp,
            'msgDigest' => $msgDigest,
            'msgData' => $msgData
        ];

        // 确定API地址
        $url = $this->config['api_url'] ?: 'https://bspgw.sf-express.com/std/service';

        try {
            $result = $this->post($url, $postData);
            $response = json_decode($result, true);

            // 5. 解析结果
            if (isset($response['apiResultCode']) && $response['apiResultCode'] === 'A1000') {
                // 成功，注意：有些业务逻辑错误可能在 apiResultData 中
                $apiResultData = json_decode($response['apiResultData'], true);
                if ($apiResultData['success']) {
                    return [
                        'success' => true,
                        'waybill_no' => $apiResultData['msgData']['waybillNoInfoList'][0]['waybillNo'] ?? '',
                        'order_sn' => $orderData['order_sn'],
                        'full_response' => $response
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'SF业务失败: ' . ($apiResultData['errorMsg'] ?? '未知错误'),
                        'full_response' => $response
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'SF调用识别: ' . ($response['apiErrorMsg'] ?? '未知错误'),
                    'full_response' => $response
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '请求异常: ' . $e->getMessage()
            ];
        }
    }

    private function createUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    private function post($url, $data) {
        $postdata = http_build_query($data);
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded;charset=utf-8',
                'content' => $postdata,
                'timeout' => 15
            ]
        ];
        $context = stream_context_create($options);
        return file_get_contents($url, false, $context);
    }
}
