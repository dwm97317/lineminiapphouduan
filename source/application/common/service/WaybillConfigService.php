<?php

namespace app\common\service;

use app\common\model\Setting as SettingModel;

/**
 * 面单配置服务类
 * Class WaybillConfigService
 * @package app\common\service
 */
class WaybillConfigService
{
    private $wxapp_id;

    public function __construct($wxapp_id = null)
    {
        $this->wxapp_id = $wxapp_id;
    }

    /**
     * 获取配置
     * @param string $expressType 快递类型 (zhongtong/shunfeng)
     * @return array
     */
    public function getConfig($expressType)
    {
        $key = 'waybill_config_' . $expressType;
        
        // 如果没有 wxapp_id，直接使用不带 wxapp_id 的方法
        if ($this->wxapp_id) {
            $config = SettingModel::getItem($key, $this->wxapp_id);
        } else {
            $config = SettingModel::getItem($key);
        }
        
        // 如果配置不存在，返回默认配置
        if (empty($config)) {
            return $this->getDefaultConfig($expressType);
        }
        
        return $config;
    }

    /**
     * 保存配置
     * @param string $expressType
     * @param array $config
     * @return bool
     */
    public function saveConfig($expressType, $config)
    {
        if (!$this->validateConfig($config)) {
            return false;
        }

        $key = 'waybill_config_' . $expressType;
        $describe = $this->getExpressName($expressType) . '快递面单配置';
        
        $wxapp_id = $this->wxapp_id ?: SettingModel::$wxapp_id;
        
        $model = SettingModel::get(['key' => $key, 'wxapp_id' => $wxapp_id]);
        
        if ($model) {
            $result = $model->save([
                'values' => $config,
                'describe' => $describe,
                'update_time' => time()
            ]);
        } else {
            $model = new SettingModel();
            $result = $model->save([
                'key' => $key,
                'values' => $config,
                'describe' => $describe,
                'wxapp_id' => $wxapp_id,
                'update_time' => time()
            ]);
        }
        
        \think\Cache::rm('setting_' . $wxapp_id);
        
        return $result !== false;
    }

    /**
     * 获取默认配置
     * @param string $expressType
     * @return array
     */
    public function getDefaultConfig($expressType)
    {
        $baseConfig = [
            'fields' => [
                'sender_name' => true,
                'sender_phone' => true,
                'sender_address' => true,
                'receiver_name' => true,
                'receiver_phone' => true,
                'receiver_address' => true,
                'item_name' => true,
                'weight' => true,
                'volume' => false,
                'remark' => false,
                'quantity' => true,
            ],
            'print_params' => [
                'paper_size' => '76x130',
                'orientation' => 'portrait',
                'scale' => 100,
            ],
        ];

        // 快递公司特定字段
        if ($expressType === 'zhongtong') {
            $baseConfig['company_fields'] = [
                'site_code' => '',
                'site_name' => '',
            ];
        } elseif ($expressType === 'shunfeng') {
            $baseConfig['company_fields'] = [
                'monthly_card' => '',
                'payment_method' => '1',
            ];
        }

        return $baseConfig;
    }

    /**
     * 验证配置
     * @param array $config
     * @return bool
     */
    public function validateConfig($config)
    {
        // 检查必需的配置项
        if (!isset($config['fields']) || !is_array($config['fields'])) {
            return false;
        }

        if (!isset($config['print_params']) || !is_array($config['print_params'])) {
            return false;
        }

        // 验证打印参数
        $printParams = $config['print_params'];
        if (empty($printParams['paper_size']) || empty($printParams['orientation'])) {
            return false;
        }

        return true;
    }

    /**
     * 获取字段定义列表
     * @param string $expressType
     * @return array
     */
    public function getFieldDefinitions($expressType)
    {
        $baseFields = [
            'fields' => [
                ['key' => 'sender_name', 'label' => '寄件人姓名', 'required' => true],
                ['key' => 'sender_phone', 'label' => '寄件人电话', 'required' => true],
                ['key' => 'sender_address', 'label' => '寄件人地址', 'required' => true],
                ['key' => 'receiver_name', 'label' => '收件人姓名', 'required' => true],
                ['key' => 'receiver_phone', 'label' => '收件人电话', 'required' => true],
                ['key' => 'receiver_address', 'label' => '收件人地址', 'required' => true],
                ['key' => 'item_name', 'label' => '物品名称', 'required' => false],
                ['key' => 'weight', 'label' => '重量', 'required' => false],
                ['key' => 'volume', 'label' => '体积', 'required' => false],
                ['key' => 'remark', 'label' => '备注', 'required' => false],
                ['key' => 'quantity', 'label' => '数量', 'required' => false],
            ],
            'print_params' => [
                ['key' => 'paper_size', 'label' => '纸张大小', 'type' => 'select', 'options' => ['76x130' => '76mm x 130mm']],
                ['key' => 'orientation', 'label' => '打印方向', 'type' => 'select', 'options' => ['portrait' => '纵向', 'landscape' => '横向']],
                ['key' => 'scale', 'label' => '缩放比例(%)', 'type' => 'number', 'min' => 50, 'max' => 150],
            ],
        ];

        // 快递公司特定字段
        if ($expressType === 'zhongtong') {
            $baseFields['company_fields'] = [
                ['key' => 'site_code', 'label' => '网点代码', 'type' => 'text'],
                ['key' => 'site_name', 'label' => '网点名称', 'type' => 'text'],
            ];
        } elseif ($expressType === 'shunfeng') {
            $baseFields['company_fields'] = [
                ['key' => 'monthly_card', 'label' => '月结卡号', 'type' => 'text'],
                ['key' => 'payment_method', 'label' => '付款方式', 'type' => 'select', 'options' => ['1' => '寄付', '2' => '到付', '3' => '月结']],
            ];
        }

        return $baseFields;
    }

    /**
     * 获取快递公司名称
     * @param string $expressType
     * @return string
     */
    private function getExpressName($expressType)
    {
        $names = [
            'zhongtong' => '中通',
            'shunfeng' => '顺丰',
        ];
        return $names[$expressType] ?? $expressType;
    }
}
