<?php

namespace app\common\model;

/**
 * 推荐关系模型
 * Class ReferralRelation
 * @package app\common\model
 */
class ReferralRelation extends BaseModel
{
    protected $name = 'referral_relation';
    protected $pk = 'id';

    /**
     * 关联推荐人
     * @return \think\model\relation\BelongsTo
     */
    public function referrer()
    {
        return $this->belongsTo('User', 'referrer_user_id');
    }

    /**
     * 关联被推荐人
     * @return \think\model\relation\BelongsTo
     */
    public function referee()
    {
        return $this->belongsTo('User', 'referee_user_id');
    }

    /**
     * 关联上级推荐关系
     * @return \think\model\relation\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('ReferralRelation', 'parent_relation_id');
    }

    /**
     * 关联奖励记录
     * @return \think\model\relation\HasMany
     */
    public function rewards()
    {
        return $this->hasMany('ReferralReward', 'relation_id');
    }

    /**
     * 更新推荐人任务状态
     * @param int $status
     * @return bool
     */
    public function updateReferrerTaskStatus($status)
    {
        $data = [
            'referrer_task_status' => $status,
        ];

        if ($status == 1) {
            $data['referrer_task_complete_time'] = time();
        }

        return $this->save($data);
    }

    /**
     * 更新被推荐人任务状态
     * @param int $status
     * @return bool
     */
    public function updateRefereeTaskStatus($status)
    {
        $data = [
            'referee_task_status' => $status,
        ];

        if ($status == 1) {
            $data['referee_task_complete_time'] = time();
        }

        return $this->save($data);
    }

    /**
     * 检查双方任务是否都完成
     * @return bool
     */
    public function areBothTasksCompleted()
    {
        return $this->referrer_task_status == 1 && $this->referee_task_status == 1;
    }

    /**
     * 更新推荐关系状态
     * @param int $status
     * @return bool
     */
    public function updateStatus($status)
    {
        return $this->save(['status' => $status]);
    }

    /**
     * 标记奖励已发放
     * @return bool
     */
    public function markRewardIssued()
    {
        return $this->save([
            'reward_issued' => 1,
            'reward_issue_time' => time(),
        ]);
    }

    /**
     * 获取用户的推荐关系(作为推荐人)
     * @param int $userId
     * @return array
     */
    public static function getByReferrer($userId)
    {
        return self::where('referrer_user_id', $userId)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取用户的推荐关系(作为被推荐人)
     * @param int $userId
     * @return ReferralRelation|null
     */
    public static function getByReferee($userId)
    {
        return self::where('referee_user_id', $userId)->find();
    }

    /**
     * 查找待完成且超时的推荐关系
     * @param int $expireTime
     * @return array
     */
    public static function findExpired($expireTime)
    {
        return self::where('status', 1)
            ->where('expire_time', '<', $expireTime)
            ->select()
            ->toArray();
    }
}
