<?php
namespace app\api\controller\sharp;
use app\api\controller\Controller;
use app\api\model\sharing\SharingUser;
use app\api\model\sharing\SharingOrder;
use app\api\model\Banner;
use app\common\model\Wxapp;
use app\common\library\wechat\WXBizDataCrypt;
use app\api\service\sharing\SharingOrder as SharingOrderService;
use app\Lib\ResponseJson;
/**
 * 拼团控制器
 * Class Article
 * @package app\api\controller
 */
class Index extends Controller
{
    /**
     * 获取基础设置 
     * */
    public function setting(){
          
    }
    
    /**
     * 团长申请
     * */
     public function apply(){
         $type = $this->request->param('type');
         $data = $this->postData();
         // 当前用户信息
         $userInfo = $this->getUser();
         $data['user_id'] = $userInfo['user_id'];
         unset($data['token']);
         $ShareUser = (new SharingUser($data));
         if ($type=='resubmit'){
             $data['status'] = 2;
             if ($ShareUser->reapply($data)){
                return $this->renderSuccess('提交成功');
             }
         }else{
             if ($ShareUser->apply($data)){
                return $this->renderSuccess('提交成功');
             }
         }
         return $this->renderError($ShareUser->getError()??'操作失败');
     }
     
    public function memberbindmobile(){
      $encryptedData = urldecode(input('encryptedData','','htmlspecialchars_decode'));
      $code =          input('code');
      $iv   = urldecode(input('iv','','htmlspecialchars_decode'));
      $member_id = ($this->getUser())['user_id'];
      $res = $this->decodeWxData([
           'code' => $code,
           'encryptedData' => $encryptedData,
           'iv' => $iv,
           'wxapp_id' =>input('wxapp_id')
      ]);
      if (isset($res['phoneNumber'])){
           $data =  $res['phoneNumber'];
           return $this->renderSuccess($data);
      }else{
          return $this->renderError('服务器超时，请稍后再试');
      }
  }
     
     public function decodeWxData($params){
        $wx_setting =  Wxapp::detail($params['wxapp_id']);
        $appid = $wx_setting['app_id'];
        $appsecret = $wx_setting['app_secret'];
        $grant_type = "authorization_code"; //授权（必填）
        $code = $params['code'];    //有效期5分钟 登录会话
        $encryptedData = $params['encryptedData'];
        $iv = $params['iv'];
        // 拼接url
        // $url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=ACCESS_TOKEN"
        $url = "https://api.weixin.qq.com/sns/jscode2session?"."appid=".$appid."&secret=".$appsecret."&js_code=".$code."&grant_type=".$grant_type;
        $res = json_decode($this->httpGet($url),true);
        $sessionKey = $res['session_key']; //取出json里对应的值
        // 获取解密后的数据
        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        ob_end_clean();
        if ($errCode != 0) {
            return ['error'=>$errCode,'msg'=>'解密失败'];
        }
        $data = json_decode($data,true);
        return $data;
    }
     
      public function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    } 

     public function apply_detail(){
          // 当前用户信息
         $userInfo = $this->getUser();
         $detail = (new SharingUser())->where(['user_id'=>$userInfo['user_id'],'status'=>3])->find();
         return $this->renderSuccess(compact('detail')); 
     }
    
     public function banner(){
         $bannerModel = (new Banner());
         $data = $bannerModel->sharpBanner();
         $data = $this->withImageById($data,'image_id','image_path');
         return $this->renderSuccess($data);
     }
     
     // 拼团首页根据经纬度返回列表
     public function sharing(){
         $param = $this->request->param();
         $shareOrder = (new SharingOrder());
         $list = $shareOrder->getListByDistane($param);
         $shareService = (new SharingOrderService());
         // 获取已拼团包裹重量
         $list = $shareService->getPackageWeight($list);
         $list = $shareService->getMainAddressInfo($list);
         return $this->renderSuccess(compact('list')); 
     }
     
     public function sharingpage(){
         $param = $this->request->param();
         $shareOrder = (new SharingOrder());
         $list = $shareOrder->getListByDistanepage($param);
         $shareService = (new SharingOrderService());
         // 获取已拼团包裹重量
         $list = $shareService->getPackageWeight($list);
         $list = $shareService->getMainAddressInfo($list);
         return $this->renderSuccess(compact('list')); 
     }
    
}
