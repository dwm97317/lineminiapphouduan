<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;

/**
 * LINE小程序配置
 * Class LineConfig
 * @package app\store\controller\setting
 */
class LineConfig extends Controller
{
    /**
     * LINE配置页面
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            // 获取当前配置
            $line_config = SettingModel::getItem('line_config');
            $line_messaging = SettingModel::getItem('line_messaging');
            $line_pay = SettingModel::getItem('line_pay');
            $customer_contact = SettingModel::getItem('customer_contact');
            
            // 确保 customer_contact 是数组
            if (empty($customer_contact)) {
                $customer_contact = [
                    'hotline_th' => '',
                    'line_support' => '',
                    'wechat' => ''
                ];
            }
            
            // 定义每个消息类型的可用变量
            $availableVariables = $this->getAvailableVariables();
            
            return $this->fetch('index', compact('line_config', 'line_messaging', 'line_pay', 'customer_contact', 'availableVariables'));
        }
        
        $data = $this->postData();
        $model = new SettingModel;
        
        // 根据提交的数据类型保存
        $key = key($data);
        
        // 如果是 line_messaging，处理选中的变量
        if ($key === 'line_messaging' && isset($data[$key]['templates'])) {
            foreach ($data[$key]['templates'] as $type => &$template) {
                if (isset($template['selected_variables'])) {
                    // 将选中的变量转换为 JSON 数组
                    $template['variables'] = $template['selected_variables'];
                    unset($template['selected_variables']);
                }
            }
        }
        
        // 如果是 customer_contact，验证数据
        if ($key === 'customer_contact') {
            $validation = $this->validateCustomerContact($data[$key]);
            if ($validation !== true) {
                return $validation;
            }
        }
        
        if ($model->edit($key, $data[$key])) {
            return $this->renderSuccess('保存成功');
        }
        return $this->renderError($model->getError() ?: '保存失败');
    }
    
    /**
     * 获取每个消息类型的可用变量
     * @return array
     */
    private function getAvailableVariables()
    {
        return [
            'inwarehouse' => [
                'shop_name' => ['label' => '仓库名称', 'example' => '泰国仓库', 'required' => true],
                'express_num' => ['label' => '快递单号', 'example' => 'SF1234567890', 'required' => true],
                'entering_warehouse_time' => ['label' => '入库时间', 'example' => '2024-01-15 10:30:00', 'required' => true],
                'weight' => ['label' => '重量(kg)', 'example' => '1.5', 'required' => true],
                'volume' => ['label' => '体积(cm³)', 'example' => '5000', 'required' => false],
                'remark' => ['label' => '备注信息', 'example' => '包裹已入库', 'required' => false],
                'detail_url' => ['label' => '详情链接', 'example' => 'https://...', 'required' => true],
                'package_count' => ['label' => '包裹数量', 'example' => '3', 'required' => false],
                'user_name' => ['label' => '用户姓名', 'example' => '张三', 'required' => false],
            ],
            'sendpack' => [
                'order_sn' => ['label' => '订单号', 'example' => 'ORD20240115001', 'required' => true],
                't_order_sn' => ['label' => '国际单号', 'example' => 'INT20240115001', 'required' => true],
                'weight' => ['label' => '重量(kg)', 'example' => '2.5', 'required' => true],
                't_name' => ['label' => '物流线路', 'example' => '标准快递', 'required' => true],
                'send_time' => ['label' => '发货时间', 'example' => '2024-01-15 14:00:00', 'required' => true],
                'tracking_url' => ['label' => '物流追踪链接', 'example' => 'https://...', 'required' => true],
                'receiver_name' => ['label' => '收件人姓名', 'example' => '李四', 'required' => false],
                'receiver_phone' => ['label' => '收件人电话', 'example' => '0812345678', 'required' => false],
                'receiver_address' => ['label' => '收件地址', 'example' => 'Bangkok, Thailand', 'required' => false],
            ],
            'payment' => [
                'order_sn' => ['label' => '订单号', 'example' => 'ORD20240115001', 'required' => true],
                'total_free' => ['label' => '支付金额', 'example' => '150.00', 'required' => true],
                'pay_time' => ['label' => '支付时间', 'example' => '2024-01-15 15:30:00', 'required' => true],
                'pay_method' => ['label' => '支付方式', 'example' => 'LINE Pay', 'required' => false],
                'transaction_id' => ['label' => '交易号', 'example' => 'TXN123456', 'required' => false],
                'remark' => ['label' => '备注信息', 'example' => '支付成功', 'required' => false],
                'order_url' => ['label' => '订单链接', 'example' => 'https://...', 'required' => false],
            ],
            'dabaosuccess' => [
                'order_sn' => ['label' => '订单号', 'example' => 'ORD20240115001', 'required' => true],
                'pack_count' => ['label' => '包裹数量', 'example' => '3', 'required' => true],
                'weight' => ['label' => '总重量(kg)', 'example' => '5.2', 'required' => true],
                'volume' => ['label' => '总体积(cm³)', 'example' => '12000', 'required' => true],
                'pack_time' => ['label' => '打包时间', 'example' => '2024-01-15 16:00:00', 'required' => false],
                'estimated_fee' => ['label' => '预估费用', 'example' => '200.00', 'required' => false],
                'pay_url' => ['label' => '支付链接', 'example' => 'https://...', 'required' => true],
            ],
            'payorder' => [
                'order_sn' => ['label' => '订单号', 'example' => 'ORD20240115001', 'required' => true],
                'amount' => ['label' => '应付金额', 'example' => '200.00', 'required' => true],
                'create_time' => ['label' => '生成时间', 'example' => '2024-01-15 17:00:00', 'required' => true],
                'due_date' => ['label' => '到期日期', 'example' => '2024-01-20', 'required' => false],
                'pay_url' => ['label' => '支付链接', 'example' => 'https://...', 'required' => true],
            ],
            'toshop' => [
                'shop_name' => ['label' => '仓库名称', 'example' => '泰国仓库', 'required' => true],
                'express_num' => ['label' => '快递单号', 'example' => 'SF1234567890', 'required' => true],
                'arrival_time' => ['label' => '到仓时间', 'example' => '2024-01-15 09:00:00', 'required' => true],
                'express_company' => ['label' => '快递公司', 'example' => 'SF Express', 'required' => false],
                'sender_name' => ['label' => '发件人', 'example' => '王五', 'required' => false],
            ],
            'outapply' => [
                'apply_sn' => ['label' => '申请单号', 'example' => 'APPLY20240115001', 'required' => true],
                'package_count' => ['label' => '包裹数量', 'example' => '5', 'required' => true],
                'apply_time' => ['label' => '申请时间', 'example' => '2024-01-15 18:00:00', 'required' => true],
                'apply_reason' => ['label' => '申请原因', 'example' => '退货', 'required' => false],
                'status' => ['label' => '审核状态', 'example' => '待审核', 'required' => false],
            ],
        ];
    }
    
