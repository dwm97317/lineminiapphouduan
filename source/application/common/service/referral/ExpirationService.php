<?php

namespace app\common\service\referral;

use app\common\model\ReferralRelation;
use app\common\model\ReferralSystemConfig;
use think\Db;
use think\Exception;

/**
 * 推荐关系失效服务
 * Class ExpirationService
 * @package app\common\service\referral
 */
class ExpirationService
{
    /**
     * 检查并处理失效的推荐关系
     * @return array 返回处理结果统计
     */
    public function checkExpiredRelations()
    {
        $stats = [
            'total' => 0,
            'expired' => 0,
            'recycled' => 0,
            'errors' => 0,
        ];

        try {
            // 1. 查找待完成且超时的推荐关系
            $expiredRelations = ReferralRelation::where('status', 1)
                ->where('expire_time', '<', time())
                ->select();

            $stats['total'] = count($expiredRelations);

            foreach ($expiredRelations as $relation) {
                try {
                    Db::startTrans();

                    // 2. 更新状态为已失效
                    $relation->updateStatus(3);
                    $stats['expired']++;

                    // 3. 如果有已发放的奖励,根据配置决定是否回收
                    if ($relation['reward_issued'] && $this->shouldRecycleReward()) {
                        $rewardService = new RewardService();
                        $rewardService->recycleRewards($relation['id'], '推荐关系失效');
                        $stats['recycled']++;
                    }

                    // 4. 发送通知
                    $this->sendExpirationNotification($relation);

                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $stats['errors']++;
                    \think\Log::error('Expiration processing failed: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \think\Log::error('Check expired relations failed: ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * 是否应该回收奖励
     * @return bool
     */
    private function shouldRecycleReward()
    {
        $config = ReferralSystemConfig::where('config_key', 'recycle_reward_on_expire')
            ->where('is_enabled', 1)
            ->find();

        if (!$config) {
            return false; // 默认不回收
        }

        return $config['config_value'] == '1' || $config['config_value'] == 'true';
    }

    /**
     * 发送失效通知
     * @param ReferralRelation $relation
     * @return void
     */
    private function sendExpirationNotification($relation)
    {
        // TODO: 集成LINE通知系统
        // 通知推荐人和被推荐人推荐关系已失效
    }

    /**
     * 手动使推荐关系失效
     * @param int $relationId
     * @param string $reason
     * @return void
     * @throws Exception
     */
    public function invalidateRelation($relationId, $reason = '管理员操作')
    {
        $relation = ReferralRelation::get($relationId);
        if (!$relation) {
            throw new Exception('推荐关系不存在');
        }

        if ($relation['status'] != 1) {
            throw new Exception('只能使待完成的推荐关系失效');
        }

        Db::startTrans();
        try {
            // 更新状态
            $relation->updateStatus(3);

            // 回收奖励
            if ($relation['reward_issued']) {
                $rewardService = new RewardService();
                $rewardService->recycleRewards($relationId, $reason);
            }

            // 发送通知
            $this->sendExpirationNotification($relation);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('使推荐关系失效失败: ' . $e->getMessage());
        }
    }

    /**
     * 检查单个推荐关系是否失效
     * @param int $relationId
     * @return bool
     */
    public function isExpired($relationId)
    {
        $relation = ReferralRelation::get($relationId);
        if (!$relation) {
            return false;
        }

        return $relation['status'] == 1 && $relation['expire_time'] < time();
    }
}
