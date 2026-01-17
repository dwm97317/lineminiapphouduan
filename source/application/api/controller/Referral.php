<?php

namespace app\api\controller;

use app\common\model\UserReferralCode;
use app\common\model\ReferralRelation;
use app\common\model\ReferralReward;
use app\common\service\referral\ReferralService;
use app\common\service\referral\LeaderboardService;
use app\common\library\referral\ReferralCodeGenerator;

/**
 * 推荐奖励API控制器
 * Class Referral
 * @package app\api\controller
 */
class Referral extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 用户信息
        $this->user = $this->getUser();
    }

    /**
     * 获取/生成推荐码
     * GET /api/referral/code
     * @return array
     */
    public function code()
    {
        try {
            // 获取或创建推荐码
            $codeModel = UserReferralCode::getOrCreate($this->user['user_id']);

            // 生成分享链接
            $shareUrl = $this->generateShareUrl($codeModel['referral_code']);

            // 生成二维码URL
            $qrCodeUrl = $this->generateQrCodeUrl($codeModel['referral_code']);

            // 获取统计信息
            $statistics = $codeModel->getStatistics();

            return $this->renderSuccess([
                'referral_code' => $codeModel['referral_code'],
                'share_url' => $shareUrl,
                'qr_code_url' => $qrCodeUrl,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 验证推荐码
     * POST /api/referral/validateCode
     * @return array
     */
    public function validateCode()
    {
        $referralCode = $this->request->post('referral_code', '');

        if (empty($referralCode)) {
            return $this->renderError('请输入推荐码');
        }

        try {
            // 验证推荐码格式
            if (!ReferralCodeGenerator::validate($referralCode)) {
                return $this->renderError('推荐码格式不正确');
            }

            // 查找推荐码
            $codeModel = UserReferralCode::findByCode($referralCode);

            if (!$codeModel) {
                return $this->renderError('推荐码不存在');
            }

            // 获取推荐人信息
            $referrer = \app\common\model\User::get($codeModel['user_id']);

            return $this->renderSuccess([
                'is_valid' => true,
                'referrer_info' => [
                    'nickname' => $referrer['nickName'] ?? '',
                    'avatar' => $referrer['avatarUrl'] ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 建立推荐关系
     * POST /api/referral/bind
     * @return array
     */
    public function bind()
    {
        $referralCode = $this->request->post('referral_code', '');

        if (empty($referralCode)) {
            return $this->renderError('请输入推荐码');
        }

        try {
            $referralService = new ReferralService();
            $relations = $referralService->createRelation($this->user['user_id'], $referralCode);

            // 获取推荐人信息
            $referrer = \app\common\model\User::get($relations[0]['referrer_user_id']);

            // 获取任务配置
            $tasks = $this->getTasksInfo($relations[0]['id']);

            return $this->renderSuccess([
                'relation_id' => $relations[0]['id'],
                'referrer_info' => [
                    'nickname' => $referrer['nickName'] ?? '',
                ],
                'tasks' => $tasks,
            ], '推荐关系建立成功');
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 查询推荐记录列表
     * GET /api/referral/list
     * @return array
     */
    public function lists()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        $status = $this->request->get('status', 'all');

        try {
            // 状态映射
            $statusMap = [
                'all' => 0,
                'pending' => 1,
                'completed' => 2,
                'expired' => 3,
            ];

            $statusValue = $statusMap[$status] ?? 0;

            // 查询推荐关系
            $query = ReferralRelation::where('referrer_user_id', $this->user['user_id']);

            if ($statusValue > 0) {
                $query->where('status', $statusValue);
            }

            $total = $query->count();
            $list = $query->with(['referee', 'rewards'])
                ->order('create_time', 'desc')
                ->page($page, $limit)
                ->select();

            // 格式化数据
            $formattedList = [];
            foreach ($list as $item) {
                $formattedList[] = $this->formatReferralItem($item);
            }

            return $this->renderSuccess([
                'list' => $formattedList,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 查询推荐统计
     * GET /api/referral/statistics
     * @return array
     */
    public function statistics()
    {
        try {
            $referralService = new ReferralService();
            $stats = $referralService->getStatistics($this->user['user_id']);

            // 获取总奖励
            $totalRewards = ReferralReward::getTotalRewards($this->user['user_id']);

            // 获取各级别统计
            $levelStats = $this->getLevelStatistics($this->user['user_id']);

            return $this->renderSuccess([
                'total_referrals' => $stats['total_referrals'],
                'pending_referrals' => $stats['pending_referrals'],
                'completed_referrals' => $stats['completed_referrals'],
                'expired_referrals' => $stats['expired_referrals'],
                'total_rewards' => $totalRewards,
                'level_statistics' => $levelStats,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 查询排行榜
     * GET /api/referral/leaderboard
     * @return array
     */
    public function leaderboard()
    {
        $periodType = $this->request->get('period', 'monthly');
        $periodDate = $this->request->get('date', null);

        try {
            $leaderboardService = new LeaderboardService();
            $data = $leaderboardService->getLeaderboard($periodType, $periodDate, $this->user['user_id']);

            return $this->renderSuccess($data);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 生成分享链接
     * @param string $code
     * @return string
     */
    private function generateShareUrl($code)
    {
        // TODO: 根据实际情况生成分享链接
        $baseUrl = request()->domain();
        return $baseUrl . '?ref=' . $code;
    }

    /**
     * 生成二维码URL
     * @param string $code
     * @return string
     */
    private function generateQrCodeUrl($code)
    {
        // TODO: 集成二维码生成服务
        return '';
    }

    /**
     * 获取任务信息
     * @param int $relationId
     * @return array
     */
    private function getTasksInfo($relationId)
    {
        // TODO: 从配置表获取任务信息
        return [
            'referrer_tasks' => [],
            'referee_tasks' => [],
        ];
    }

    /**
     * 格式化推荐记录项
     * @param ReferralRelation $item
     * @return array
     */
    private function formatReferralItem($item)
    {
        $statusText = ['', '待完成', '已完成', '已失效'];

        $formatted = [
            'id' => $item['id'],
            'referee_info' => [
                'nickname' => $item->referee['nickName'] ?? '',
                'avatar' => $item->referee['avatarUrl'] ?? '',
            ],
            'level' => $item['level'],
            'status' => $item['status'],
            'status_text' => $statusText[$item['status']] ?? '',
            'referrer_task_status' => $item['referrer_task_status'],
            'referee_task_status' => $item['referee_task_status'],
            'create_time' => $item['create_time'],
        ];

        // 添加奖励信息
        if ($item->rewards) {
            $formatted['rewards'] = [];
            foreach ($item->rewards as $reward) {
                $formatted['rewards'][] = [
                    'reward_type' => $reward['reward_type'],
                    'reward_type_text' => $reward->reward_type['text'] ?? '',
                    'reward_amount' => $reward['reward_amount'],
                    'status' => $reward['status'],
                    'status_text' => $reward->status['text'] ?? '',
                ];
            }
        }

        return $formatted;
    }

    /**
     * 获取各级别统计
     * @param int $userId
     * @return array
     */
    private function getLevelStatistics($userId)
    {
        $stats = [];
        $levels = [1, 2, 3];

        foreach ($levels as $level) {
            $count = ReferralRelation::where('referrer_user_id', $userId)
                ->where('level', $level)
                ->where('status', 2)
                ->count();

            $rewards = ReferralReward::where('user_id', $userId)
                ->where('status', 2)
                ->alias('r')
                ->join('referral_relation rr', 'r.relation_id = rr.id')
                ->where('rr.level', $level)
                ->sum('r.reward_amount');

            $stats[] = [
                'level' => $level,
                'count' => $count,
                'rewards' => $rewards ?: 0,
            ];
        }

        return $stats;
    }
}
