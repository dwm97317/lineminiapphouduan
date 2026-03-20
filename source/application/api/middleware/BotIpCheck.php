<?php

namespace app\api\middleware;

use app\common\exception\BaseException;

/**
 * Bot API IP 白名单中间件
 * 限制只能从特定 IP 访问 Bot API
 * Class BotIpCheck
 * @package app\api\middleware
 */
class BotIpCheck
{
    /**
     * IP 白名单
     * 可以添加 Bot 服务器的 IP 地址
     */
    const WHITELIST_IPS = [
        '127.0.0.1',      // Localhost (开发环境)
        '::1',            // IPv6 localhost
        // 'your-bot-server-ip',  // 生产环境 Bot 服务器 IP
        // 'another-ip',
    ];

    /**
     * 是否启用 IP 检查
     * 生产环境应设为 true
     */
    const ENABLE_IP_CHECK = false; // 开发环境先设为 false，方便测试

    /**
     * 中间件执行入口
     * @param \think\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws BaseException
     */
    public function handle($request, \Closure $next)
    {
        // 如果未启用 IP 检查，直接放行
        if (!self::ENABLE_IP_CHECK) {
            return $next($request);
        }
        
        // 获取客户端 IP
        $clientIp = $this->getClientIp($request);
        
        // 检查 IP 是否在白名单中
        if (!$this->isWhitelisted($clientIp)) {
            // 记录非法访问日志
            \think\Log::record(sprintf(
                '[Bot IP Check] Unauthorized access attempt from IP: %s, Path: %s',
                $clientIp,
                $request->url()
            ), 'warning');
            
            throw new BaseException([
                'code' => 403,
                'msg' => 'IP 地址未被授权',
            ]);
        }
        
        return $next($request);
    }

    /**
     * 获取客户端真实 IP
     * @param \think\Request $request
     * @return string
     */
    protected function getClientIp($request)
    {
        // 检查代理头
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_TRUE_CLIENT_IP',   // Akamai
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim($_SERVER[$header]);
                // X-Forwarded-For 可能包含多个 IP，取第一个
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                return $ip;
            }
        }
        
        // 返回直接连接的 IP
        return $request->ip();
    }

    /**
     * 检查 IP 是否在白名单中
     * @param string $ip
     * @return bool
     */
    protected function isWhitelisted($ip)
    {
        // 精确匹配
        if (in_array($ip, self::WHITELIST_IPS)) {
            return true;
        }
        
        // CIDR 格式匹配 (例如：192.168.1.0/24)
        foreach (self::WHITELIST_IPS as $whitelistIp) {
            if (strpos($whitelistIp, '/') !== false && $this->ipInRange($ip, $whitelistIp)) {
                return true;
            }
        }
        
        // 通配符匹配 (例如：192.168.1.*)
        foreach (self::WHITELIST_IPS as $whitelistIp) {
            if (strpos($whitelistIp, '*') !== false && $this->matchWildcard($ip, $whitelistIp)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 检查 IP 是否在 CIDR 范围内
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    protected function ipInRange($ip, $cidr)
    {
        list($subnet, $mask) = explode('/', $cidr);
        
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = ~((1 << (32 - (int)$mask)) - 1);
        
        return ($ip & $mask) == ($subnet & $mask);
    }

    /**
     * 通配符匹配
     * @param string $ip
     * @param string $wildcard
     * @return bool
     */
    protected function matchWildcard($ip, $wildcard)
    {
        $pattern = str_replace('.', '\.', $wildcard);
        $pattern = str_replace('*', '\d{1,3}', $pattern);
        $pattern = '/^' . $pattern . '$/';
        
        return preg_match($pattern, $ip) === 1;
    }
}
