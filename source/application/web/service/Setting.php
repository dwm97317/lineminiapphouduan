<?php
declare (strict_types=1);

namespace app\web\service;

use app\common\library\helper;
use app\common\service\Basics;
use app\web\model\Setting as SettingModel;
use app\web\model\h5\Setting as H5SettingModel;
use app\common\enum\Setting as SettingEnum;

/**
 * 服务类：商城设置
 * Class Setting
 * @package app\web\service
 */
class Setting extends Basics
{
    /**
     * 商城公共设置
     * 这里的商城设置仅暴露可公开的设置项 例如分类页模板、积分名称
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPublic(): array
    {
        $data = [];
        //分类页模板设置
        $data[SettingEnum::PAGE_CATEGORY_TEMPLATE] = $this->getCatTplStyle();
        // 积分设置
        $data[SettingEnum::POINTS] = $this->getPoints();
        // 充值设置
        $data[SettingEnum::RECHARGE] = $this->getRecharge();
        // 注册设置
        $data[SettingEnum::REGISTER] = $this->getRegister();
        // 其他设置
        $data['_other'] = $this->getOtherSetting();
        return $data;
    }

    /**
     * 获取其他设置
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getOtherSetting(): array
    {
        // H5端访问地址
        $data['h5Url'] = H5SettingModel::getH5Url();
        return $data;
    }

    /**
     * 积分设置 (积分名称、积分描述)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getPoints(): array
    {
        $values = SettingModel::getItem(SettingEnum::POINTS);
        return helper::pick($values, ['points_name', 'describe']);
    }

    /**
     * 积分设置 (积分名称、积分描述)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getRecharge(): array
    {
        $values = SettingModel::getItem(SettingEnum::RECHARGE);
        return helper::pick($values, ['is_entrance', 'is_custom', 'describe']);
    }

    /**
     * 注册设置 (默认登录方式、是否开启微信小程序授权登录)
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getRegister(): array
    {
        $values = SettingModel::getItem(SettingEnum::REGISTER);
        return helper::pick($values, ['registerMethod', 'isOauthMpweixin', 'isManualBind']);
    }

    /**
     * 获取分类页模板设置
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getCatTplStyle()
    {
        return SettingModel::getItem(SettingEnum::PAGE_CATEGORY_TEMPLATE);
    }
}