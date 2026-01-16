<?php
declare (strict_types = 1);

namespace app\api\validate\passport;

use think\Validate;

/**
 * 验证类：发送短信验证码
 * Class SmsCaptcha
 * @package app\api\validate\passport
 */
class SmsCaptcha extends Validate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [
        // 用户手机号
        'mobile' => ['require'],
    ];

    /**
     * 验证提示
     * @var string[]
     */
    protected $message  =   [
        'mobile.require' => '手机号不能为空',
    ];
}