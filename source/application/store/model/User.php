<?php

namespace app\store\model;

use app\common\model\User as UserModel;

use app\store\model\dealer\User as DealerUserModel;
use app\store\model\user\GradeLog as GradeLogModel;
use app\store\model\user\PointsLog as PointsLogModel;
use app\store\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\user\balanceLog\Scene as SceneEnum;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;
use app\common\library\helper;
use app\store\model\UserAddress;
use app\store\model\Package;
use app\store\model\Inpack;
use app\store\model\Setting as SettingModel;
/**
 * 用户模型
 * Class User
 * @package app\store\model
 */
class User extends UserModel
{
    /**
     * 获取当前用户总数
     * @param null $day
     * @return int|string
     * @throws \think\Exception
     */
    public function getUserTotal($day = null)
    {
        $startTime = strtotime($day);
        $filter=[
            'is_delete' =>0
        ];
        if (!is_null($day)) {
            $filter['create_time'] = ["between time",[$startTime, $startTime + 86400]];
        }
        return $this->where($filter)->count();
    }

    /**
     * 获取用户列表
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($nickName = '', $gender = -1, $grade = null,$user_code='',$user_id='')
    {
        // 检索：微信昵称
        !empty($nickName) && $this->where('nickName', 'like', "%$nickName%");
        
        // 检索：微信昵称
        !empty($user_code) && $this->where('user_code', 'like', "%$user_code%");
        !empty($user_id) && $this->where('user_id', 'like', "%$user_id%");
        // 检索：性别
        if ($gender !== '' && $gender > -1) {
            $this->where('gender', '=', (int)$gender);
        }
        // 检索：会员等级
        $grade > 0 && $this->where('grade_id', '=', (int)$grade);
        // 获取用户列表
        $inpack = new Inpack();
        $package = new Package();
        return $this->with(['grade','service','usermark'])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ])
            ->each(function($item, $key) use (&$inpack,&$package) {
                $tiems = $inpack->where('member_id',$item['user_id'])->order('created_time desc')->limit(1)->value('created_time');
               
               if(!empty($tiems)){
                   $item['update_time'] = strtotime($tiems);
               }else{
                   $item['update_time']=946656000;
               }
                // dump($item);die;
            });
    }
    
    public function add($data){
       // 表单验证
      if (!$this->onValidate($data)) return false;
       $setting = SettingModel::getItem('store');
    //   dump($setting);die;
       // 保存数据
       $data['paytype'] = $setting['moren']['user_pack_in_pay'];
       // 保存数据
       $data['wxapp_id'] = self::$wxapp_id;
       $data['create_time'] = time();
       $data['update_time'] = time();
       $data['open_id'] =$data['mobile'];
       $data['password'] = yoshop_hash($data['password']);
       if($this->allowField(true)->save($data)){
           return true;
       }
       return false;
    }
    
    public function edit($data){
       // 表单验证
     
       if(!empty($data['user']['user_code'])){
           $usercode = $this->where('user_code', '=',$data['user']['user_code'])->where('user_id','<>',$data['user_id'])->find();
           if (!empty($usercode)){
              $this->error = '用户编号已存在，请更换编号';
              return false;
           }  
       }
        // dump($data);die;
       // 保存数据
       $data['user']['update_time'] = time();
       if($this->allowField(true)->save($data['user'])){
           return true;
       }
       return false;
    }
    
    public function onValidate($data){
      if (!isset($data['password']) || empty($data['password'])) {
         $this->error = '请输入密码';
         return false;
      }
      if (!isset($data['password_confirm']) || empty($data['password_confirm'])) {
        $this->error = '请输入确认密码';
        return false;
      }
      if($data['password'] != $data['password_confirm']){
          $this->error = '两次输入密码不一致';
          return false;
      }
      $usercode = $this->where('user_code', '=',$data['user_code'])->find();
      if (!empty($usercode)){
          $this->error = '用户编号已存在，请更换编号';
          return false;
      }
      $usermobile = $this->where('mobile', '=',$data['mobile'])->find();
      if (!empty($usermobile)){
          $this->error = '手机号已存在，请更换手机号';
          return false;
      }
      
      if(!empty($data['email'])){
          $useremail = $this->where('email', '=',$data['email'])->find();
          if (!empty($useremail)){
              $this->error = '邮箱已存在，请更换邮箱';
              return false;
          }
      }
      
      return true;
    }
    
    
    /**
     * 获取用户列表
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getFunList($nickName = '', $user_code = '', $user_id = '')
    {
        // 检索：微信昵称
        !empty($nickName) && $this->where('nickName', 'like', "%$nickName%");
        // 检索：用户id
        !empty($user_code) && $this->where('user_code', '=', $user_code);
        // 检索：用户code
        !empty($user_id) && $this->where('user_id', '=', $user_id);
            // dump($code);die;
        // 获取用户列表
        return $this->with(['grade'])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 删除用户
     * @return bool|mixed
     */
    public function setDelete()
    {
        // 判断是否为分销商
        if (DealerUserModel::isDealerUser($this['user_id'])) {
            $this->error = '当前用户为分销商，不可删除';
            return false;
        }
      
        return $this->transaction(function () {
            // 删除用户推荐关系
            (new DealerUserModel)->onDeleteReferee($this['user_id']);
            // 标记为已删除
            return $this->save(['is_delete' => 1]);
        });
    }

