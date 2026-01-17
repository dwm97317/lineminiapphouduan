<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\common\service\WaybillConfigService;
use app\common\model\Setting as SettingModel;

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
        $config = SettingModel::getItem('express_api_config');
        
        if (empty($config)) {
            // 返回默认结构
            $config = [
                'zhongtong' => [
                    'api_url' => '',
                    'api_key' => '',
                    'api_secret' => '',
                    'company_code' => 'ZTO'
                ],
                'shunfeng' => [
                    'api_url' => '',
                    'api_key' => '',
                    'api_secret' => '',
                    'company_code' => 'SF'
                ]
            ];
        }
        
        return $this->renderSuccess($config, '获取成功');
    }

    /**
     * 保存 API 配置
     * @return \think\response\Json
     */
    public function saveApiConfig()
    {
        $config = $this->request->post('config');
        
        if (empty($config)) {
            return $this->renderError('参数错误');
        }

        // 如果 config 是 JSON 字符串，解析它
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $result = SettingModel::edit('express_api_config', $config, '快递API配置');
        
        if ($result) {
            return $this->renderSuccess([], '保存成功');
        }
        
        return $this->renderError('保存失败');
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
        $config = $this->request->post('config');
        
        if (empty($expressType) || empty($config)) {
            return $this->renderError('参数错误');
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
