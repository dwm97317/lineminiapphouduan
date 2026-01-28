<?php

namespace app\task\behavior;

use think\Cache;
use think\Session;
use app\store\model\Setting as StoreSettingModel;
use app\common\library\storage\engine\Cloudflare as CloudflareEngine;

/**
 * Cloudflare R2容量监控与自动切换
 */
class R2Monitor
{
    /**
     * 每次请求触发，但通过缓存节流
     */
    public function run($arg = null)
    {
        // 仅在商户后台会话下运行
        $session = Session::get('yoshop_store');
        if (empty($session) || empty($session['wxapp']['wxapp_id'])) {
            return true;
        }
        $wxappId = $session['wxapp']['wxapp_id'];
        $throttleKey = "__task_space__r2_monitor__{$wxappId}";
        if (Cache::has($throttleKey)) {
            return true;
        }
        // 节流 15 分钟
        Cache::set($throttleKey, time(), 15 * 60);

        // 读取存储配置
        $storage = StoreSettingModel::getItem('storage', $wxappId);
        if (empty($storage) || ($storage['default'] ?? '') !== 'cloudflare') {
            return true;
        }
        $cf = $storage['engine']['cloudflare'];
        $autoSwitch = isset($cf['auto_switch']) ? (int)$cf['auto_switch'] : 0;
        if ($autoSwitch !== 1) {
            return true;
        }

        // 计算当前账号使用率
        $accounts = [];
        if (isset($cf['accounts']) && is_array($cf['accounts']) && !empty($cf['accounts'])) {
            $accounts = $cf['accounts'];
        } else {
            // 兼容单账号
            $aid = $cf['account_id'] ?: 'default';
            $accounts[$aid] = [
                'bucket' => $cf['bucket'] ?? '',
                'access_key' => $cf['access_key'] ?? '',
                'secret_key' => $cf['secret_key'] ?? '',
                'account_id' => $cf['account_id'] ?? '',
                'domain' => $cf['domain'] ?? '',
            ];
            $cf['active_account_id'] = $aid;
        }
        $activeId = $cf['active_account_id'] ?? array_keys($accounts)[0];
        $capacityBytes = 10 * 1024 * 1024 * 1024;

        $usage = [];
        foreach ($accounts as $aid => $conf) {
            $cacheKey = "__r2_usage__{$aid}__{$conf['bucket']}";
            if (Cache::has($cacheKey)) {
                $usedBytes = (int)Cache::get($cacheKey);
            } else {
                try {
                    $engine = new CloudflareEngine([
                        'accounts' => [$aid => $conf],
                        'active_account_id' => $aid,
                        'domain' => $conf['domain'] ?? '',
                    ]);
                    $usedBytes = $engine->getBucketUsageBytes();
                    Cache::set($cacheKey, $usedBytes, 4 * 60 * 60);
                } catch (\Exception $e) {
                    // 跳过错误账号
                    continue;
                }
            }
            $usage[$aid] = $usedBytes;
        }

        if (!isset($usage[$activeId])) {
            return true;
        }
        $activePercent = round($usage[$activeId] / $capacityBytes * 100, 2);
        if ($activePercent < 95) {
            return true;
        }

        // 寻找可用账号
        $targetId = null;
        foreach ($usage as $aid => $bytes) {
            $percent = round($bytes / $capacityBytes * 100, 2);
            if ($percent < 95) {
                $targetId = $aid;
                break;
            }
        }

        if (!$targetId) {
            Cache::set("__r2_autoswitch_warning__{$wxappId}", [
                'time' => time(),
                'message' => '所有R2账号使用率均超过95%，已暂停自动切换'
            ], 60 * 60);
            return true;
        }

        // 更新设置为目标账号
        $cf['active_account_id'] = $targetId;
        $cf['domain'] = $accounts[$targetId]['domain'] ?? ($cf['domain'] ?? '');
        $storage['engine']['cloudflare'] = $cf;

        $model = new StoreSettingModel;
        $model::$wxapp_id = $wxappId;
        $model->edit('storage', $storage);

        Cache::set("__r2_autoswitched__{$wxappId}", [
            'time' => time(),
            'from' => $activeId,
            'to' => $targetId
        ], 60 * 60);

        return true;
    }
}

