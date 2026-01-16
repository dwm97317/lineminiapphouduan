<?php
namespace app\store\controller;

use app\store\model\store\User as StoreUser;
use think\Session;
use app\store\model\Countries;
use app\api\controller\Page;
use app\store\model\Line;
use app\common\model\UpdateLog;
use app\common\model\ApiPost;
use app\common\model\Logistics;
use app\store\model\Setting as SettingModel;
/**
 * 工具功能
 * Class Passport
 * @package app\store\controller
 */
class Tools extends Controller
{
    
    public function index()
    {
        return $this->fetch('index');
    }
    
    public function seachfree()
    {
        $country = (new Countries())->getListAllCountry();
        $data = input();
        if($data){
            $list = getsearchfree($data);
            $list=  json_encode($list);
            $list=  json_decode($list,true); 
          
        }else{
            $list = [];
            $list=  json_encode($list);
            $list=  json_decode($list,true); 
        }
        return $this->fetch('searchfree',compact('country','list'));
    }
    
    //获取更新日志
    public function updatelog(){
        $UpdateLog = new UpdateLog;
        $list = $UpdateLog->getList();
        return $this->fetch('updatelog',compact('list'));
    }
    
    public function apipost(){
        $ApiPost = new ApiPost;
        $list = $ApiPost->getList();
        return $this->fetch('apipost',compact('list'));
    }
    

}
