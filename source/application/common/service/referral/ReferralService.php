<?php

namespace app\common\service\referral;

use app\common\model\UserReferralCode;
use app\common\model\ReferralRelation;
use app\common\model\ReferralSystemConfig;
use think\Db;
use think\Exception;

/**
 * 推荐关系服务
 * Class ReferralService
 * @package app\common\service\referral
 */
class ReferralService
{
    /**
     * 建立推荐关系(支持多级)
     * @param int $refereeUserId 被推荐人用户ID
     * @param string $referralCode 推荐码
     * @return array 返回创建的推荐关系数组
     * @throws Exception
     */
    public function createRelation($refereeUserId, $referralCode)
    {
        // 1. 验证推荐码
        $referrerCode = $this->validateCode($referralCode);
        if (!$referrerCode) {
            throw new Exception('推荐码无效');
        }

        $referrerUserId = $referrerCode['user_id'];

        // 2. 防止自己推荐自己
        if ($referrerUserId == $refereeUserId) {
            throw new Exception('不能使用自己的推荐码');
        }

        // 3. 检查是否已有推荐关系
        if ($this->hasRelation($refereeUserId)) {
            throw new Exception('您已经有推荐人了');
        }

        // 4. 获取最大推荐级数配置
        $maxLevels = $this->getConfig('max_referral_levels', 1);

        // 5. 获取失效天数配置
        $expireDays = $this->getConfig('expire_days', 30);
        $expireTime = time() + ($expireDays * 86400);

        // 使用事务确保数据一致性
        Db::startTrans();
        try {
            $relations = [];

            // 6. 创建一级推荐关系
            $relations[] = $this->createRelationRecord(
                $referrerUserId,
                $refereeUserId,
                $referralCode,
                1,
                null,
                $expireTime
            );

            // 7. 创建多级推荐关系
            if ($maxLevels > 1) {
                $currentReferrer = $referrerUserId;

                for ($level = 2; $level <= $maxLevels; $level++) {
                    // 查找上级推荐人
                    $parentRelation = $this->findParentRelation($currentReferrer);

                    if (!$parentRelation) {
                        break; // 没有更上级了
                    }

                    $relations[] = $this->createRelationRecord(
                        $parentRelation['referrer_user_id'],
                        $refereeUserId,
                        $referralCode,
                        $level,
                        $parentRelation['id'],
                        $expireTime
                    );

                    $currentReferrer = $parentRelation['referrer_user_id'];
                }
            }

            // 8. 更新推荐码统计
            $referrerCode->incrementRegisterCount();

            Db::commit();
            return $relations;
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('建立推荐关系失败: ' . $e->getMessage());
        }
    }

    /**
     * 验证推荐码
     * @param string $code
     * @return UserReferralCode|null
     */
    private function validateCode($code)
    {
        return UserReferralCode::findByCode($code);
    }

    /**
     * 检查用户是否已有推荐关系
     * @param int $userId
     * @return bool
     */
    private function hasRelation($userId)
    {
        return ReferralRelation::where('referee_user_id', $userId)->count() > 0;
    }

    /**
     * 创建推荐关系记录
     * @param int $referrerUserId 推荐人ID
     * @param int $refereeUserId 被推荐人ID
     * @param string $referralCode 推荐码
     * @param int $level 推荐级别
     * @param int|null $parentRelationId 上级推荐关系ID
     * @param int $expireTime 失效时间
     * @return array
     */
    private function createRelationRecord($referrerUserId, $refereeUserId, $referralCode, $level, $parentRelationId, $expireTime)
    {
        $relation = ReferralRelation::create([
            'referrer_user_id' => $referrerUserId,
            'referee_user_id' => $refereeUserId,
            'referral_code' => $referralCode,
            'level' => $level,
            'parent_relation_id' => $parentRelationId,
            'status' => 1, // 待完成
            'referrer_task_status' => 0,
            'referee_task_status' => 0,
            'reward_issued' => 0,
            'expire_time' => $expireTime,
            'wxapp_id' => UserReferralCode::$wxapp_id,
        ]);

        return $relation->toArray();
    }

    /**
     * 查找上级推荐关系
     * @param int $userId
     * @return array|null
     */
    private function findParentRelation($userId)
    {
        $relation = ReferralRelation::where('referee_user_id', $userId)
            ->where('level', 1)
            ->find();

        return $relation ? $relation->toArray() : null;
    }

    /**
     * 获取系统配置
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getConfig($key, $default = null)
    {
        $config = ReferralSystemConfig::where('config_key', $key)->find();
        
        if (!$config || !$config['is_enabled']) {
            return $default;
        }

        // 根据配置类型转换值
        switch ($config['config_type']) {
            case 'int':
                return (int)$config['config_value'];
            case 'float':
                return (float)$config['config_value'];
            case 'json':
                return json_decode($config['config_value'], true);
            default:
                return $config['config_value'];
        }
    }

    /**
     * 获取用户的推荐关系列表
     * @param int $userId
     * @param int $status 状态筛选(0=全部,1=待完成,2=已完成,3=已失效)
     * @return array
     */
    public function getUserReferrals($userId, $status = 0)
    {
        $query = ReferralRelation::where('referrer_user_id', $userId);

        if ($status > 0) {
            $query->where('status', $status);
        }

        return $query->with(['referee'])
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取推荐统计
     * @param int $userId
     * @return array
     */
    public function getStatistics($userId)
    {
        $total = ReferralRelation::where('referrer_user_id', $userId)->count();
        $pending = ReferralRelation::where('referrer_user_id', $userId)->where('status', 1)->count();
        $completed = ReferralRelation::where('referrer_user_id', $userId)->where('status', 2)->count();
        $expired = ReferralRelation::where('referrer_user_id', $userId)->where('status', 3)->count();

        return [
            'total_referrals' => $total,
            'pending_referrals' => $pending,
            'completed_referrals' => $completed,
            'expired_referrals' => $expired,
        ];
    }
}
