<?php
declare (strict_types=1);

namespace app\api\controller;

use think\Cache;
use think\response\Json;
use app\api\service\passport\{Captcha as CaptchaService, SmsCaptcha as SmsCaptchaService};
use cores\exception\BaseException;
use app\common\service\Email;
/**
 * 验证码管理
 * Class Cart
 * @package app\api\controller
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
    
    /**
     * 发送邮箱验证码
     * @return Json
     * @throws BaseException
     */
    public function sendEmailCaptcha()
    { 
        $Email = new Email;
        $data = $this->postData();
        $code = $this->getSmsCode(6);
        Cache::set('emailcode',$code);
        // dump($data['form']['email']);die;
        if ($Email->sendEmailCaptcha($data['form']['email'],$code,$type=2)) {
            return $this->renderSuccess('','发送成功，请注意查收');
        }
        return $this->renderError($Email->getError() ?: '短信发送失败');
    }
    
    private function getSmsCode($length){
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);  
    }
}