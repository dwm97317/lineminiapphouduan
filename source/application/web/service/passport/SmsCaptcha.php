<?php
declare (strict_types=1);

namespace app\web\service\passport;

use think\Cache;
use yiovo\captcha\facade\Captchaweb;
use app\web\validate\passport\SmsCaptcha as ValidateSmsCaptcha;
use app\common\service\Basics;
use app\common\service\Message as MessageService;


/**
 * 服务类：发送短信验证码
 * Class SmsCaptcha
 * @package app\web\service\passport
 */
class SmsCaptcha extends Basics
{
    // 最大发送次数，默认10次
    protected $sendTimes = 10;

    // 发送限制间隔时间，默认24小时
    protected $safeTime = 86400;

    /**
     * 发送短信验证码
     * @param array $data
     * @return bool
     * @throws BaseException
     */
    public function handle(array $data): bool
    {
        // 数据验证
        $this->validate($data);
        $data = $data['form'];
        // 执行发送短信
        if (!$this->sendCaptcha($data['mobile'])) {
            return false;
        }
        return true;
    }

    /**
     * 执行发送短信
     * @param string $mobile
     * @return bool
     */
    private function sendCaptcha(string $mobile): bool
    {
        // 缓存发送记录并判断次数
        if (!$this->record($mobile)) {
            return false;
        }
        // 生成验证码
        $smsCaptcha = $this->getSmsCode(6);
        Cache::set('smscode',$smsCaptcha);
        // 发送短信
        MessageService::sendSms('password.login', [
            'code' => $smsCaptcha,
            'mobile' => $mobile,
        ],10001);
        return true;
    }
    
    
    private function getSmsCode($length){
        $min = pow(10 , ($length - 1));
        $max = pow(10, $length) - 1;
        return rand($min, $max);  
    }
    
    /**
     * 记录短信验证码发送记录并判断是否超出发送限制
     * @param string $mobile
     * @return bool
     */
    private function record(string $mobile): bool
    {
        // 获取发送记录缓存
        $record = Cache::get("sendCaptchaSMS.$mobile");
        Cache::set("sendCaptchaSMS.$mobile",0);
        // 写入缓存:记录剩余发送次数
        if (empty($record)) {
            Cache::set("sendCaptchaSMS.$mobile", ['times' => $this->sendTimes - 1], $this->safeTime);
            return true;
        }
        // 判断发送次数是否合法
        if ($record['times'] <= 0) {
            $this->error = '很抱歉，已超出今日最大发送次数限制';
            return false;
        }
        // 发送次数递减
        Cache::set("sendCaptchaSMS.$mobile", ['times' => $record['times'] - 1]);
        return true;
    }

    /**
     * 数据验证
     * @param array $data
     * @throws BaseException
     */
    private function validate(array $data)
    {
        // 数据验证
        $validate = new ValidateSmsCaptcha;
        $data = $data['form'];
        if (!$data['mobile']){
            throw new \Exception('mobile为必填项'); 
        }
        if (!$validate->check($data)) {
            return $validate->getError();
        }
    }
}