    /**
     * 测试消息发送
     * @return array
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
        
        // 获取 wxapp_id
        $wxappId = $this->getWxappId();
        
        // 临时设置 wxapp_id 和 member_id
        $testData['wxapp_id'] = $wxappId;
        $testData['member_id'] = 0; // 测试用，直接使用提供的 LINE User ID
        
        // 创建服务实例并发送
        $service = new $className();
        
        // 临时覆盖 getLineUserIdByUserId 方法的返回值
        // 通过直接调用 sendLineFlexMsg 方法
        try {
            $config = SettingModel::getItem('line_messaging', $wxappId);
            if (empty($config['is_enable']) || $config['is_enable'] != '1') {
                return $this->renderError('LINE 消息通知未启用');
            }
            
            $template = $config['templates'][$messageType] ?? null;
            if (!$template || $template['is_enable'] != '1') {
                return $this->renderError('该消息模板未启用');
            }
            
            // 使用反射调用 protected 方法
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('sendLineFlexMsg');
            $method->setAccessible(true);
            
            $result = $method->invoke($service, $wxappId, $lineUserId, $messageType, $testData);
            
            if ($result) {
                return $this->renderSuccess('测试消息发送成功');
            }
            
            return $this->renderError('测试消息发送失败，请检查日志');
            
        } catch (\Exception $e) {
            return $this->renderError('发送失败：' . $e->getMessage());
        }
    }
    
    /**
     * 预览模板
     * @return array
     */
    public function previewTemplate()
    {
        $messageType = $this->request->get('type');
        
        if (empty($messageType)) {
            return $this->renderError('参数错误');
        }
        
        $wxappId = $this->getWxappId();
        $config = SettingModel::getItem('line_messaging', $wxappId);
        $template = $config['templates'][$messageType] ?? null;
        
        if (!$template) {
            return $this->renderError('模板不存在');
        }
        
        return $this->renderSuccess('', [
            'template' => $template,
            'flex_simulator_url' => 'https://developers.line.biz/flex-simulator/'
        ]);
    }
    
