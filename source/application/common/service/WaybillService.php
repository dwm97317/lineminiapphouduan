<?php

namespace app\common\service;

use app\common\library\express\ZhongtongExpress;
use app\common\library\express\ShunfengExpress;
use app\store\model\Inpack;
use app\store\model\UserAddress;
use app\store\model\WaybillRecord;

/**
 * 面单服务类
 * Class WaybillService
 * @package app\common\service
 */
class WaybillService
{
    private $wxapp_id;
    private $configService;

    public function __construct($wxapp_id = null)
    {
        $this->wxapp_id = $wxapp_id;
        $this->configService = new WaybillConfigService($wxapp_id);
    }

    /**
     * 生成面单
     * @param int $inpackId 订单ID
     * @param string $expressType 快递类型 (zhongtong/shunfeng)
     * @return array
     */
    public function generateWaybill($inpackId, $expressType)
    {
        // 1. 验证订单
        $validation = $this->validateOrder($inpackId);
        if (!$validation['success']) {
            return $validation;
        }

        $order = $validation['order'];
        $address = $validation['address'];

        // 2. 获取配置
        $config = $this->configService->getConfig($expressType);

        // 3. 准备订单数据
        $orderData = $this->prepareOrderData($order, $address, $config);

        // 4. 调用快递API
        $express = $this->getExpressInstance($expressType);
        $result = $express->getWaybill($orderData);

        if ($result['success']) {
            // 5. 应用配置过滤字段
            $result['html'] = $this->applyConfig($result['html'], $config);
        }

        return $result;
    }

    /**
     * 验证订单信息
     * @param int $inpackId
     * @return array
     */
    public function validateOrder($inpackId)
    {
        $order = Inpack::find($inpackId);
        
        if (!$order) {
            return [
                'success' => false,
                'message' => '订单不存在'
            ];
        }

        // 检查 address_id
        if (empty($order['address_id'])) {
            return [
                'success' => false,
                'message' => '订单未设置收货地址，请先选择收货地址'
            ];
        }

        // 查询收货地址
        $address = UserAddress::find($order['address_id']);
        if (!$address) {
            return [
                'success' => false,
                'message' => '收货地址不存在（address_id: ' . $order['address_id'] . '），请重新选择地址'
            ];
        }

        // 验证地址完整性
        if (empty($address['name']) || empty($address['phone']) || empty($address['detail'])) {
            return [
                'success' => false,
                'message' => '订单收货信息不完整，请先完善收货地址（姓名、电话、详细地址）'
            ];
        }

        return [
            'success' => true,
            'order' => $order,
            'address' => $address
        ];
    }

    /**
     * 准备订单数据
     * @param $order
     * @param $address
     * @param $config
     * @return array
     */
    private function prepareOrderData($order, $address, $config)
    {
        return [
            'order_sn' => $order['order_sn'],
            'inpack_id' => $order['id'],
            'weight' => $order['weight'] ?? 0,
            'volume' => $order['volume'] ?? 0,
            'quantity' => !empty($order['pack_ids']) ? count(explode(',', $order['pack_ids'])) : 1,
            'receiver' => [
                'name' => $address['name'],
                'phone' => $address['phone'],
                'country' => $address['country'] ?? '',
                'province' => $address['province'] ?? '',
                'city' => $address['city'] ?? '',
                'region' => $address['region'] ?? '',
                'detail' => $address['detail'] ?? '',
                'street' => $address['street'] ?? '',
                'door' => $address['door'] ?? '',
                'code' => $address['code'] ?? '',
                'email' => $address['email'] ?? '',
            ],
            'config' => $config,
        ];
    }

    /**
     * 应用配置过滤字段
     * @param string $html
     * @param array $config
     * @return string
     */
    public function applyConfig($html, $config)
    {
        // TODO: 根据配置的字段显示/隐藏设置，动态调整HTML
        return $html;
    }

    /**
     * 保存打印记录
     * @param array $data
     * @return bool|int
     */
    public function saveRecord($data)
    {
        return WaybillRecord::add($data);
    }

    /**
     * 获取快递实例
     * @param string $expressType
     * @return ZhongtongExpress|ShunfengExpress
     */
    private function getExpressInstance($expressType)
    {
        switch ($expressType) {
            case 'zhongtong':
                return new ZhongtongExpress($this->wxapp_id);
            case 'shunfeng':
                return new ShunfengExpress($this->wxapp_id);
            default:
                throw new \Exception('不支持的快递类型: ' . $expressType);
        }
    }
}
