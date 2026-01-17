<?php

namespace app\common\model;

/**
 * 推荐奖励记录模型
 * Class ReferralReward
 * @package app\common\model
 */
class ReferralReward extends BaseModel
{
    protected $name = 'referral_reward';
    protected $pk = 'id';

    /**
     * 关联推荐关系
     * @return \think\model\relation\BelongsTo
     */
    public function relation()
    {
        return $this->belongsTo('ReferralRelation', 'relation_id');
    }

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
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
     * 获取状态文本
     * @param $value
     * @return array
     */
    public function getStatusAttr($value)
    {
        $statuses = [
            1 => '待发放',
            2 => '已发放',
            3 => '已回收',
        ];

        return [
            'value' => $value,
            'text' => $statuses[$value] ?? '未知',
        ];
    }

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
     * 获取用户的奖励记录
     * @param int $userId
     * @param int $status 状态筛选(0=全部)
     * @return array
     */
    public static function getByUser($userId, $status = 0)
    {
        $query = self::where('user_id', $userId);

        if ($status > 0) {
            $query->where('status', $status);
        }

        return $query->order('create_time', 'desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取推荐关系的奖励记录
     * @param int $relationId
     * @return array
     */
    public static function getByRelation($relationId)
    {
        return self::where('relation_id', $relationId)
            ->select()
            ->toArray();
    }

    /**
     * 统计用户的总奖励
     * @param int $userId
     * @return array
     */
    public static function getTotalRewards($userId)
    {
        $cash = self::where('user_id', $userId)
            ->where('reward_type', 1)
            ->where('status', 2)
            ->sum('reward_amount');

        $points = self::where('user_id', $userId)
            ->where('reward_type', 2)
            ->where('status', 2)
            ->sum('reward_amount');

        $coupons = self::where('user_id', $userId)
            ->where('reward_type', 3)
            ->where('status', 2)
            ->count();

        return [
            'cash' => $cash ?: 0,
            'points' => $points ?: 0,
            'coupons' => $coupons ?: 0,
        ];
    }
}
