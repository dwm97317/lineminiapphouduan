<?php
namespace app\api\controller;

use app\api\model\store\shop\Clerk;
use app\api\model\User as UserModel;
use app\api\model\dealer\Referee as RefereeModel;
use app\api\model\dealer\Capital;
use app\api\model\sharing\SharingUser;
use app\api\model\SiteSms as SiteSmsModel;
use app\common\library\wxl\WXBizDataCrypt;
use app\api\service\user\Oauth as OauthService;
use app\common\service\Email;
use app\api\model\Setting;
use think\Cache;
use app\api\model\Wxapp as WxappModel;
use app\api\model\UserCoupon;
/**
 * 用户管理
 * Class User
 * @package app\api
 */
class User extends Controller
{
    /**
     * 用户自动登录
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function login()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => $model->login($this->request->post()),
            'token' => $model->getToken()
        ]);
    }
    
    public function clerklogin()
    {
        $model = new UserModel;
        return $this->renderSuccess([
            'user_id' => $model->loginClerk($this->request->post()),
            'token' => $model->getToken()
        ]);
    }
    
    public function getSiteUrl(){
        // 
        $WxappModel = new WxappModel();
        $url = $_SERVER['HTTP_ORIGIN'];
        $wxappData  = WxappModel::useGlobalScope(false)->where('other_url','like','%'.$url.'%')->find();
            // dump($wxappData);die;
        if(empty($wxappData)){
            return $this->renderSuccess([
            'other_url' => base_url(),
            'wxapp_id' => 10001
            ]);
        }

        return $this->renderSuccess([
            'other_url' => $wxappData['other_url'],
            'wxapp_id' => $wxappData['wxapp_id']
        ]);
    }
    
    public function getCode(){
        $wxapp = WxappModel::getWxappCache();
        
        $redirectUri = base_url()."html5/pages/my/my";
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$wxapp['app_wxappid']."&redirect_uri=".$redirectUri."&response_type=code&scope=snsapi_userinfo&state=".$this->wxapp_id."#wechat_redirect";
        // dump($url);die;
       return $url;
    }

     public function loginwx(){
        $model = new UserModel;
        $data = $this->request->param();
        return $this->renderSuccess([
            'user_id' => $model->loginwx($data),
            'token' => $model->getToken()
        ]);
    }
    /**
     * 当前用户详情
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        // 当前用户信息
        $userInfo = $this->getUser();
        // dump($userInfo);die;
        $is_sharp_verify = (new SharingUser())->getVerify($userInfo['user_id']);
        if ($is_sharp_verify['status']==2){
            $userInfo['is_sharp'] = 3;
        }
        if ($is_sharp_verify['status']==3){
            $userInfo['is_sharp'] = 4;
        }
        $userInfo['sms'] = (new SiteSmsModel())->where('user_id',$userInfo['user_id'])->where('is_read',0)->count();
        $userInfo['coupon'] = (new UserCoupon())->where('user_id',$userInfo['user_id'])->where('is_use',0)->where('is_expire',0)->count();
        return $this->renderSuccess(compact('userInfo'));
    }
    
    /**
     * 更新用户资料
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function updateuser(){
        $userInfo = $this->getUser();
        $param = $this->request->param();
        if(empty($param['user_image_id'])){
            unset($param['user_image_id']);
        }
        if($userInfo->allowField(true)->save($param)){
             return $this->renderSuccess('更新成功');   
        }
        return $this->renderError('更新失败');  
    }
    
    //站内消息
    public function smslist(){
        $userInfo = $this->getUser();
        $model = new SiteSmsModel;
        $data = $model->getList(['member_id'=>$userInfo['user_id']]);
        foreach ($data as $key => $val){
            (new SiteSmsModel())->where('id',$val['id'])->update(['is_read'=>1]);
        }
        return $this->renderSuccess(compact('data'));
    }
    
    //更新用户昵称
    public function changeName(){
        $user = $this->getUser();
        $name = input('nickname');
        $res = $user->save(['nickName'=> $name]);
        if($res){
            return $this->renderSuccess('更新昵称成功');   
        }
         return $this->renderError('更新昵称失败');  
    }
    
    //更新用户手机号
    public function changephone(){
        $userInfo = $this->getUser();
        $userphone = input('userphone');
        $res = $userInfo->save(['mobile' => $userphone]);
        if($res){
            return $this->renderSuccess('添加成功');   
        }
        return $this->renderError('添加有误');  
    }
    
    //我的邮箱
    public function email(){
        $userInfo = $this->getUser();
        return $this->renderSuccess(compact('userInfo'));
    }
    
    //发送验证码
    public function emailCode(){
        $user = $this->getUser();
        $code = mt_rand(100000, 999999);

        $user['email'] =input('email');
        if(empty($user['email'])){
             return $this->renderError('请输入邮箱');  
        }
        $preg_email='/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims';
        if(!preg_match($preg_email,$user['email'])){
             return $this->renderError('邮箱格式不正确');  
        }
        $update['email_code'] = json_encode(['code'=>$code,'sendTime'=>time(),'email'=>$user['email']]);
        $res = (new UserModel())->where('user_id',$user['user_id'])->update($update);
        $data['code'] =$code;
        // {"code":93315,"sendTime":1653013053,"email":"1835504221@qq.com"}
        (new Email())->sendemail($user,$data,$type=2);
        return $this->renderSuccess('验证码发送成功');   
    }
    
    //验证验证码验证码
    public function UpdateMail(){
        $user = $this->getUser();
        $codeData = json_decode($user['email_code'],true);
        $code = input('code');
        if(empty($code)){
             return $this->renderError('请输入验证码');
        }
        if(time()-300<=$codeData['sendTime']){
            if($code && ($code==$codeData['code'])){
               $update['email_code'] = '';
               $update['email'] = $codeData['email'];
              (new UserModel())->where('user_id',$user['user_id'])->update($update);  
            } else{
                return $this->renderError('验证码不匹配');
            }
        }else{
            return $this->renderError('验证码超时');
        }
        $userInfo = $this->getUser();
        return $this->renderSuccess(compact('userInfo'));
    }
    
    
    //支付凭证提交
      public function certificate(){
         // 当前用户信息
         $userInfo = $this->getUser();
         $post = $this->postData();
      
         $cer = (new Certificate());
   
         if (!$cer->create($post)){
            return $this->renderError($cer->getError()??"提交失败");
         }  
         return $this->renderSuccess('提交成功');          
    }
    //支付凭证列表
    public function certificateLog(){
        $model = new Certificate;
        $cur = $this->postData('cur')[0];
        $userInfo = $this->getUser();
        $map = ['member_id'=>$userInfo['user_id']];
        if ($cur){
          $map['is_verify'] = $cur;
        }
        return $this->renderSuccess(
            $model->getList($map));
    }
    
    /**
     * 分销中心数据 
     * */
    public function dealer(){
        // 当前用户信息
        $userInfo = $this->getUser();
        $refferModel = (new RefereeModel());
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        
        // 一周内
        $weekStart = time()-(7*3600*60);
        $weekEnd = time();
        
        $capital = (new Capital());
        $total = [
            'today' => $capital->where('flow_type',10)->where(['user_id'=>$userInfo['user_id']])->whereBetween('create_time',[$beginToday,$endToday])->sum('money'),
            'week' =>$capital->where('flow_type',10)->where(['user_id'=>$userInfo['user_id']])->whereBetween('create_time',[$weekStart,$weekEnd])->sum('money'),
            'tatal' => $capital->where('flow_type',10)->where(['user_id'=>$userInfo['user_id']])->sum('money'),
            'today_user' => $refferModel->countRefferUser($userInfo['user_id'],'today'),
            'all_member' => $refferModel->countRefferUser($userInfo['user_id']),
            'income' => $userInfo['income'],
        ];
        return $this->renderSuccess(compact('total','userInfo'));
    }
    
    
    /**
     * 当前用户角色
     */
    public function role(){
         // 当前用户信息
         $userInfo = $this->getUser();
         switch($userInfo['user_type']){
             case 0:
              $role_name = '普通用户';
              break;
             case 1:
              $role_name = '入库员';
              break;
             case 2:
              $role_name = '分拣员';
              break;
             case 3:
              $role_name = '打包员';
              break;
             case 4:
              $role_name = '签收员';
              break; 
             case 5:
              $role_name = '仓管员';
              break;
             default:
              $role_name = '未知角色';
              break;  
         }
         $userRole['role_name'] = $role_name;
         $userRole['role_type'] = $userInfo['user_type'];
         $this->userRole = $userRole;
         return $this->renderSuccess(compact('userRole'));
    }
    
