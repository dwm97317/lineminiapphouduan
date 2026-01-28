<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;
use think\Cache;
use app\common\library\storage\engine\Cloudflare as CloudflareEngine;

class CloudflareR2 extends Controller
{
    public function usage()
    {
        $aid = $this->request->get('aid');
        $refresh = (int)$this->request->get('refresh', 0);
        $wxappId = $this->store['wxapp']['wxapp_id'];
        $storage = SettingModel::getItem('storage', $wxappId);
        if (empty($storage) || ($storage['default'] ?? '') !== 'cloudflare') {
            return $this->renderError('未启用Cloudflare R2');
        }
        $cf = $storage['engine']['cloudflare'];
        $accounts = [];
        if (isset($cf['accounts']) && is_array($cf['accounts']) && !empty($cf['accounts'])) {
            $accounts = $cf['accounts'];
        } else {
            $defId = isset($cf['account_id']) && $cf['account_id'] ? $cf['account_id'] : 'default';
            $accounts[$defId] = [
                'bucket' => $cf['bucket'] ?? '',
                'access_key' => $cf['access_key'] ?? '',
                'secret_key' => $cf['secret_key'] ?? '',
                'account_id' => $cf['account_id'] ?? '',
                'domain' => $cf['domain'] ?? '',
            ];
            $cf['active_account_id'] = $defId;
        }
        if (!$aid) {
            $aid = $cf['active_account_id'] ?? array_keys($accounts)[0];
        }
        if (!isset($accounts[$aid])) {
            return $this->renderError('账号不存在');
        }
        $conf = $accounts[$aid];
        if (empty($conf['bucket']) || empty($conf['access_key']) || empty($conf['secret_key']) || empty($conf['account_id'])) {
            return $this->renderError('账号配置不完整');
        }
        $cacheKey = "__r2_usage__{$aid}__{$conf['bucket']}";
        if ($refresh === 1) {
            Cache::rm($cacheKey);
        }
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
                return $this->renderError($e->getMessage());
            }
        }
        $capacityBytes = 10 * 1024 * 1024 * 1024;
        $percent = round($usedBytes / $capacityBytes * 100, 2);
        if ($percent > 100) {
            $percent = 100;
        }
        $usedGb = $usedBytes / (1024 * 1024 * 1024);
        $usedText = '已用 ' . number_format($usedGb, 2) . ' GB / 10 GB (' . number_format($percent, 2) . '%)';
        return $this->renderSuccess('success', '', [
            'percent' => $percent,
            'used_text' => $usedText,
            'used_bytes' => $usedBytes,
        ]);
    }
}
