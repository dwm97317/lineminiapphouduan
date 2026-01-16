<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\store\shop\Setting;
use app\api\model\User as UserModel;
use app\api\model\store\shop\ShopApply;
use app\api\model\store\shop\Clerk as ClerkModel;
use app\api\model\store\Shop as ShopModel;
use app\api\model\store\shop\Capital;
/**
 * 仓管中心
 * Class Dealer
 * @package app\api\controller\user
 */
class Worker extends Controller
{
    /* @var \app\api\model\User $user */
    private $user;

    private $dealer;
    private $setting;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 用户信息
        $this->user = $this->getUser();
        // 仓库商用户信息
        $this->user = UserModel::detail($this->user['user_id']);
        // 分销商设置
        $this->setting = Setting::getAll();
    }

    // 推广链接
    public function inviteLink(){
        $user = $this->user;
        $str = $user['user_id'];
        $enkey = str_crypt($str);
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/html5/pages/login/index?key='.$enkey;
        return $this->renderSuccess($url);
    }

    /**
     * 加盟商中心
     * @return array
     */
    public function center()
    {
        return $this->renderSuccess([
            // 当前是否为加盟商
            'is_worker' => $this->WorkerUser(),
            // 当前用户信息
            'user' => $this->user,
            // 页面文字
            'words' => $this->setting['words']['values'],
            //加盟商设置
            'jmsetting' => $this->setting['basic']['values'],
        ]);
    }
    
    /**
     * 加盟商财务数据
     * @return array
     */
    public function shopdata(){
      $model = new ClerkModel;
      $ShopModel = new ShopModel();
      $Capital = new Capital;
      $shopclerk =$model::detail(['user_id'=> $this->user['user_id']]);
      $details = $ShopModel::detail($shopclerk['shop_id']);
    //   dump($details['income']);die;
      return $this->renderSuccess([
            // 当前是否为加盟商
            'total' => [
               'today' => $Capital->countIncome($shopclerk['shop_id'],'today'),
               'week' => $Capital->countIncome($shopclerk['shop_id'],'week'),
               'mouth' => $Capital->countIncome($shopclerk['shop_id'],'mouth'),
               'freeze_income' => $details['freeze_income'],
               'income' => $details['income'],
               'total_money' => $details['total_money'],
            ]
        ]);
    }
    
    
    /**
     * 提交分销商申请
     * @param string $name
     * @param string $mobile
     * @return array
     * @throws \think\exception\DbException
     */
    public function submit()
    {
        $model = new ShopApply;
        $data = $this->request->post();
        if ($model->submit($this->user, $data)) {
            return $this->renderSuccess('提交成功');
        }
        return $this->renderError($model->getError() ?: '提交失败');
    }
    
    
    /**
     * 获取仓库详情
     * @throws \think\exception\DbException
     */
    public function getShopDetail(){
        $model = new ClerkModel;
        $ShopModel = new ShopModel;
        $shopclerk =$model::detail(['user_id'=> $this->user['user_id']]);
        return $this->renderSuccess([
            // 分销商用户信息
            'is_worker' => $this->WorkerUser(),
            //仓库信息
            'shopDetail' => $ShopModel::detail($shopclerk['shop_id']),
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }
    
    /**
     * 保存仓库
     * @throws \think\exception\DbException
     */
    public function saveshop($shop_id){
         $ShopModel = ShopModel::detail($shop_id);
         if(!$ShopModel->edit($this->request->post())){
             return $this->renderError($ShopModel->getError() ?: '保存失败');
         }
         return $this->renderSuccess("保存成功");
    }

    /**
     * 分销商申请状态
     * @param null $referee_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function apply($referee_id = null)
    {
        // 推荐人昵称
        $referee_name = '平台';
        if ($referee_id > 0 && ($referee = UserModel::detail($referee_id))) {
            $referee_name = $referee['user']['nickName'];
        }
        return $this->renderSuccess([
            // 当前是否为分销商
            'is_worker' => $this->WorkerUser(),
            // 当前是否在申请中
            'is_applying' => ShopApply::isApplying($this->user['user_id']),
            // 推荐人昵称
            'referee_name' => $referee_name,
            // 背景图
            'background' => $this->setting['background']['values']['apply'],
            // 页面文字
            'words' => $this->setting['words']['values'],
            // 申请协议
            'license' => $this->setting['license']['values']['license'],
        ]);
    }

    /**
     * 分销商提现信息
     * @return array
     */
    public function withdraw()
    {
        $model = new ClerkModel;
        $ShopModel = new ShopModel;
        $shopclerk =$model::detail(['user_id'=> $this->user['user_id']]);
        return $this->renderSuccess([
            // 分销商用户信息
            'detail' => $ShopModel::detail($shopclerk['shop_id']),
            // 结算设置
            'settlement' => $this->setting['settlement']['values'],
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

    /**
     * 当前用户是否为分销商
     * @return bool
     */
    private function WorkerUser()
    {
        return !!$this->user && !$this->user['is_delete'] && $this->user['user_type']>0;
    }

}