    // 用户余额 
    public function banlance(){
        // 当前用户信息
        $userInfo = $this->getUser();
        return $this->renderSuccess([
           'balance' => $userInfo['balance'],
           'income' => $userInfo['income'],
        ]);
    }
    
    /**
     * 用户余额变动明细
     * @return array
     * @throws \think\exception\DbException
     */
    public function balanceLog(){
        $userInfo = $this->getUser();
        $model = new \app\common\model\user\BalanceLog();
        
        // 获取查询参数
        $params = $this->request->param();
        $scene = isset($params['scene']) ? (int)$params['scene'] : -1;
        $page = isset($params['page']) ? (int)$params['page'] : 1;
        
        // 构建查询
        $query = $model->where('user_id', $userInfo['user_id']);
        
        // 根据scene参数筛选
        // scene=1 表示充值 (sence_type=1)
        // scene=2 表示消费 (sence_type=2)
        if ($scene === 1) {
            $query->where('sence_type', 1);
        } elseif ($scene === 2) {
            $query->where('sence_type', 2);
        }
        
        // 分页查询
        $list = $query->order(['create_time' => 'desc'])
            ->paginate(15, false, ['page' => $page]);
        
        return $this->renderSuccess([
            'list' => $list
        ]);
    }
    
    
    
    public function clerk(){
        $userInfo = $this->getUser();
        if ($userInfo['user_type'] == 0){
            return $this->renderError('您还没有成为员工');
        }
        $clerk_arr_map = [1 => 's',2 => 'f',3 => 'd',4 => 'q',5 => 'c',6=>'da',7=>'kf'];
        $info = (new Clerk())->where(['user_id'=>$userInfo['user_id'],'is_delete'=>0])->with(['storage','user'])->find()->toArray();
        $info['clerk_type_arr'] = [];
        $info['clerk_type_arr']['s'] = 0;
        $info['clerk_type_arr']['f'] = 0;
        $info['clerk_type_arr']['d'] = 0;
        $info['clerk_type_arr']['q'] = 0;
        $info['clerk_type_arr']['c'] = 0;
        $info['clerk_type_arr']['da'] = 0;
        $info['clerk_type_arr']['kf'] = 0;
        // dump($info['clerk_type']);die;
        if ($info['clerk_type']){
            $clerk_arr = explode(',',$info['clerk_type']);
            foreach ($clerk_arr as $v){
              
                $info['clerk_type_arr'][$clerk_arr_map[$v]] = 1;
            }
        }
        return $this->renderSuccess(compact('info'));
    }
    
