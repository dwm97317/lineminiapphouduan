<?php
declare (strict_types=1);

namespace app\api\model\h5;

use app\common\model\h5\Setting as SettingModel;

/**
 * H5设置模型
 * Class Setting
 * @package app\api\model\h5
 */
class Setting extends SettingModel
{
    /**
     * 验证当前是否允许访问
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function checkStatus(): bool
    {
        return (bool)static::getItem('basic', static::$wxapp_id)['enabled'];
    }
}