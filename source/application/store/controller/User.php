<?php
namespace app\store\controller;

use app\store\model\User as UserModel;
use app\store\model\user\Grade as GradeModel;
use app\store\model\Line;
use app\store\model\user\UserLine;
use app\store\model\Coupon;
use app\store\model\UserCoupon;
use app\store\model\Setting;
use app\store\model\store\shop\Clerk;
use app\store\model\user\UserMark as UserMarkModel;
/**
 * 用户管理
 * Class User
 * @package app\store\controller
 */
class User extends Controller
{
    /**
     * 用户列表
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index($nickName = '', $gender = null, $grade = null,$user_code = '',$user_id= '')
    {
        $model = new UserModel;
        $list = $model->getList($nickName, $gender, $grade,$user_code,$user_id);
        // 会员等级列表
        $gradeList = GradeModel::getUsableList();
        //获取设置
        $set = Setting::detail('store')['values']['usercode_mode'];
        // dump($list->toArray());die;
        // 可以分发的优惠券
        $coupon = (new Coupon())->getAllList();
        $line = (new Line())->getListAll();
        return $this->fetch('index', compact('list', 'gradeList','line','coupon','set'));
    }
    /**
     * 获取用户信息
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function findUser($member_id)
    {
        // 用户详情
        $model = UserModel::detail($member_id);
        if(!empty($model)){
            return $this->renderSuccess('获取成功','',$model);
        }
         return $this->renderError('找不到该ID的用户');
    }

    
    
    
    /**
     * 新增用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function addUSer()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('user/user/add');
        }
        $data = $this->request->param();
        if(empty($data['user']['password']) || empty($data['user']['password_confirm'])){
            return $this->renderError('请输入密码和确认密码');
        }
        if($data['user']['password'] != $data['user']['password_confirm']){
            return $this->renderError('两次输入密码不一致');
        }
        $model = new UserModel;
        if ($model->add($data['user'])) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 编辑用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function edit($user_id)
    {
        $model = new UserModel;
        $Clerk = new Clerk;
        $detail = $model::detail($user_id);
        // dump($detail);die;
        $set = Setting::detail('store')['values']['usercode_mode'];
        $service = $Clerk->where('FIND_IN_SET(:ids,clerk_type)', ['ids' => 7])->select();
        // dump($service);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('user/user/edit',compact('detail','set','service'));
        }
        $param = $this->request->param();
        if($detail->edit($param)){
            return $this->renderSuccess('重置成功',url('index'));
        }
        return $this->renderError($detail->getError() ?: '重置失败');
    }

    /**
     * 删除用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function reset()
    {
        // 用户详情
        $model = UserModel::detail(input('id'));
        if ($model->reset()) {
            return $this->renderSuccess('重置成功');
        }
        return $this->renderError($model->getError() ?: '重置失败');
    }
    
    /**
     * 删除用户
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }


    /**
     * 用户充值
     * @param $user_id
     * @param int $source 充值类型
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function recharge($user_id, $source)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        if ($model->recharge($this->store['user']['user_name'], $source, $this->postData('recharge'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 修改会员等级
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function grade($user_id)
    {
        // 用户详情
        // dump($this->postData('grade'));die;
        $model = UserModel::detail($user_id);
        if ($model->updateGrade($this->postData('grade'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }
    
    /**
     * 新增会员唛头
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function usermark($user_id)
    {
        // 用户详情
        $model = new UserMarkModel;
        $param = $this->postData('mark');
        $param['user_id'] = $user_id;
        if(empty($param['mark'])){
            return $this->renderError('请输入唛头');
        }
        $marks = $model->where('user_id',$user_id)->where('mark',$param['mark'])->find();
        if(!empty($marks)){
            return $this->renderError('唛头已存在');
        }
        if (UserMarkModel::add($param)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError('操作失败');
    }
    
    /**
     * 获取用户唛头信息
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function marklist(){
       $model = new UserMarkModel;
    //   dump($this->request->param());die;
       $list = $model->getList($this->request->param());
      
       //获取设置
        $set = Setting::detail('store')['values']['usercode_mode'];
       return $this->fetch('user/usermark/index', compact('list','set'));
    }
    
    public function findUserMark(){
        $model = new UserMarkModel;
        $param = $this->request->param();
        $list = $model->getList($param);
        return $this->renderSuccess('操作成功','',compact('list'));
    }
    
    public function findusercode(){
        $model = new UserMarkModel;
        $UserModel = new UserModel;
        $param = $this->request->param();
        $user = $UserModel->where(['user_code'=>$param['member_id'],'is_delete'=>0])->find();
        $list = [];
        if(!empty($user)){
             $param['member_id'] = $user['user_id'];
             $list = $model->getList($param);
        }
       
        return $this->renderSuccess('操作成功','',compact('list'));
    }
    
     /**
     * 删除会员唛头
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function deletemark($id)
    {
         $model = (new UserMarkModel());
         $res = $model->where('id',$id)->delete();
         if ($res) {
            return $this->renderSuccess('删除成功');
         }
         return $this->renderError($model->getError() ?: '删除失败');
    }
    
    
    /**
     * 获取用户信息
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function userDetail($user_id)
    {
        // 用户详情
        $model = UserModel::detail($user_id);
        return $this->renderSuccess($model);
    }
    
     /**
     * 修改会员路线折扣
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function discount($user_id)
    {
         $model = (new UserLine());
         $data = $this->postData('line');
         $data['user_id'] = $user_id;
        if ($model->addUserLineDiscount($data)) {
            return $this->renderSuccess('设置成功');
        }
        return $this->renderError($model->getError() ?: '设置失败');
    }
    
    /**
     * 路线折扣列表
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function discountList()
    {
         $model = (new UserLine());
         $list = $model->getList($this->request->param());
        //  dump($list);die;
        return  $this->fetch('user/line/discount', compact('list'));
    }
    
    /**
     * 删除会员路线折扣
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function deletediscount($id)
    {
         $model = (new UserLine());
         $res = $model->where('id',$id)->delete();
         if ($res) {
            return $this->renderSuccess('删除成功');
         }
         return $this->renderError($model->getError() ?: '删除失败');
    }
    
    /**
     * 删除会员路线折扣
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function editdiscount()
    {
         $user_id = input('user_id');
         $discount = input('discount');
         $line_id = input('line_id');
         $model = (new UserLine());
         $res = $model->where('user_id',$user_id)->where('line_id',$line_id)->update(['discount'=>$discount]);
         if ($res) {
            return $this->renderSuccess('修改成功');
         }
         return $this->renderError($model->getError() ?: '修改失败');
    }
    
     /**
     * 修改会员code
     * @param $user_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function edituserCode()
    {

         $user_id = input('user_id');
         $user_code = input('user_code');
         $model = new UserModel;
         if(empty($user_code)){
             return $this->renderError($model->getError() ?: '用户Code不能为空'); 
         }
         $ress = $model->where('user_code',$user_code)->find();
         if ($ress) {
            return $this->renderError('用户Code重复，请填写其他Code'); 
         }
         
         $res = $model->where('user_id',$user_id)->update(['user_code'=>$user_code]);
             if ($res) {
                return $this->renderSuccess('修改成功'); 
             }else{
                return $this->renderError($model->getError() ?: '修改失败');
             } 
         return $this->renderError($model->getError() ?: '修改失败');
    }
    
     /**
     * 会员地址列表
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function address(){
      $model = new UserModel;
      $list = $model->getListAddress($this->request->param()); 
     //获取设置
      $set = Setting::detail('store')['values']['usercode_mode'];
      return $this->fetch('user/address/index', compact('list','set')); 
    }
    
    /**
     * 领取优惠券
     * @param $coupon_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function receive($coupon_id,$user_id)
    {
        $model = new UserCoupon();
        $user = explode(',',$user_id);
        if(!empty($user)){
            foreach ($user as $item){
                $res = $model->receive($item, $coupon_id);
            }
            if($res){
                return $this->renderSuccess('领取成功');
            }
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
        
    /**
     * 设置支付方式
     * @param $coupon_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function setpaytype(){
        $param = $this->request->param();
        $userid = explode(',',$param['user_id']);
        foreach ($userid as $val){
            $user = UserModel::detail($val);
            $user->save(['paytype'=>$param['paytype']]);
        }
        return $this->renderSuccess('批量设置成功');
    }

}
