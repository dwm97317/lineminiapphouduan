<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;

use app\common\model\ReferralSystemConfig;
use app\common\model\ReferralTaskConfig;
use app\common\model\ReferralRewardConfig;
use app\common\model\ReferralRelation;
use app\common\model\ReferralReward;
use app\common\service\referral\ExpirationService;
use app\common\service\referral\RewardService;

/**
 * 推荐奖励后台管理控制器
 * Class Referral
 * @package app\store\controller\setting
 */
class Referral extends Controller
{
    /**
     * 推荐配置页面
     * GET /store/referral/config
     * @return mixed
     */
    public function config()
    {
        // 如果是AJAX请求，返回JSON数据
        if ($this->request->isAjax()) {
            return $this->getConfigData();
        }

        // 否则返回视图
        try {
            $wxappId = $this->getWxappId();
            
            // 使用原始 SQL 查询系统配置，避免访问器干扰
            $systemConfigList = \think\Db::name('referral_system_config')
                ->where('wxapp_id', $wxappId)
                ->select();
            $systemConfig = [];
            foreach ($systemConfigList as $config) {
                $systemConfig[$config['config_key']] = $config['config_value'];
            }

            // 使用原始 SQL 查询任务配置，避免访问器干扰
            $taskConfigList = \think\Db::name('referral_task_config')
                ->where('wxapp_id', $wxappId)
                ->order('user_type', 'asc')
                ->order('sort_order', 'asc')
                ->select();
            
            $taskConfigs = [
                'referrer' => [],
                'referee' => [],
            ];
            foreach ($taskConfigList as $task) {
                // 直接使用原始值，不需要处理访问器
                $userType = $task['user_type'];
                
                if ($userType == 1) {
                    $taskConfigs['referrer'][] = $task;
                } else {
                    $taskConfigs['referee'][] = $task;
                }
            }

            // 使用原始 SQL 查询奖励配置，避免访问器干扰
            $rewardConfigs = \think\Db::name('referral_reward_config')
                ->where('wxapp_id', $wxappId)
                ->order('level', 'asc')
                ->order('user_type', 'asc')
                ->select();

            return $this->fetch('config', [
                'system_config' => $systemConfig,
                'task_configs' => $taskConfigs,
                'reward_configs' => $rewardConfigs,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 获取推荐配置数据（AJAX）
     * @return array
     */
    private function getConfigData()
    {
        try {
            // 获取系统配置
            $systemConfig = ReferralSystemConfig::select();

            // 获取任务配置
            $taskConfig = ReferralTaskConfig::order('user_type', 'asc')
                ->order('sort_order', 'asc')
                ->select();

            // 获取奖励配置
            $rewardConfig = ReferralRewardConfig::order('level', 'asc')
                ->order('user_type', 'asc')
                ->select();

            return $this->renderSuccess('success', '', [
                'system_config' => $systemConfig,
                'task_config' => $taskConfig,
                'reward_config' => $rewardConfig,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage(), '', []);
        }
    }

    /**
     * 保存推荐配置
     * POST /store/referral/config/save
     * @return mixed
     */
    public function saveConfig()
    {
        // 直接从 $_POST 获取数据，避免 Request 类的类型检查
        $type = isset($_POST['config_type']) ? $_POST['config_type'] : '';
        
        if (empty($type)) {
            return json(['code' => 0, 'msg' => '参数错误：缺少配置类型']);
        }

        try {
            \think\Db::startTrans();

            switch ($type) {
                case 'system':
                    $data = isset($_POST['system_config']) ? $_POST['system_config'] : [];
                    if (empty($data)) {
                        return json(['code' => 0, 'msg' => '参数错误：缺少系统配置数据']);
                    }
                    $this->saveSystemConfig($data);
                    break;
                case 'task':
                    $data = isset($_POST['task_config']) ? $_POST['task_config'] : [];
                    if (empty($data)) {
                        return json(['code' => 0, 'msg' => '参数错误：缺少任务配置数据']);
                    }
                    $this->saveTaskConfig($data);
                    break;
                case 'reward':
                    $data = isset($_POST['reward_config']) ? $_POST['reward_config'] : [];
                    if (empty($data)) {
                        return json(['code' => 0, 'msg' => '参数错误：缺少奖励配置数据']);
                    }
                    $this->saveRewardConfig($data);
                    break;
                default:
                    return json(['code' => 0, 'msg' => '配置类型错误']);
            }

            \think\Db::commit();

            // 记录操作日志
            $this->logOperation('保存推荐配置', $type);

            return json(['code' => 1, 'msg' => '配置保存成功']);
        } catch (\Exception $e) {
            \think\Db::rollback();
            
            // 详细的错误信息
            $errorMsg = $e->getMessage();
            $errorFile = $e->getFile();
            $errorLine = $e->getLine();
            
            // 返回详细错误信息
            $detailedError = $errorMsg;
            if (config('app_debug')) {
                $detailedError .= " (文件: " . basename($errorFile) . ":{$errorLine})";
            }
            
            return json(['code' => 0, 'msg' => '保存失败：' . $detailedError]);
        }
    }

    /**
     * 推荐关系列表页面
     * GET /store/referral/relations
     * @return mixed
     */
    public function relations()
    {
        // 获取搜索参数
        $search = $this->request->get();
        
        try {
            $query = ReferralRelation::with(['referrer', 'referee']);
            
            // 状态筛选
            if (isset($search['status']) && $search['status'] !== '') {
                $query->where('status', $search['status']);
            }
            
            // 级别筛选
            if (isset($search['level']) && $search['level'] !== '') {
                $query->where('level', $search['level']);
            }
            
            // 推荐人ID筛选
            if (isset($search['referrer_user_id']) && $search['referrer_user_id'] !== '') {
                $query->where('referrer_user_id', $search['referrer_user_id']);
            }
            
            // 被推荐人ID筛选
            if (isset($search['referee_user_id']) && $search['referee_user_id'] !== '') {
                $query->where('referee_user_id', $search['referee_user_id']);
            }

            // 分页查询
            $list = $query->order('create_time', 'desc')
                ->paginate(20, false, [
                    'query' => $this->request->get()
                ]);

            // 返回视图
            return $this->fetch('relations', [
                'list' => $list,
                'search' => $search,
            ]);
        } catch (\Exception $e) {
            return $this->fetch('relations', [
                'list' => [],
                'search' => $search,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取推荐关系列表数据（AJAX）
     * @return array
     */
    private function getRelationsData()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        $status = $this->request->get('status', 0);
        $keyword = $this->request->get('keyword', '');

        try {
            $query = ReferralRelation::with(['referrer', 'referee', 'rewards']);

            // 状态筛选
            if ($status > 0) {
                $query->where('status', $status);
            }

            // 关键词搜索
            if (!empty($keyword)) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereOr('referral_code', 'like', "%{$keyword}%")
                      ->whereOr('id', $keyword);
                });
            }

            $total = $query->count();
            $list = $query->order('create_time', 'desc')
                ->page($page, $limit)
                ->select();

            // 格式化数据
            $formattedList = [];
            foreach ($list as $item) {
                $formattedList[] = $this->formatRelationItem($item);
            }

            return $this->renderSuccess('success', '', [
                'list' => $formattedList,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage(), '', []);
        }
    }

    /**
     * 使推荐关系失效
     * POST /store/referral/relation/invalidate
     * @return array
     */
    public function invalidateRelation()
    {
        $relationId = $this->request->post('relation_id', 0);
        $reason = $this->request->post('reason', '管理员操作');

        if (empty($relationId)) {
            return $this->renderError('请选择推荐关系', '', []);
        }

        try {
            $expirationService = new ExpirationService();
            $expirationService->invalidateRelation($relationId, $reason);

            // 记录操作日志
            $this->logOperation('使推荐关系失效', "关系ID: {$relationId}, 原因: {$reason}");

            return $this->renderSuccess('操作成功', '', []);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage(), '', []);
        }
    }

    /**
     * 奖励记录列表页面
     * GET /store/referral/rewards
     * @return mixed
     */
    public function rewards()
    {
        // 获取搜索参数
        $search = $this->request->get();
        
        try {
            $query = ReferralReward::with(['user', 'relation']);
            
            // 用户ID筛选
            if (isset($search['user_id']) && $search['user_id'] !== '') {
                $query->where('user_id', $search['user_id']);
            }
            
            // 用户类型筛选
            if (isset($search['user_type']) && $search['user_type'] !== '') {
                $query->where('user_type', $search['user_type']);
            }
            
            // 奖励类型筛选
            if (isset($search['reward_type']) && $search['reward_type'] !== '') {
                $query->where('reward_type', $search['reward_type']);
            }
            
            // 状态筛选
            if (isset($search['status']) && $search['status'] !== '') {
                $query->where('status', $search['status']);
            }

            // 计算统计数据
            $statistics = [
                'total_cash' => ReferralReward::where('reward_type', 1)->where('status', 2)->sum('reward_amount'),
                'total_points' => ReferralReward::where('reward_type', 2)->where('status', 2)->sum('reward_amount'),
                'total_coupons' => ReferralReward::where('reward_type', 3)->where('status', 2)->count(),
                'total_count' => ReferralReward::count(),
            ];

            // 分页查询
            $list = $query->order('create_time', 'desc')
                ->paginate(20, false, [
                    'query' => $this->request->get()
                ]);

            // 返回视图
            return $this->fetch('rewards', [
                'list' => $list,
                'search' => $search,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return $this->fetch('rewards', [
                'list' => [],
                'search' => $search,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取奖励记录列表数据（AJAX）
     * @return array
     */
    private function getRewardsData()
    {
        $page = $this->request->get('page', 1);
        $limit = $this->request->get('limit', 20);
        $status = $this->request->get('status', 0);
        $rewardType = $this->request->get('reward_type', 0);

        try {
            $query = ReferralReward::with(['user', 'relation']);

            // 状态筛选
            if ($status > 0) {
                $query->where('status', $status);
            }

            // 奖励类型筛选
            if ($rewardType > 0) {
                $query->where('reward_type', $rewardType);
            }

            $total = $query->count();
            $list = $query->order('create_time', 'desc')
                ->page($page, $limit)
                ->select();

            // 格式化数据
            $formattedList = [];
            foreach ($list as $item) {
                $formattedList[] = $this->formatRewardItem($item);
            }

            return $this->renderSuccess('success', '', [
                'list' => $formattedList,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage(), '', []);
        }
    }

    /**
     * 回收奖励
     * POST /store/referral/reward/recycle
     * @return array
     */
    public function recycleReward()
    {
        $relationId = $this->request->post('relation_id', 0);
        $reason = $this->request->post('reason', '管理员回收');

        if (empty($relationId)) {
            return $this->renderError('请选择推荐关系', '', []);
        }

        try {
            $rewardService = new RewardService();
            $rewardService->recycleRewards($relationId, $reason);

            // 记录操作日志
            $this->logOperation('回收推荐奖励', "关系ID: {$relationId}, 原因: {$reason}");

            return $this->renderSuccess('奖励回收成功', '', []);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage(), '', []);
        }
    }

    /**
     * 保存系统配置
     * @param array $data
     */
    private function saveSystemConfig($data)
    {
        $wxappId = $this->getWxappId();
        
        // 遍历每个配置项
        foreach ($data as $key => $value) {
            try {
                // 使用原始SQL查询，避免模型访问器干扰
                $config = \think\Db::name('referral_system_config')
                    ->where('config_key', $key)
                    ->where('wxapp_id', $wxappId)
                    ->find();
                
                if ($config) {
                    // 更新现有配置 - 使用原始SQL，禁用严格模式
                    \think\Db::name('referral_system_config')
                        ->strict(false)
                        ->where('config_key', $key)
                        ->where('wxapp_id', $wxappId)
                        ->update(['config_value' => $value]);
                } else {
                    // 创建新配置 - 使用原始SQL，禁用严格模式
                    \think\Db::name('referral_system_config')
                        ->strict(false)
                        ->insert([
                            'wxapp_id' => $wxappId,
                            'config_key' => $key,
                            'config_value' => $value,
                            'config_name' => $this->getConfigName($key),
                            'is_enabled' => 1,
                        ]);
                }
            } catch (\Exception $e) {
                // 记录错误但继续处理其他配置
                \think\Log::error("保存系统配置失败 - Config Key: {$key}, Error: " . $e->getMessage());
                throw $e; // 重新抛出异常以便外层捕获
            }
        }
    }
    
    /**
     * 获取配置项的中文名称
     * @param string $key
     * @return string
     */
    private function getConfigName($key)
    {
        $names = [
            'max_referral_levels' => '最大推荐级数',
            'referral_code_length' => '推荐码长度',
            'expire_days' => '推荐关系失效天数',
            'referral_limit_enabled' => '启用推荐上限',
            'referral_limit_per_month' => '每月推荐上限',
            'leaderboard_enabled' => '启用排行榜',
            'leaderboard_top_count' => '排行榜显示人数',
        ];
        
        return $names[$key] ?? $key;
    }

    /**
     * 保存任务配置
     * @param array $data
     */
    private function saveTaskConfig($data)
    {
        // 如果没有任务配置数据，直接返回（可能是还没有创建任务配置）
        if (empty($data)) {
            return;
        }
        
        $wxappId = $this->getWxappId();
        
        // 用户类型映射
        $userTypeMap = [
            'referrer' => 1,
            'referee' => 2,
        ];
        
        // 遍历推荐人和被推荐人的任务配置
        foreach ($data as $userTypeKey => $tasks) {
            if (!isset($userTypeMap[$userTypeKey]) || !is_array($tasks)) {
                continue;
            }
            
            $userType = $userTypeMap[$userTypeKey];
            
            foreach ($tasks as $taskId => $taskData) {
                if (!is_array($taskData)) {
                    continue;
                }
                
                try {
                    // 使用原始SQL查询验证任务 - 确保 taskId 是整数
                    $taskId = intval($taskId);
                    $task = \think\Db::name('referral_task_config')
                        ->where('id', $taskId)
                        ->where('wxapp_id', $wxappId)
                        ->where('user_type', $userType)
                        ->find();
                    
                    if ($task) {
                        $updateData = [
                            'is_enabled' => isset($taskData['is_enabled']) ? intval($taskData['is_enabled']) : 0,
                            'is_required' => isset($taskData['is_required']) ? intval($taskData['is_required']) : 0,
                        ];
                        
                        // 处理 task_params - 只处理数组格式
                        if (isset($taskData['task_params']) && is_array($taskData['task_params'])) {
                            // 过滤空值
                            $params = array_filter($taskData['task_params'], function($value) {
                                return $value !== '' && $value !== null;
                            });
                            
                            if (!empty($params)) {
                                // 转换为JSON字符串
                                $updateData['task_params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
                            }
                        }
                        
                        // 使用原始SQL更新，禁用严格模式避免类型检查错误
                        $result = \think\Db::name('referral_task_config')
                            ->strict(false)  // 关键：禁用严格模式
                            ->where('id', $taskId)
                            ->where('wxapp_id', $wxappId)
                            ->where('user_type', $userType)
                            ->update($updateData);
                        
                        if ($result === false) {
                            throw new \Exception("任务配置更新失败 - Task ID: {$taskId}");
                        }
                    } else {
                        \think\Log::warning("任务配置不存在 - Task ID: {$taskId}, User Type: {$userTypeKey}");
                    }
                } catch (\Exception $e) {
                    // 记录详细错误信息
                    \think\Log::error("保存任务配置失败 - Task ID: {$taskId}, User Type: {$userTypeKey}, Error: " . $e->getMessage() . "\nData: " . json_encode($taskData, JSON_UNESCAPED_UNICODE));
                    throw $e; // 重新抛出异常以便外层捕获
                }
            }
        }
    }

    /**
     * 保存奖励配置
     * @param array $data
     */
    private function saveRewardConfig($data)
    {
        // 如果没有奖励配置数据，直接返回
        if (empty($data)) {
            return;
        }
        
        $wxappId = $this->getWxappId();
        
        foreach ($data as $configId => $configData) {
            if (!is_array($configData)) {
                continue;
            }
            
            try {
                // 确保 configId 是整数
                $configId = intval($configId);
                
                // 使用原始SQL查询，避免模型访问器干扰
                $config = \think\Db::name('referral_reward_config')
                    ->where('id', $configId)
                    ->where('wxapp_id', $wxappId)
                    ->find();
                
                if ($config) {
                    $updateData = [
                        'is_enabled' => isset($configData['is_enabled']) ? intval($configData['is_enabled']) : 0,
                    ];
                    
                    // 只更新提供的字段
                    if (isset($configData['reward_type'])) {
                        $updateData['reward_type'] = intval($configData['reward_type']);
                    }
                    if (isset($configData['reward_amount'])) {
                        $updateData['reward_amount'] = floatval($configData['reward_amount']);
                    }
                    if (isset($configData['reward_ratio'])) {
                        $updateData['reward_ratio'] = floatval($configData['reward_ratio']);
                    }
                    if (isset($configData['expire_days'])) {
                        $updateData['expire_days'] = $configData['expire_days'] !== '' ? intval($configData['expire_days']) : null;
                    }
                    
                    // 处理 reward_params - 只处理数组格式
                    if (isset($configData['reward_params']) && is_array($configData['reward_params'])) {
                        // 过滤空值
                        $params = array_filter($configData['reward_params'], function($value) {
                            return $value !== '' && $value !== null;
                        });
                        
                        if (!empty($params)) {
                            // 转换为JSON字符串
                            $updateData['reward_params'] = json_encode($params, JSON_UNESCAPED_UNICODE);
                        } else {
                            $updateData['reward_params'] = null;
                        }
                    }
                    
                    // 使用原始SQL更新，禁用严格模式避免类型检查错误
                    $result = \think\Db::name('referral_reward_config')
                        ->strict(false)  // 关键：禁用严格模式
                        ->where('id', $configId)
                        ->where('wxapp_id', $wxappId)
                        ->update($updateData);
                    
                    if ($result === false) {
                        throw new \Exception("奖励配置更新失败 - Config ID: {$configId}");
                    }
                } else {
                    \think\Log::warning("奖励配置不存在 - Config ID: {$configId}");
                }
            } catch (\Exception $e) {
                // 记录详细错误信息
                \think\Log::error("保存奖励配置失败 - Config ID: {$configId}, Error: " . $e->getMessage() . "\nData: " . json_encode($configData, JSON_UNESCAPED_UNICODE));
                throw $e; // 重新抛出异常以便外层捕获
            }
        }
    }

    /**
     * 格式化推荐关系项
     * @param ReferralRelation $item
     * @return array
     */
    private function formatRelationItem($item)
    {
        $statusText = ['', '待完成', '已完成', '已失效'];

        return [
            'id' => $item['id'],
            'referrer_info' => [
                'user_id' => $item['referrer_user_id'],
                'nickname' => $item->referrer['nickName'] ?? '',
            ],
            'referee_info' => [
                'user_id' => $item['referee_user_id'],
                'nickname' => $item->referee['nickName'] ?? '',
            ],
            'referral_code' => $item['referral_code'],
            'level' => $item['level'],
            'status' => $item['status'],
            'status_text' => $statusText[$item['status']] ?? '',
            'referrer_task_status' => $item['referrer_task_status'],
            'referee_task_status' => $item['referee_task_status'],
            'reward_issued' => $item['reward_issued'],
            'create_time' => $item['create_time'],
            'expire_time' => $item['expire_time'],
        ];
    }

    /**
     * 格式化奖励记录项
     * @param ReferralReward $item
     * @return array
     */
    private function formatRewardItem($item)
    {
        return [
            'id' => $item['id'],
            'relation_id' => $item['relation_id'],
            'user_info' => [
                'user_id' => $item['user_id'],
                'nickname' => $item->user['nickName'] ?? '',
            ],
            'user_type' => $item['user_type'],
            'user_type_text' => $item->user_type['text'] ?? '',
            'reward_type' => $item['reward_type'],
            'reward_type_text' => $item->reward_type['text'] ?? '',
            'reward_amount' => $item['reward_amount'],
            'status' => $item['status'],
            'status_text' => $item->status['text'] ?? '',
            'issue_time' => $item['issue_time'],
            'create_time' => $item['create_time'],
        ];
    }

    /**
     * 记录操作日志
     * @param string $action
     * @param string $detail
     */
    private function logOperation($action, $detail = '')
    {
        // TODO: 集成操作日志系统
        \think\Log::info("推荐系统操作: {$action} - {$detail}");
    }
}
