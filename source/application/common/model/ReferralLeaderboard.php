<?php

namespace app\common\model;

/**
 * 推荐排行榜模型
 * Class ReferralLeaderboard
 * @package app\common\model
 */
class ReferralLeaderboard extends BaseModel
{
    protected $name = 'referral_leaderboard';
    protected $pk = 'id';

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }

    /**
     * 获取周期类型文本
     * @param $value
     * @return array
     */
    public function getPeriodTypeAttr($value)
    {
        $types = [
            'daily' => '日榜',
            'weekly' => '周榜',
            'monthly' => '月榜',
        ];

        return [
            'value' => $value,
            'text' => $types[$value] ?? '未知',
        ];
    }

    /**
     * 获取排行榜数据
     * @param string $periodType
     * @param string $periodDate
     * @param int $limit
     * @return array
     */
    public static function getLeaderboard($periodType, $periodDate, $limit = 100)
    {
        return self::where('period_type', $periodType)
            ->where('period_date', $periodDate)
            ->order('rank', 'asc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 获取用户排名
     * @param int $userId
     * @param string $periodType
     * @param string $periodDate
     * @return array|null
     */
    public static function getUserRank($userId, $periodType, $periodDate)
    {
        $record = self::where('user_id', $userId)
            ->where('period_type', $periodType)
            ->where('period_date', $periodDate)
            ->find();

        return $record ? $record->toArray() : null;
    }
}
