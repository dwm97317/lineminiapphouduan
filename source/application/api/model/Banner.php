<?php
namespace app\api\model;
use app\common\model\Banner as BannerModel;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Banner extends BannerModel
{
    protected $updateTime = false;

    //小程序端轮播图
    public function queryPage(){
        return $this
        ->with('image')
        ->where(['status'=>1,'banner_site'=> 10])
        ->order('sort DESC')
        ->field('title,image_id,redirect_type,url,banner_site')
        ->limit(6)->select();
    }
    
    //小程序端广告图
    public function adviseBanner(){
        return $this
        ->with('image')
        ->where(['status'=>1,'banner_site'=> 20])
        ->order('sort DESC')
        ->field('title,image_id,redirect_type,url,banner_site')
        ->limit(6)->select();
    }
    
      //弹窗公告图
    public function noticeBanner(){
        return $this
        ->with('image')
        ->where(['status'=>1,'banner_site'=> 40])
        ->field('id,title,image_id,redirect_type,url,banner_site')
        ->order('created_time','asc')
        ->limit(10)->select();
    }
    
    public function sharpBanner(){
        return $this
        ->with('image')
        ->where(['status'=>1,'banner_site'=> 30])
        ->order('sort DESC')
        ->field('title,image_id,redirect_type,url,banner_site')
        ->limit(6)
        ->select();
    }
}
