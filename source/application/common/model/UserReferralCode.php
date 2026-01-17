<?php

namespace app\common\model;

use app\common\library\referral\ReferralCodeGenerator;

/**
 * 用户推荐码模型
 * Class UserReferralCode
 * @package app\common\model
 */
class UserReferralCode extends BaseModel
{
    protected $name = 'user_referral_code';
    protected $pk = 'id';

    /**
     * 获取或创建用户推荐码
     * @param int $userId
     * @return UserReferralCode|null
     * @throws \Exception
     */
    public static function getOrCreate($userId)
    {
        // 查找现有推荐码
        $codeModel = self::where('user_id', $userId)->find();
        
        if ($codeModel) {
            return $codeModel;
        }

        // 生成新推荐码
        $generator = new ReferralCodeGenerator();
        $code = $generator->generate($userId);

        // 创建记录
        $codeModel = new self();
        $codeModel->save([
            'user_id' => $userId,
            'referral_code' => $code,
            'share_count' => 0,
            'click_count' => 0,
            'register_count' => 0,
            'success_count' => 0,
            'total_reward' => 0.00,
            'wxapp_id' => self::$wxapp_id,
        ]);

        return $codeModel;
    }

    /**
     * 根据推荐码查找
     * @param string $code
     * @return UserReferralCode|null
     */
    public static function findByCode($code)
    {
        $code = ReferralCodeGenerator::normalize($code);
        return self::where('referral_code', $code)->find();
    }

    /**
     * 增加分享次数
     * @return bool
     */
    public function incrementShareCount()
    {
        return $this->setInc('share_count', 1);
    }

    /**
     * 增加点击次数
     * @return bool
     */
    public function incrementClickCount()
    {
        return $this->setInc('click_count', 1);
    }

    /**
     * 增加注册人数
     * @return bool
     */
    public function incrementRegisterCount()
    {
        return $this->setInc('register_count', 1);
    }

    /**
     * 增加成功推荐数
     * @return bool
     */
    public function incrementSuccessCount()
    {
        return $this->setInc('success_count', 1);
    }

    /**
     * 增加累计奖励金额
     * @param float $amount
     * @return bool
     */
    public function incrementTotalReward($amount)
    {
        return $this->setInc('total_reward', $amount);
    }

    /**
     * 获取推荐统计信息
     * @return array
     */
    public function getStatistics()
    {
        return [
            'share_count' => $this->share_count,
            'click_count' => $this->click_count,
            'register_count' => $this->register_count,
            'success_count' => $this->success_count,
            'total_reward' => $this->total_reward,
        ];
    }

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User', 'user_id');
    }
}
