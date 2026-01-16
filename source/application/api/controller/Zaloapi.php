<?php
namespace app\api\controller;
/**
 * zaloApi 控制器
 * */
class Zaloapi {
    
    //从zalo获取accessToken
    public function getZaloAccessToken(){
        file_put_contents('debug.log',var_dump(1111));
    }
    
}