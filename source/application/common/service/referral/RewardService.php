<?php

namespace app\common\service\referral;

use app\common\model\ReferralRelation;
use app\common\model\ReferralReward;
use app\common\model\ReferralRewardConfig;
use app\common\model\UserReferralCode;
use app\common\model\User;
use think\Db;
use think\Exception;

/**
 * 推荐奖励发放服务
 * Class RewardService
 * @package app\common\service\referral
 */
class RewardService
{
    /**
     * 发放推荐奖励
     * @param int $relationId 推荐关系ID
     * @return void
     * @throws Exception
     */
    public function issueRewards($relationId)
    {
        $relation = ReferralRelation::get($relationId);
        if (!$relation) {
            throw new Exception('推荐关系不存在');
        }

        // 检查是否已发放
        if ($relation['reward_issued']) {
            return;
        }

        // 获取奖励配置
        $configs = $this->getRewardConfigs($relation['level']);

        if (empty($configs)) {
            return;
        }

        Db::startTrans();
        try {
            foreach ($configs as $config) {
                // 计算实际奖励金额(考虑多级比例)
                $actualAmount = $config['reward_amount'] * ($config['reward_ratio'] / 100);

                // 确定接收奖励的用户
                $userId = ($config['user_type'] == 1)
                    ? $relation['referrer_user_id']
                    : $relation['referee_user_id'];

                // 创建奖励记录
                $rewardId = $this->createRewardRecord([
                    'relation_id' => $relationId,
                    'user_id' => $userId,
                    'user_type' => $config['user_type'],
                    'reward_type' => $config['reward_type'],
                    'reward_amount' => $actualAmount,
                    'coupon_id' => $config['reward_type'] == 3 ? $config['reward_amount'] : null,
                    'expire_time' => $this->calculateExpireTime($config['expire_days']),
                ]);

                // 发放奖励
                $this->distributeReward($rewardId, $config['reward_type'], $userId, $actualAmount);

                // 发送通知
                $this->sendNotification($userId, $config['reward_type'], $actualAmount);
            }

            // 更新推荐关系
            $relation->markRewardIssued();

            // 更新推荐码统计
            $referrerCode = UserReferralCode::where('user_id', $relation['referrer_user_id'])->find();
            if ($referrerCode) {
                $referrerCode->incrementSuccessCount();
                $referrerCode->incrementTotalReward($actualAmount);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('奖励发放失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取奖励配置
     * @param int $level 推荐级别
     * @return array
     */
    private function getRewardConfigs($level)
    {
        return ReferralRewardConfig::where('level', $level)
            ->where('is_enabled', 1)
            ->select()
            ->toArray();
    }

    /**
     * 创建奖励记录
     * @param array $data
     * @return int 奖励记录ID
     */
    private function createRewardRecord($data)
    {
        $reward = ReferralReward::create(array_merge($data, [
            'status' => 1, // 待发放
            'wxapp_id' => UserReferralCode::$wxapp_id,
        ]));

        return $reward['id'];
    }

    /**
     * 分发奖励到用户账户
     * @param int $rewardId
     * @param int $rewardType 奖励类型(1=现金,2=积分,3=优惠券)
     * @param int $userId
     * @param float $amount
     * @return void
     */
    private function distributeReward($rewardId, $rewardType, $userId, $amount)
    {
        $user = User::get($userId);
        if (!$user) {
            throw new Exception('用户不存在');
        }

        switch ($rewardType) {
            case 1: // 现金
                $this->addBalance($user, $amount);
                break;
            case 2: // 积分
                $this->addPoints($user, (int)$amount);
                break;
            case 3: // 优惠券
                $this->issueCoupon($userId, (int)$amount);
                break;
        }

        // 更新奖励状态
        $this->updateRewardStatus($rewardId, 2); // 已发放
    }

    /**
     * 增加用户余额
     * @param User $user
     * @param float $amount
     * @return void
     */
    private function addBalance($user, $amount)
    {
        $user->banlanceUpdate('add', $user['user_id'], $amount, '推荐奖励');
    }

    /**
     * 增加用户积分
     * @param User $user
     * @param int $points
     * @return void
     */
    private function addPoints($user, $points)
    {
        $user->setIncPoints($points, '推荐奖励');
    }

    /**
     * 发放优惠券
     * @param int $userId
     * @param int $couponId
     * @return void
     */
    private function issueCoupon($userId, $couponId)
    {
        // TODO: 集成优惠券系统
        // 调用优惠券发放接口
    }

    /**
     * 更新奖励状态
     * @param int $rewardId
     * @param int $status
     * @param string|null $reason
     * @return void
     */
    private function updateRewardStatus($rewardId, $status, $reason = null)
    {
        $reward = ReferralReward::get($rewardId);
        if (!$reward) {
            return;
        }

        $data = ['status' => $status];

        if ($status == 2) {
            $data['issue_time'] = time();
        } elseif ($status == 3) {
            $data['recycle_time'] = time();
            $data['recycle_reason'] = $reason;
        }

        $reward->save($data);
    }

    /**
     * 计算过期时间
     * @param int|null $expireDays
     * @return int|null
     */
    private function calculateExpireTime($expireDays)
    {
        if ($expireDays === null) {
            return null; // 永久有效
        }

        return time() + ($expireDays * 86400);
    }

    /**
     * 发送通知
     * @param int $userId
     * @param int $rewardType
     * @param float $amount
     * @return void
     */
    private function sendNotification($userId, $rewardType, $amount)
    {
        // TODO: 集成LINE通知系统
        // 发送推荐奖励通知
    }

    /**
     * 回收奖励
     * @param int $relationId
     * @param string $reason
     * @return void
     * @throws Exception
     */
    public function recycleRewards($relationId, $reason)
    {
        $rewards = ReferralReward::where('relation_id', $relationId)
            ->where('status', 2) // 只回收已发放的
            ->select();

        if ($rewards->isEmpty()) {
            return;
        }

        Db::startTrans();
        try {
            foreach ($rewards as $reward) {
                // 从用户账户扣除
                $this->deductReward($reward, $reason);

                // 更新奖励状态
                $this->updateRewardStatus($reward['id'], 3, $reason); // 已回收
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('奖励回收失败: ' . $e->getMessage());
        }
    }

    /**
     * 扣除奖励
     * @param ReferralReward $reward
     * @param string $reason
     * @return void
     */
    private function deductReward($reward, $reason)
    {
        $user = User::get($reward['user_id']);
        if (!$user) {
            return;
        }

        switch ($reward['reward_type']) {
            case 1: // 现金
                $user->banlanceUpdate('remove', $user['user_id'], $reward['reward_amount'], $reason);
                break;
            case 2: // 积分
                // TODO: 扣除积分
                break;
            case 3: // 优惠券
                // TODO: 撤销优惠券
                break;
        }
    }
}
