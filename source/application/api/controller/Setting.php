<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\service\Setting as SettingService;

/**
 * 商城设置控制器
 * Class Setting
 * @package app\store\controller
 */
class Setting extends Controller
{
    /**
     * 商城公共设置(仅展示可公开的信息)
     * @return array|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function data()
    {
        $service = new SettingService;
        $setting = $service->getPublic();
        return $this->renderSuccess(compact('setting'));
    }
}
