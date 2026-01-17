<?php

namespace app\common\model;

/**
 * 推荐任务配置模型
 * Class ReferralTaskConfig
 * @package app\common\model
 */
class ReferralTaskConfig extends BaseModel
{
    protected $name = 'referral_task_config';
    protected $pk = 'id';

    /**
     * 获取用户类型文本
     * @param $value
     * @return array
     */
    public function getUserTypeAttr($value)
    {
        $types = [
            1 => '推荐人',
            2 => '被推荐人',
        ];

        return [
            'value' => $value,
            'text' => $types[$value] ?? '未知',
        ];
    }

    /**
     * 获取任务类型文本
     * @param $value
     * @return array
     */
    public function getTaskTypeAttr($value)
    {
        $types = [
            'register' => '完成注册',
            'first_recharge' => '首次充值',
            'first_order' => '首次下单',
            'real_name' => '实名认证',
        ];

        return [
            'value' => $value,
            'text' => $types[$value] ?? $value,
        ];
    }

    /**
     * 获取启用的任务配置
     * @param int $userType
     * @return array
     */
    public static function getEnabledTasks($userType)
    {
        return self::where('user_type', $userType)
            ->where('is_enabled', 1)
            ->order('sort_order', 'asc')
            ->select()
            ->toArray();
    }
}
