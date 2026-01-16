<?php

namespace app\api\model\store\shop;

use app\common\exception\BaseException;
use app\common\model\store\shop\Clerk as ClerkModel;

/**
 * 商家门店店员模型
 * Class Clerk
 * @package app\api\model\store\shop
 */
class Clerk extends ClerkModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'is_delete',
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * 店员列表
     * @param $where
     * @return static
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public  function getList($shop_id)
    {
        return $this->with('user')
        ->where('shop_id',$shop_id)
        ->where('status',1)
        ->where('is_delete',0)
        ->select()
        ->each(function($item, $key){
           $item['clerk_type'] =  explode(',',$item['clerk_type']);
           $item['checkName'] = '';
           foreach ($item['clerk_type'] as $k => $v){
               switch ($v) {
                   case '1':
                       $item['checkName'] = $item['checkName'].'入库,';
                       break;
                   case '2':
                       $item['checkName'] = $item['checkName'].'分拣,';
                       break;
                   case '3':
                       $item['checkName'] = $item['checkName'].'打包,';
                       break;
                    case '4':
                       $item['checkName'] = $item['checkName'].'签收,';
                       break;
                    case '5':
                       $item['checkName'] = $item['checkName'].'仓管';
                       break;
                   default:
                       $item['checkName'] = $item['checkName'].'';
                       break;
               }
           }
           return $item;
        });
    }
    
    /**
     * 店员详情
     * @param $where
     * @return static
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    public static function detail($where)
    {
        /* @var static $model */
        $model = parent::detail($where);
        if (!$model) {
            return false;
            // throw new BaseException(['msg' => '未找到店员信息']);
        }
        return $model;
    }

    /**
     * 验证用户是否为核销员
     * @param $shop_id
     * @return bool
     */
    public function checkUser($shop_id)
    {
        if ($this['is_delete']) {
            $this->error = '未找到店员信息';
            return false;
        }
        if ($this['shop_id'] != $shop_id) {
            $this->error = '当前店员不属于该门店，没有核销权限';
            return false;
        }
        if (!$this['status']) {
            $this->error = '当前店员状态已被禁用';
            return false;
        }
        return true;
    }
    
    public function storage(){
        return $this->belongsTo('app\api\model\store\\Shop','shop_id')->field('shop_name,shop_id,province_id,city_id,region_id');
    }
    
    public function user(){
        return $this->belongsTo('app\api\model\User','user_id');
    }

}