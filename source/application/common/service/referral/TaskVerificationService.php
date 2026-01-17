<?php

namespace app\common\service\referral;

use app\common\model\ReferralRelation;
use app\common\model\ReferralTaskConfig;
use think\Db;
use think\Exception;

/**
 * 推荐任务验证服务
 * Class TaskVerificationService
 * @package app\common\service\referral
 */
class TaskVerificationService
{
    /**
     * 验证任务完成并发放奖励
     * @param int $userId 用户ID
     * @param string $taskType 任务类型(register/first_recharge/first_order/real_name等)
     * @param array $taskData 任务数据(如充值金额、订单金额等)
     * @return void
     * @throws Exception
     */
    public function verifyAndReward($userId, $taskType, $taskData = [])
    {
        // 1. 查找该用户相关的推荐关系
        $relations = $this->findRelationsByUser($userId);

        if (empty($relations)) {
            return; // 没有推荐关系，无需处理
        }

        foreach ($relations as $relation) {
            try {
                // 2. 判断用户类型(推荐人还是被推荐人)
                $userType = ($relation['referrer_user_id'] == $userId) ? 1 : 2;

                // 3. 检查任务是否匹配
                if (!$this->isTaskMatch($relation['id'], $userType, $taskType, $taskData)) {
                    continue;
                }

                // 4. 更新任务状态
                $this->updateTaskStatus($relation['id'], $userType);

                // 5. 检查双方任务是否都完成
                $relationModel = ReferralRelation::get($relation['id']);
                if ($relationModel && $relationModel->areBothTasksCompleted()) {
                    // 6. 发放奖励
                    $rewardService = new RewardService();
                    $rewardService->issueRewards($relation['id']);

                    // 7. 更新推荐关系状态
                    $relationModel->updateStatus(2); // 已完成
                }
            } catch (\Exception $e) {
                // 记录错误但继续处理其他关系
                \think\Log::error('Task verification failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * 查找用户相关的推荐关系
     * @param int $userId
     * @return array
     */
    private function findRelationsByUser($userId)
    {
        // 查找作为推荐人的关系
        $asReferrer = ReferralRelation::where('referrer_user_id', $userId)
            ->where('status', 1) // 只处理待完成的
            ->select()
            ->toArray();

        // 查找作为被推荐人的关系
        $asReferee = ReferralRelation::where('referee_user_id', $userId)
            ->where('status', 1)
            ->select()
            ->toArray();

        return array_merge($asReferrer, $asReferee);
    }

    /**
     * 检查任务是否匹配
     * @param int $relationId
     * @param int $userType 用户类型(1=推荐人,2=被推荐人)
     * @param string $taskType 任务类型
     * @param array $taskData 任务数据
     * @return bool
     */
    private function isTaskMatch($relationId, $userType, $taskType, $taskData)
    {
        // 获取该用户类型的任务配置
        $taskConfigs = ReferralTaskConfig::where('user_type', $userType)
            ->where('task_type', $taskType)
            ->where('is_enabled', 1)
            ->select()
            ->toArray();

        if (empty($taskConfigs)) {
            return false;
        }

        // 检查任务参数是否满足
        foreach ($taskConfigs as $config) {
            if ($this->checkTaskParams($config, $taskData)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查任务参数是否满足
     * @param array $config 任务配置
     * @param array $taskData 任务数据
     * @return bool
     */
    private function checkTaskParams($config, $taskData)
    {
        if (empty($config['task_params'])) {
            return true; // 没有参数要求，直接通过
        }

        $params = json_decode($config['task_params'], true);
        if (!$params) {
            return true;
        }

        // 检查最低金额要求
        if (isset($params['min_amount']) && isset($taskData['amount'])) {
            if ($taskData['amount'] < $params['min_amount']) {
                return false;
            }
        }

        // 检查最低订单数要求
        if (isset($params['min_orders']) && isset($taskData['order_count'])) {
            if ($taskData['order_count'] < $params['min_orders']) {
                return false;
            }
        }

        return true;
    }

    /**
     * 更新任务状态
     * @param int $relationId
     * @param int $userType
     * @return void
     */
    private function updateTaskStatus($relationId, $userType)
    {
        $relation = ReferralRelation::get($relationId);
        if (!$relation) {
            return;
        }

        if ($userType == 1) {
            // 推荐人任务
            $relation->updateReferrerTaskStatus(1);
        } else {
            // 被推荐人任务
            $relation->updateRefereeTaskStatus(1);
        }
    }

    /**
     * 用户注册时触发
     * @param int $userId
     * @return void
     */
    public function onUserRegister($userId)
    {
        $this->verifyAndReward($userId, 'register');
    }

    /**
     * 用户首次充值时触发
     * @param int $userId
     * @param float $amount
     * @return void
     */
    public function onFirstRecharge($userId, $amount)
    {
        $this->verifyAndReward($userId, 'first_recharge', ['amount' => $amount]);
    }

    /**
     * 用户首次下单时触发
     * @param int $userId
     * @param float $amount
     * @return void
     */
    public function onFirstOrder($userId, $amount)
    {
        $this->verifyAndReward($userId, 'first_order', ['amount' => $amount]);
    }

    /**
     * 用户实名认证时触发
     * @param int $userId
     * @return void
     */
    public function onRealNameAuth($userId)
    {
        $this->verifyAndReward($userId, 'real_name');
    }
}