    /**
     * 设置用户类型
     */
    public function setUserType($userId,$type){
       return $this->where(['user_id'=>$userId])->update(['user_type'=>$type]);
    }  

    /**  
     * 用户充值
     * @param string $storeUserName 当前操作人用户名
     * @param int $source 充值类型
     * @param array $data post数据
     * @return bool
     */
    public function recharge($storeUserName, $source, $data)
    {
        if ($source == 0) {
            return $this->rechargeToBalance($storeUserName, $data['balance']);
        } elseif ($source == 1) {
            return $this->rechargeToPoints($storeUserName, $data['points']);
        }
        return false;
    }

    /**
     * 用户充值：余额
     * @param $storeUserName
     * @param $data
     * @return bool
     */
    private function rechargeToBalance($storeUserName, $data)
    {
        if (!isset($data['money']) || $data['money'] === '' || $data['money'] < 0) {
            $this->error = '请输入正确的金额';
            return false;
        }
        // 判断充值方式，计算最终金额
        if ($data['mode'] === 'inc') {
            $diffMoney = $data['money'];
        } elseif ($data['mode'] === 'dec') {
            $diffMoney = -$data['money'];
        } else {
            $diffMoney = helper::bcsub($data['money'], $this['balance']);
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney) {
            // 更新账户余额
            $this->setInc('balance', $diffMoney);
            // 新增余额变动记录
            BalanceLogModel::add(SceneEnum::ADMIN, [
                'user_id' => $this['user_id'],
                'money' => $diffMoney,
                'remark' => $data['remark'],
                'sence_type' => 1,
            ], [$storeUserName]);
        });
        return true;
    }

    /**
     * 用户充值：积分
     * @param $storeUserName
     * @param $data
     * @return bool
     */
    private function rechargeToPoints($storeUserName, $data)
    {
        if (!isset($data['value']) || $data['value'] === '' || $data['value'] < 0) {
            $this->error = '请输入正确的积分数量';
            return false;
        }
        // 判断充值方式，计算最终积分
        if ($data['mode'] === 'inc') {
            $diffMoney = $data['value'];
        } elseif ($data['mode'] === 'dec') {
            $diffMoney = -$data['value'];
        } else {
            $diffMoney = $data['value'] - $this['points'];
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney) {
            // 更新账户积分
            $this->setInc('points', $diffMoney);
            // 新增积分变动记录
            PointsLogModel::add([
                'user_id' => $this['user_id'],
                'value' => $diffMoney,
                'describe' => "后台管理员 [{$storeUserName}] 操作",
                'remark' => $data['remark'],
            ]);
        });
        return true;
    }
    
     /**
     * 修改用户密码
     * @param $data
     * @return mixed
     */
    public function reset()
    {
        return $this->save(['password' => yoshop_hash('123456')]);
    }

    /**
     * 修改用户等级
     * @param $data
     * @return mixed
     */
    public function updateGrade($data)
    {
        // 变更前的等级id
        $oldGradeId = $this['grade_id'];
        return $this->transaction(function () use ($oldGradeId, $data) {
            // 更新用户的等级
            $status = $this->save(['grade_id' => $data['grade_id']]);
            // 新增用户等级修改记录
            if ($status) {
                (new GradeLogModel)->record([
                    'user_id' => $this['user_id'],
                    'old_grade_id' => $oldGradeId,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => ChangeTypeEnum::ADMIN_USER,
                    'remark' => $data['remark']
                ]);
            }
            return $status !== false;
        });
    }

    /**
     * 消减用户的实际消费金额
     * @param $userId
     * @param $expendMoney
     * @return int|true
     * @throws \think\Exception
     */
    public function setDecUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->setDec('expend_money', $expendMoney);
    }
    
    /**
     * 获取用户列表和地址
     * @param string $nickName 昵称
     * @param int $gender 性别
     * @param int $grade 会员等级
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getListAddress($query)
    {
        !empty($query) && $this->setListQueryWhere($query);
    
        // 获取用户列表 - 使用 Db 查询避免触发模型访问器
        return \think\Db::name('user_address')->alias('ad')
            ->join('user u','u.user_id = ad.user_id','left')
            ->where('ad.address_type',0)
            ->where('ad.wxapp_id',self::$wxapp_id)
            ->field('u.user_id,u.user_code,u.nickName,ad.*')
            ->order('ad.address_id', 'desc')
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
     public function setListQueryWhere($query){

        if(!empty($query['search'])){
            // if(is_numeric($query['search'])){
            //     (new UserAddress())->where('ad.user_id','=',$query['search']);
            // }else{
               (new UserAddress())->where('ad.user_id|u.nickName|u.user_code','like','%'.$query['search'].'%');
            // }
        }
        return $this;
    }

}
