<?php

namespace app\api\controller;

use app\api\model\LineApp as LineAppModel;
use app\api\model\WxappHelp;

/**
 * LINE小程序
 * Class LineApp
 * @package app\api\controller
 */
class LineApp extends Controller
{
    /**
     * 小程序基础信息
     * @return array
     */
    public function base()
    {
        // 获取 LINE 配置
        $config = \app\api\model\Setting::getItem('line_config');
        $payConfig = \app\api\model\Setting::getItem('line_pay');
        return $this->renderSuccess([
            'config' => [
                'is_enable' => (int)$config['is_enable'],
                'liff_id' => (string)$config['liff_id'],
                'liff_size' => (string)$config['liff_size'],
                'scopes' => $config['scopes'] ?? [],
                'bot_link' => $config['bot_link'] ?? 'Off',
                'google_maps_key' => (string)($config['google_maps_key'] ?? ''),
                'pay_is_enable' => (int)$payConfig['is_enable']
            ]
        ]);
    }




    /**
     * 帮助中心
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function help()
    {
        $model = new WxappHelp;
        $list = $model->getList();
        return $this->renderSuccess(compact('list'));
    }

    /**

     * 逆地理编码
     * @return array
     */
    public function parseAddress()
    {
        $params = $this->postData();
        if (!isset($params['lat']) || !isset($params['lng'])) {
            return $this->renderError('缺失坐标参数');
        }

        $service = new \app\common\service\maps\GoogleMaps();
        $result = $service->reverseGeocoding($params['lat'], $params['lng']);

        if (!$result) {
            return $this->renderError('无法解析该地址');
        }

        return $this->renderSuccess($result);
    }

}

