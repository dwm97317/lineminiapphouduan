<?php

namespace app\common\model;

/**
 * 推荐奖励配置模型
 * Class ReferralRewardConfig
 * @package app\common\model
 */
class ReferralRewardConfig extends BaseModel
{
    protected $name = 'referral_reward_config';
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
     * 获取奖励类型文本
     * @param $value
     * @return array
     */
    public function getRewardTypeAttr($value)
    {
        $types = [
            1 => '现金',
            2 => '积分',
            3 => '优惠券',
        ];

        return [
            'value' => $value,
            'text' => $types[$value] ?? '未知',
        ];
    }

    /**
     * 获取启用的奖励配置
     * @param int $level
     * @return array
     */
    public static function getEnabledRewards($level = 1)
    {
        return self::where('level', $level)
            ->where('is_enabled', 1)
            ->select()
            ->toArray();
    }
}