    // 分享海报生成
    public function share(){
        $userInfo = $this->getUser();
        if ($userInfo['is_delete']==1){
            return $this->renderError('您已被系统删除,请咨询管理员');
        }
        $code = createCode($userInfo['user_id']);
        // 得到小程序分销码
        $wx_code =  Cache::get('wx_code_goods_id_'.$userInfo['user_id']);
        
        if (!$wx_code){
            $res = getWxCodeByMemberId($code,$userInfo['user_id']);
    
            if ($res){
                $wx_code = $res['data'];
            }
        }
        if (!file_exists($wx_code)){
            $res = getWxCodeByMemberId($code,$userInfo['user_id']);
            if ($res){
                $wx_code = $res['data'];
            }
        }

        if (!$wx_code){
            return $this->renderError('分享码生成失败');
        }
     
        $img = createShareImage($wx_code);
        if (!$img){
            return $this->renderError('分享图片生成失败');                     
        }
        $img = str_replace('uploads','',$img);
        return $this->renderSuccess(['src'=>$img,'shareData'=>['code'=>$code,'user_id'=>$userInfo['user_id']]]);
    }
    
    // 获取微信手机号码
    public function wechatMobile(){
        $encryptedData = urldecode(htmlspecialchars_decode($this->request->post('encryptedData')));
        $code = $this->request->post('code');
        $iv = urldecode(htmlspecialchars_decode($this->request->post('iv')));
        // 微信小程序通过code获取session
        $session = OauthService::wxCode2Session($code);
        if (!$session){
             return $this->renderError('绑定手机号失败');
        }
        $sessionKey = $session['session_key'];
        $data = OauthService::wxDecryptData($sessionKey,$encryptedData,$iv);
        $dataJson = json_decode($data,true);
        $userInfo = $this->getUser();
        $model = new UserModel;
        if ($model->where(['user_id'=>$userInfo['user_id']])->update(['mobile'=>$dataJson['phoneNumber'],'password' =>yoshop_hash('123456')])){
            return $this->renderSuccess('ok');
        }
        return $this->renderError('绑定手机号失败');
        
    }
}
