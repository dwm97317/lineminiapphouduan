<?php
declare (strict_types=1);

namespace app\web\controller;

use think\response\Json;
use app\web\service\passport\{Captcha as CaptchaService, SmsCaptcha as SmsCaptchaService};
use cores\exception\BaseException;

/**
 * 验证码管理
 * Class Cart
 * @package app\web\controller
 */
class Captcha extends Controller
{
    /**
     * 图形验证码
     * @return Json
     */
    public function image(): Json
    {
        $CaptchaService = new CaptchaService;
        return $this->renderSuccess($CaptchaService->create());
    }

    /**
     * 发送短信验证码
     * @return Json
     * @throws BaseException
     */
    public function sendSmsCaptcha()
    {
        $SmsCaptchaService = new SmsCaptchaService;
        if ($SmsCaptchaService->handle($this->postData())) {
            return $this->renderSuccess('发送成功，请注意查收');
        }
        return $this->renderError($SmsCaptchaService->getError() ?: '短信发送失败');
    }
}