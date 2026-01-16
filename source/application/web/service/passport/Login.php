<?php
declare (strict_types=1);

namespace app\web\service\passport;

use think\Cache;
use app\web\model\{User as UserModel, Setting as SettingModel};
use app\common\service\Basics;
use app\common\enum\Setting as SettingEnum;
use think\Session;

/**
 * 服务类：用户登录
 * Class Login
 * @package app\web\service\passport
 */
class Login extends Basics
{
    /**
     * 用户信息 (登录成功后才记录)
     * @var UserModel|null $userInfo
     */
    private $userInfo;

    // 用于生成token的自定义盐
    const TOKEN_SALT = 'user_salt';

    /**
     * 执行用户登录
     * @param array $data
     * @return bool
     * @throws BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function loginForm(array $data): bool
    {
        // 数据验证
        if (!$this->validate($data,'login')){
          return false;
        }
        if (!$this->checkLogin($data)){
            return false;
        } 
        $wxAppid = (new UserModel())->getWxappid();
        if ($this->userInfo['wxapp_id']!=$wxAppid){
            $this->error = '账号不存在';
            return false; 
        }
        return $this->loginState($this->userInfo);
    }

    public function registerForm($data){
        // 数据验证
        if (!$this->validate($data,'register')){
             return false;
        }
        $data['isParty'] = false;
        $data['partyData'] = [];
        $this->register($data);
        return $this->loginState($this->userInfo);
    }
    
    /**
     * 检查登录状态
     */
    public function checkLogin($data){
        $UserModel = new UserModel();
        $result = $UserModel->where(['mobile'=>$data['account']])->whereOr(['email'=>$data['account']])->where('is_delete',0)->find();
    //   dump($result);die;
        if(!$result){
            $this->error = '用户名或密码错误';
            return false;  
        }
        if ($result['password'] != yoshop_hash($data['password'])){
            $this->error = '用户名或密码错误';
            return false; 
        }
        $this->userInfo = $result;
        return true;
    }

    // /**
    //  * 记录登录态
    //  * @return bool
    //  * @throws BaseException
    //  */
    // private function setSession(): bool
    // {
    //     empty($this->userInfo) && throwError('未找到用户信息');
    //     // 登录的token
    //     $token = $this->getToken($this->getUserId());
    //     // 记录缓存, 30天
    //     Cache::set($token, [
    //         'user' => $this->userInfo,
    //         'openid' => $this->userInfo->mobile,
    //         'store_id' => (new UserModel)->getWxappid(),
    //         'is_login' => true,
    //     ], 86400 * 30);
    //     return true;
    // }
    
    /**
     * 保存登录状态
     * @param $user
     * @throws \think\Exception
     */
    public function loginState($user)
    {
        // 保存登录状态
        Session::set('yoshop_user', [
            'user' => [
                'user_id' => $user['user_id'],
                'user_name' => $user['nickName'],
                'mobile'=>$user['mobile'],
                'avatarUrl' => $user['avatarUrl'],
                'user_code'=>isset($user['user_code'])?$user['user_code']:'',
                'email'=>$user['email'],
                'gender'=>$user['gender'],
            ],
            'is_login' => true,
        ]);
        return true;
    }

    /**
     * 数据验证
     * @param array $data
     * @return void
     * @throws BaseException
     */
    private function validate(array $data,$scene='login'): bool
    {
        // 数据验证
        if ($scene == 'login'){
            if (!isset($data['account']) || !isset($data['password']) || $data['account'] == '' || $data['password'] == ''){
                $this->error = '请填写用户名或密码';
                return false;
            } 
        }
        if ($scene == 'register'){
          if ($data['username'] == '' || $data['password'] == '' || $data['mobile'] == ''){
              $this->error = '请填写表单信息';
              return false;
          } 
          if ($data['password'] != $data['password_cfd']){
              $this->error = '两次输入密码不一致';
              return false;
          }
        //   if(!is_mobile($data['mobile'])){
        //       $this->error = '请输入正确的手机号码';
        //       return false;
        //   }
          $UserModel = new UserModel();
          $res = $UserModel->where(['mobile'=>$data['mobile']])->find();
          if ($res){
              $this->error = '该手机号已注册';
              return false;
          } 
        }
        // 验证短信验证码是否匹配
        // $smsCode = Cache::get('smscode');
        // if ($smsCode!=$data['smsCode']) {
        //     throwError('短信验证码不正确');
        // }
        return true;
    }

