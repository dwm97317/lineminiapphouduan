<?php

namespace app\api\controller\shop;

use app\api\controller\Controller;
use app\api\model\store\shop\Setting;
use app\api\model\store\Shop as ShopModel;
use app\api\model\store\shop\Clerk as ClerkModel;
/**
 * 我的团队
 * Class Order
 * @package app\api\controller\user\dealer
 */
class Clerk extends Controller
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
        // 分销商用户信息
        // $this->shop = ClerkModel::detail(['user_id',$this->user['user_id']])['id'];
        // 分销商设置
        $this->setting = Setting::getAll();
    }
    
    
    /**
     * 我的团队详情
     * @param int $level
     * @return array
     * @throws \think\exception\DbException
     */
    public function getclerkdetail($id){
         $model = new ClerkModel;
         $detail =$model::detail(['clerk_id'=> $id]);
         $cherk = explode(',',$detail['clerk_type']);
         $cherkAr = [
             0 => ['value'=>1,'text'=>'入库员','is_pre' => false],
             1 => ['value'=>2,'text'=>'分拣员','is_pre' => false],
             2 => ['value'=>3,'text'=>'打包员','is_pre' => false],
             3 => ['value'=>4,'text'=>'签收员','is_pre' => false],
             4 => ['value'=>5,'text'=>'仓管员','is_pre' => false],
         ];
         foreach ($cherkAr as $key => $value) {
            if(in_array($value['value'],$cherk)){
                $cherkAr[$key]['is_pre'] = true;
            }
         }
         $detail['cherkAr'] = $cherkAr;
         return $this->renderSuccess($detail);
    }
    
    /**
     * 编辑员工
     * @param int $level
     * @return array
     * @throws \think\exception\DbException
     */
    public function editclerk($clerk_id){
         $model = ClerkModel::detail($clerk_id);
         $data = $this->request->post();
         foreach ($data['cherkAr'] as $key =>$val){
             if($val['is_pre']==1){
                 $clerk_type[$key] = $val['value'];
             }
         }
         $data['clerk_type'] = implode(',',$clerk_type);
        if(!$model->edit($data)){
            return $this->renderError($model->getError() ?: '保存失败');
        }
         return $this->renderSuccess("保存成功");
    }

    /**
     * 我的团队列表
     * @param int $level
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $model = new ClerkModel;
        $shopclerk =$model::detail(['user_id'=> $this->user['user_id']]);
        $list = $model->getList($shopclerk['shop_id']);
        return $this->renderSuccess([
            // 我的团队列表
            'list' => $list,
            // 基础设置
            'setting' => $this->setting['basic']['values'],
            // 页面文字
            'words' => $this->setting['words']['values'],
        ]);
    }

}