<?php

namespace app\common\library\referral;

use app\common\model\UserReferralCode;

/**
 * 推荐码生成器
 * Class ReferralCodeGenerator
 * @package app\common\library\referral
 */
class ReferralCodeGenerator
{
    // 字符集(排除易混淆字符: 0/O, 1/I/l)
    private const CHARSET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    
    // 默认推荐码长度
    private const DEFAULT_LENGTH = 6;
    
    // 最大尝试次数
    private const MAX_ATTEMPTS = 10;

    /**
     * 生成推荐码
     * @param int $userId 用户ID
     * @param int $length 推荐码长度(6-8位)
     * @return string
     * @throws \Exception
     */
    public function generate($userId, $length = self::DEFAULT_LENGTH)
    {
        // 验证长度
        if ($length < 6 || $length > 8) {
            throw new \Exception('推荐码长度必须在6-8位之间');
        }

        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            // 基于用户ID和时间戳生成种子
            $seed = $userId . microtime(true) . mt_rand();
            $hash = hash('sha256', $seed);
            
            // 从哈希中提取字符
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $index = hexdec(substr($hash, $j * 2, 2)) % strlen(self::CHARSET);
                $code .= self::CHARSET[$index];
            }
            
            // 检查唯一性
            if (!$this->codeExists($code)) {
                return $code;
            }
        }
        
        throw new \Exception('无法生成唯一推荐码，请稍后重试');
    }

    /**
     * 检查推荐码是否已存在
     * @param string $code
     * @return bool
     */
    private function codeExists($code)
    {
        return UserReferralCode::where('referral_code', $code)->count() > 0;
    }

    /**
     * 验证推荐码格式
     * @param string $code
     * @return bool
     */
    public static function validate($code)
    {
        // 检查长度
        $length = strlen($code);
        if ($length < 6 || $length > 8) {
            return false;
        }

        // 检查字符集(大小写不敏感)
        $code = strtoupper($code);
        for ($i = 0; $i < $length; $i++) {
            if (strpos(self::CHARSET, $code[$i]) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 标准化推荐码(转大写)
     * @param string $code
     * @return string
     */
    public static function normalize($code)
    {
        return strtoupper(trim($code));
    }
}