    /**
     * 获取测试数据
     * @param string $messageType 消息类型
     * @return array
     */
    private function getTestData($messageType)
    {
        $testDataMap = [
            'inwarehouse' => [
                'shop_name' => '泰国仓库',
                'express_num' => 'TEST' . date('YmdHis'),
                'entering_warehouse_time' => date('Y-m-d H:i:s'),
                'weight' => 1.5,
                'remark' => '这是一条测试消息',
                'id' => 999,
                'detail_url' => 'https://example.com/package/detail?id=999',
                // 添加测试图片（使用公开的测试图片URL）
                'images' => [
                    'https://via.placeholder.com/800x600/1DB446/FFFFFF?text=Package+Photo+1',
                    'https://via.placeholder.com/800x600/0066CC/FFFFFF?text=Package+Photo+2',
                ]
            ],
            'sendpack' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                't_order_sn' => 'INT' . date('YmdHis'),
                'weight' => 2.5,
                't_name' => '标准快递',
                'send_time' => date('Y-m-d H:i:s'),
                'tracking_url' => 'https://example.com/tracking?order_sn=ORD' . date('YmdHis'),
                'images' => [
                    'https://via.placeholder.com/800x600/0066CC/FFFFFF?text=Shipping+Label',
                ]
            ],
            'payment' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                'total_free' => 150.00,
                'pay_time' => date('Y-m-d H:i:s'),
                'remark' => '支付成功，感谢您的使用',
                'order_url' => 'https://example.com/order/detail'
            ],
            'dabaosuccess' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                'pack_count' => 3,
                'weight' => 5.2,
                'volume' => 12000,
                'order_id' => 888,
                'pay_url' => 'https://example.com/payment?order_id=888',
                'images' => [
                    'https://via.placeholder.com/800x600/9933FF/FFFFFF?text=Packed+Box+1',
                    'https://via.placeholder.com/800x600/9933FF/FFFFFF?text=Packed+Box+2',
                    'https://via.placeholder.com/800x600/9933FF/FFFFFF?text=Packed+Box+3',
                ]
            ],
            'payorder' => [
                'order_sn' => 'ORD' . date('YmdHis'),
                'amount' => 200.00,
                'create_time' => date('Y-m-d H:i:s'),
                'order_id' => 777,
                'pay_url' => 'https://example.com/payment?order_id=777'
            ],
            'toshop' => [
                'shop_name' => '泰国仓库',
                'express_num' => 'TEST' . date('YmdHis'),
                'arrival_time' => date('Y-m-d H:i:s'),
                'images' => [
                    'https://via.placeholder.com/800x600/00CC99/FFFFFF?text=Arrival+Photo',
                ]
            ],
            'outapply' => [
                'apply_sn' => 'APPLY' . date('YmdHis'),
                'package_count' => 5,
                'apply_time' => date('Y-m-d H:i:s')
            ],
        ];
        
        return $testDataMap[$messageType] ?? [];
    }
    
    /**
     * 验证客户联系配置数据
     * @param array $data 客户联系配置数据
     * @return bool|array true 表示验证通过，array 表示验证失败并返回错误信息
     */
    private function validateCustomerContact($data)
    {
        // Hotline: 允许数字、+、-、空格、括号
        if (!empty($data['hotline_th']) && !preg_match('/^[\d\s\+\-\(\)]+$/', $data['hotline_th'])) {
            return $this->renderError('电话号码格式不正确，只能包含数字、+、-、空格和括号');
        }
        
        // LINE Support: 允许字母、数字、下划线、点
        if (!empty($data['line_support']) && !preg_match('/^[a-zA-Z0-9_\.]+$/', $data['line_support'])) {
            return $this->renderError('LINE ID 格式不正确，只能包含字母、数字、下划线和点');
        }
        
        // WeChat: 允许字母、数字、下划线、连字符
        if (!empty($data['wechat']) && !preg_match('/^[a-zA-Z0-9_\-]+$/', $data['wechat'])) {
            return $this->renderError('微信号格式不正确，只能包含字母、数字、下划线和连字符');
        }
        
        return true;
    }


}
