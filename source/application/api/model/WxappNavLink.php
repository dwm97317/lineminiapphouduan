<?php

namespace app\api\model;
use app\common\model\wxapp\Navlink;

/**
 * 小程序导航
 * Class Wxapp/nav
 * @package app\store\model
 */
class WxappNavLink extends Navlink
{
    public function getList(){
        return $this
        ->with('image')
        ->where('is_use',0)
        ->order('sort','asc')
        ->select();
        ;
    }
}
