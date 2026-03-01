<?php

namespace app\store\model;

use think\Model;

/**
 * 账单模板模型
 */
class StatementTemplate extends Model
{
    protected $name = 'statement_template';
    // 关闭自动时间戳，手动管理
    protected $autoWriteTimestamp = false;
    protected $createTime = false;
    protected $updateTime = false;
    
    /**
     * 获取默认模板
     * @return array|null
     */
    public static function getDefault()
    {
        $wxappId = self::getWxappId();
        
        $template = self::where('wxapp_id', $wxappId)
            ->where('is_default', 1)
            ->find();
        
        return $template ? $template->toArray() : null;
    }
    
    /**
     * 获取当前小程序ID
     */
    private static function getWxappId()
    {
        // 从Session获取wxapp_id（store模块）
        $session = \think\Session::get('yoshop_store');
        return $session['wxapp']['wxapp_id'] ?? 10001;
    }
}
