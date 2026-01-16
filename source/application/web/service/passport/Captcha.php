<?php
declare (strict_types=1);

namespace app\web\service\passport;

use app\common\service\Basics;
use yiovo\captcha\facade\Captchaweb;

class Captcha extends Basics
{
    /**
     * 图形验证码
     * @return array
     */
    public function create(): array
    {
        $data = Captchaweb::create();
        return [
            'base64' => str_replace("\r\n", '', $data['base64']),
            'key' => $data['key'],
            'md5' => $data['md5']
        ];
    }
}