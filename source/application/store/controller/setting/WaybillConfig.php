<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\common\service\WaybillConfigService;
use app\common\model\Setting as SettingModel;
use app\store\model\Setting as StoreSettingModel;

/**
 * 面单配置控制器
 * Class WaybillConfig
 * @package app\store\controller\setting
 */
class WaybillConfig extends Controller
{
    private $configService;

    public function _initialize()
    {
        parent::_initialize();
        // 获取 wxapp_id，如果不存在则使用默认值
        $wxappId = isset($this->store['wxapp_id']) ? $this->store['wxapp_id'] : null;
        $this->configService = new WaybillConfigService($wxappId);
    }

    /**
     * 配置管理页面
     * @return mixed
     */
    public function index()
    {
        return $this->fetch('', [
            'expressTypes' => $this->getExpressTypes(),
        ]);
    }

    /**
     * 获取 API 配置
     * @return \think\response\Json
     */
    public function getApiConfig()
    {
        $config = SettingModel::getItem('waybill');
        
        // 确保 renderSuccess 将数据放在 data 字段中
        // 如果 $config 是空数组，renderSuccess 可能会将其视为 msg 或做特殊处理
        // 这里显式指定 data
        return $this->renderSuccess($config, '获取成功');
    }

    /**
     * 保存 API 配置
     * @return \think\response\Json
     */
    public function saveApiConfig()
    {
        // 获取原始数据，不使用默认过滤(htmlspecialchars)
        $config = $this->request->post('config', null, null);
        
        if ($config === null || $config === '') {
            return $this->renderError('参数错误：配置数据为空');
        }

        // 如果被转义了，尝试反转义 (兼容性处理)
        if (is_string($config) && strpos($config, '&quot;') !== false) {
            $config = htmlspecialchars_decode($config);
        }

        if (is_string($config)) {
            $decodedConfig = json_decode($config, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->renderError('JSON 解析失败：' . json_last_error_msg());
            }
            $config = $decodedConfig;
        }
        
        if (!is_array($config)) {
            return $this->renderError('配置数据格式错误：必须是数组，当前类型：' . gettype($config));
        }
        
        if (!isset($config['zhongtong']) || !isset($config['shunfeng'])) {
            return $this->renderError('配置数据不完整：缺少 zhongtong 或 shunfeng 配置');
        }

        try {
            $model = new StoreSettingModel();
            $result = $model->edit('waybill', $config);
            
            if ($result) {
                return $this->renderSuccess([], '保存成功');
            }
            
            $error = $model->getError();
            return $this->renderError($error ?: '保存失败');
            
        } catch (\Exception $e) {
            return $this->renderError('保存失败：' . $e->getMessage());
        }
    }

    /**
     * 获取配置
     * @return \think\response\Json
     */
    public function getConfig()
    {
        $expressType = $this->request->param('express_type', 'zhongtong');
        
        $config = $this->configService->getConfig($expressType);
        
        return $this->renderSuccess($config, '获取成功');
    }

    /**
     * 保存配置
     * @return \think\response\Json
     */
    public function saveConfig()
    {
        $expressType = $this->request->post('express_type');
        // 获取原始数据，不使用默认过滤(htmlspecialchars)
        $config = $this->request->post('config', null, null);
        
        if (empty($expressType) || empty($config)) {
            return $this->renderError('参数错误');
        }

        // 如果被转义了，尝试反转义 (兼容性处理)
        if (is_string($config) && strpos($config, '&quot;') !== false) {
            $config = htmlspecialchars_decode($config);
        }

        // 如果 config 是 JSON 字符串，解析它
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $result = $this->configService->saveConfig($expressType, $config);
        
        if ($result) {
            return $this->renderSuccess([], '保存成功');
        }
        
        return $this->renderError('保存失败');
    }

    /**
     * 获取字段列表
     * @return \think\response\Json
     */
    public function getFieldList()
    {
        $expressType = $this->request->param('express_type', 'zhongtong');
        
        $fields = $this->configService->getFieldDefinitions($expressType);
        
        return $this->renderSuccess($fields, '获取成功');
    }

    /**
     * 恢复默认配置
     * @return \think\response\Json
     */
    public function resetConfig()
    {
        $expressType = $this->request->post('express_type');
        
        if (empty($expressType)) {
            return $this->renderError('参数错误');
        }

        $defaultConfig = $this->configService->getDefaultConfig($expressType);
        $result = $this->configService->saveConfig($expressType, $defaultConfig);
        
        if ($result) {
            return $this->renderSuccess($defaultConfig, '已恢复默认配置');
        }
        
        return $this->renderError('恢复失败');
    }

    /**
     * 获取快递类型列表
     * @return array
     */
    private function getExpressTypes()
    {
        return [
            ['value' => 'zhongtong', 'label' => '中通快递'],
            ['value' => 'shunfeng', 'label' => '顺丰快递'],
        ];
    }
}
