<?php

namespace app\common\service\referral;

use app\common\model\ReferralRelation;
use app\common\model\ReferralLeaderboard;
use app\common\model\ReferralSystemConfig;
use app\common\model\User;
use think\Db;

/**
 * 推荐排行榜服务
 * Class LeaderboardService
 * @package app\common\service\referral
 */
class LeaderboardService
{
    /**
     * 更新排行榜数据
     * @param string $periodType 周期类型(daily/weekly/monthly)
     * @param string|null $periodDate 周期日期(格式: Y-m-d 或 Y-m)
     * @return array 返回更新统计
     */
    public function updateLeaderboard($periodType = 'monthly', $periodDate = null)
    {
        // 检查排行榜是否启用
        if (!$this->isLeaderboardEnabled()) {
            return ['enabled' => false];
        }

        // 确定周期日期
        if (!$periodDate) {
            $periodDate = $this->getCurrentPeriodDate($periodType);
        }

        // 获取时间范围
        list($startTime, $endTime) = $this->getPeriodTimeRange($periodType, $periodDate);

        // 统计推荐数据
        $stats = $this->calculateReferralStats($startTime, $endTime);

        // 计算排名
        $rankedStats = $this->calculateRanks($stats);

        // 保存到数据库
        $this->saveLeaderboard($periodType, $periodDate, $rankedStats);

        return [
            'enabled' => true,
            'period_type' => $periodType,
            'period_date' => $periodDate,
            'total_users' => count($rankedStats),
        ];
    }

    /**
     * 检查排行榜是否启用
     * @return bool
     */
    private function isLeaderboardEnabled()
    {
        $config = ReferralSystemConfig::where('config_key', 'leaderboard_enabled')
            ->where('is_enabled', 1)
            ->find();

        if (!$config) {
            return false;
        }

        return $config['config_value'] == '1' || $config['config_value'] == 'true';
    }

    /**
     * 获取当前周期日期
     * @param string $periodType
     * @return string
     */
    private function getCurrentPeriodDate($periodType)
    {
        switch ($periodType) {
            case 'daily':
                return date('Y-m-d');
            case 'weekly':
                return date('Y-m-d', strtotime('monday this week'));
            case 'monthly':
            default:
                return date('Y-m');
        }
    }

    /**
     * 获取周期时间范围
     * @param string $periodType
     * @param string $periodDate
     * @return array [startTime, endTime]
     */
    private function getPeriodTimeRange($periodType, $periodDate)
    {
        switch ($periodType) {
            case 'daily':
                $startTime = strtotime($periodDate . ' 00:00:00');
                $endTime = strtotime($periodDate . ' 23:59:59');
                break;
            case 'weekly':
                $startTime = strtotime($periodDate . ' 00:00:00');
                $endTime = strtotime($periodDate . ' +6 days 23:59:59');
                break;
            case 'monthly':
            default:
                $startTime = strtotime($periodDate . '-01 00:00:00');
                $endTime = strtotime($periodDate . '-01 +1 month -1 second');
                break;
        }

        return [$startTime, $endTime];
    }

    /**
     * 计算推荐统计数据
     * @param int $startTime
     * @param int $endTime
     * @return array
     */
    private function calculateReferralStats($startTime, $endTime)
    {
        $sql = "
            SELECT 
                referrer_user_id as user_id,
                COUNT(*) as referral_count,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as success_count
            FROM yoshop_referral_relation
            WHERE create_time >= {$startTime}
            AND create_time <= {$endTime}
            AND level = 1
            GROUP BY referrer_user_id
            ORDER BY success_count DESC, referral_count DESC
        ";

        return Db::query($sql);
    }

    /**
     * 计算排名
     * @param array $stats
     * @return array
     */
    private function calculateRanks($stats)
    {
        $rank = 1;
        foreach ($stats as $key => &$item) {
            $item['rank'] = $rank++;
        }

        return $stats;
    }

    /**
     * 保存排行榜数据
     * @param string $periodType
     * @param string $periodDate
     * @param array $rankedStats
     * @return void
     */
    private function saveLeaderboard($periodType, $periodDate, $rankedStats)
    {
        // 获取排行榜显示人数配置
        $topCount = $this->getTopCount();

        // 只保存前N名
        $topStats = array_slice($rankedStats, 0, $topCount);

        Db::startTrans();
        try {
            // 删除旧数据
            ReferralLeaderboard::where('period_type', $periodType)
                ->where('period_date', $periodDate)
                ->delete();

            // 插入新数据
            foreach ($topStats as $stat) {
                ReferralLeaderboard::create([
                    'period_type' => $periodType,
                    'period_date' => $periodDate,
                    'user_id' => $stat['user_id'],
                    'referral_count' => $stat['referral_count'],
                    'success_count' => $stat['success_count'],
                    'rank' => $stat['rank'],
                    'reward_amount' => 0, // 排行榜奖励暂未实现
                    'reward_issued' => 0,
                    'wxapp_id' => ReferralRelation::$wxapp_id,
                ]);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            \think\Log::error('Save leaderboard failed: ' . $e->getMessage());
        }
    }

    /**
     * 获取排行榜显示人数配置
     * @return int
     */
    private function getTopCount()
    {
        $config = ReferralSystemConfig::where('config_key', 'leaderboard_top_count')
            ->where('is_enabled', 1)
            ->find();

        if (!$config) {
            return 100; // 默认100名
        }

        return (int)$config['config_value'];
    }

    /**
     * 获取排行榜数据
     * @param string $periodType
     * @param string|null $periodDate
     * @param int $userId 当前用户ID
     * @return array
     */
    public function getLeaderboard($periodType = 'monthly', $periodDate = null, $userId = 0)
    {
        if (!$periodDate) {
            $periodDate = $this->getCurrentPeriodDate($periodType);
        }

        // 获取排行榜列表
        $list = ReferralLeaderboard::where('period_type', $periodType)
            ->where('period_date', $periodDate)
            ->with(['user'])
            ->order('rank', 'asc')
            ->select()
            ->toArray();

        // 获取当前用户排名
        $myRank = 0;
        $myCount = 0;
        if ($userId > 0) {
            $myData = ReferralLeaderboard::where('period_type', $periodType)
                ->where('period_date', $periodDate)
                ->where('user_id', $userId)
                ->find();

            if ($myData) {
                $myRank = $myData['rank'];
                $myCount = $myData['success_count'];
            }
        }

        return [
            'period_type' => $periodType,
            'period_date' => $periodDate,
            'my_rank' => $myRank,
            'my_count' => $myCount,
            'list' => $list,
        ];
    }
}