    /**
     * 当前登录的用户信息
     */
    public function getUserInfo(): ?UserModel
    {
        return $this->userInfo;
    }

    /**
     * 当前登录的用户ID
     * @return int
     */
    private function getUserId(): int
    {
        return (int)$this->getUserInfo()['user_id'];
    }

    /**
     * 自动登录注册
     * @param array $data
     * @return void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function register(array $data): void
    {
        // 查询用户是否已存在
        // 用户存在: 更新用户登录信息
        $userInfo = UserModel::detail(['mobile' => $data['mobile']]);
        if ($userInfo) {
            $this->updateUser($userInfo, $data['isParty'], $data['partyData']);
            return;
        }
        $refereeId = 0;
        if (isset($data['refferid'])){
            $refereeId = str_crypt($data['refferid'],'DECODE');
        }
        // $data['partyData']['refereeId'] = $refereeId;
        // 用户不存在: 创建一个新用户
        $this->createUser($data, $data['isParty'], $data['partyData']);
    }

  /**
     * 新增用户
     * @param string $mobile 手机号
     * @param bool $isParty 是否存在第三方用户信息
     * @param array $partyData 用户信息(第三方)
     * @return void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function createUser($data,$isParty, array $partyData = []): void
    {
        // 用户信息
        $data = [
            'mobile' => $data['mobile'],
            'nickName' => $data['username']??'',
            'gender'=>0,
            'avatarUrl' => '',
            'platform' => "PC",
            'open_id' => $data['mobile'],
            'password' => yoshop_hash($data['password']),
            'email' => $data['email']??'',
            'last_login_time' => time(),
            'wxapp_id' => $data['wxapp_id'],
        ];
        if (isset($partyData['refereeId'])){
            $data['referee_id'] = $partyData['refereeId'];
            $refereeId = $partyData['refereeId'];
        }
        // 写入用户信息(第三方)
        if ($isParty === true && !empty($partyData)) {
            $partyUserInfo = PartyService::partyUserInfo($partyData, true);
            $data = array_merge($data, $partyUserInfo);
        }
        // 新增用户记录
        $model = new UserModel;
        $status = $model->save($data);
        // 记录用户信息
        $this->userInfo = $model;
        // 记录推荐人关系
    }

    /**
     * 更新用户登录信息
     * @param UserModel $userInfo
     * @param bool $isParty 是否存在第三方用户信息
     * @param array $partyData 用户信息(第三方)
     * @return void
     */
    private function updateUser(UserModel $userInfo, $isParty, array $partyData = []): void
    {
        // 用户信息
        $data = [
            'last_login_time' => time(),
            'wxapp_id' => (new UserModel)->getWxappid()
        ];
        // 写入用户信息(第三方)
        // 如果不需要每次登录都更新微信用户头像昵称, 下面4行代码可以屏蔽掉
        if ($isParty === true && !empty($partyData)) {
            $partyUserInfo = PartyService::partyUserInfo($partyData, true);
            $data = array_merge($data, $partyUserInfo);
        }
        // 更新用户记录
        $status = $userInfo->save($data) !== false;
        // 记录用户信息
        $this->userInfo = $userInfo;
    }


    /**
     * 获取登录的token
     * @param int $userId
     * @return string
     */
    public function getToken(int $userId): string
    {
        static $token = '';
        if (empty($token)) {
            $token = $this->makeToken($userId);
        }
        return $token;
    }

    /**
     * 生成用户认证的token
     * @param int $userId
     * @return string
     */
    private function makeToken(int $userId): string
    {
        $storeId = (new UserModel)->getWxappid();
        // 生成一个不会重复的随机字符串
        $guid = get_guid_v4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = self::TOKEN_SALT;
        return md5("{$storeId}_{$timeStamp}_{$userId}_{$guid}_{$salt}");
    }
}