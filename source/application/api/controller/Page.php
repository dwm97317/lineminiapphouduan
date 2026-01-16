<?php
namespace app\api\controller;
use app\api\model\WxappPage;
use app\api\model\Banner;
use app\api\model\store\Shop;
use app\api\model\Article as ArticleModel;
use app\api\model\dealer\Setting;
use app\api\model\Setting as SettingModel;
use app\api\model\Line;
use app\api\model\Bank;
use app\common\model\Batch;
use app\common\model\Setting as CommonSetting;
use app\store\model\user\UserLine;
use app\common\model\Wxapp as WxappModel;
use app\api\service\trackApi\TrackApi;
use app\api\model\BannerLog;
use app\common\library\wechat\WxPay;
use app\common\library\payment\HantePay\hantePay;
use app\common\library\payment\Omipay\Omipay;
use think\Hook;
use app\common\model\UploadFile;
use  app\api\model\PackageService;
use app\api\model\article\Category as CategoryModel;
use app\api\model\Country;
/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class Page extends Controller
{
    /**
     * 页面数据
     * @param null $page_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($page_id = null)
    {
        // 页面元素
        $data = WxappPage::getPageData($this->getUser(false), $page_id);
        return $this->renderSuccess($data);
    }
    
    //获取H5跳转地址
    public function getCode(){
        $wxappId = $this->request->param('wxapp_id');
        $app_wxappid = WxappModel::detail($wxappId);
        // dump($app_wxappid);die;
        if(!empty($app_wxappid['other_url'])){
            $redirectUri = $app_wxappid['other_url']."html5/pages/my/my"; 
        }else{
            $redirectUri = base_url()."html5/pages/my/my"; 
        }
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$app_wxappid['app_wxappid']."&redirect_uri=".$redirectUri."&response_type=code&scope=snsapi_userinfo&state=".$wxappId."#wechat_redirect";
       return $this->renderSuccess($url);
    }
    
    //校验用户资料必填项是否填写完毕
    public function checkuserinfo(){
        $user = $this->getUser();
        $userclient = SettingModel::getItem("userclient");
        if($userclient['packit']['is_force']==1){
            if($userclient['userinfo']['is_mobile_force']==1 && empty($user['mobile'])){
            return $this->renderError("打包前请完善个人资料");
            }
            if($userclient['userinfo']['is_email_force']==1 && empty($user['email'])){
                return $this->renderError("打包前请完善个人资料");
            }
            if($userclient['userinfo']['is_wechat_force']==1 && empty($user['wechat'])){
                return $this->renderError("打包前请完善个人资料");
            }
            
            if($userclient['userinfo']['is_birthday_force']==1 && empty($user['birthday'])){
                return $this->renderError("打包前请完善个人资料");
            }
            
            if($userclient['userinfo']['is_identification_card_force']==1 && empty($user['user_image_id'])){
                return $this->renderError("打包前请完善个人资料");
            }
        }
        
        return $this->renderSuccess("资料非常完善");
    }
    
    public function batchLog(){
        $Batch = new Batch;
        return true;
    }
    
    //获取国家前缀
    public function getCountryQianZhui(){
        $list = getFileData('assets/sms.json');
        $SettingModel = new SettingModel();
        $is_phone = $SettingModel::getItem("userclient")['loginsetting']['is_phone'];
        return $this->renderSuccess(compact('list','is_phone'));
    }
    
    //获取语言设置
    public function getLangSetting(){
        $SettingModel = new SettingModel();
        $lang = $SettingModel::getItem("lang");
        // dump($lang);die;
        
        if($lang['zhHans']==1){
            $zhHans=[
                ['name' =>'简体','type' => 'zhHans']
            ];
        }
        $en = $thai = $vietnam = $zhHant = [];
        if($lang['en']==1){
            $en=[
                ['name' =>'English','type' => 'en']
            ]; 
        }
        if($lang['zhHant']==1){
            $zhHant=[
                ['name' =>'繁体','type' => 'zhHant']
            ]; 
        }
        if($lang['thai']==1){
            $thai =[
                ['name' =>'thai','type' => 'thai']
            ]; 
        }
        if($lang['vietnam']==1){
            $vietnam=[
                ['name' =>'vietnam','type' => 'vietnam']
            ]; 
        }
        // dump();die;
        $langs['data'] = array_merge($zhHans,$en,$zhHant,$thai,$vietnam);
        $langs['default'] = $lang['default'];
        return $this->renderSuccess($langs);
    }
    
    //获取语言包
    public function getLangPackage(){
        $data = $this->request->param();
        switch ($data['type']) {
            case 'zhHant':
                $track = getFileDataForLang('lang/10001/zhHant.json');  
                break;
            case 'en':
                $track = getFileDataForLang('lang/10001/English.json');
                break;
            case 'zhHans':
                $track = getFileDataForLang('lang/10001/zhHans.json');
                break;
            case 'thai':
                $track = getFileDataForLang('lang/10001/thai.json');
                break;
            case 'vietnam':
                $track = getFileDataForLang('lang/10001/vietnam.json');
                break;
                
            default:
                $track = getFileDataForLang('lang/10001/zhHant.json');  
                break;
        }
        return $this->renderSuccess($track);
    }
    
    //获取支付设置
    public function getPaySetting(){
        $SettingModel = new SettingModel();
        $paytype = $SettingModel::getItem("paytype");
        return $this->renderSuccess($paytype);
    }
    
    //测试hook
    public function testhook(){
        $result = Hook::exec('app\\task\\behavior\\Inpack','run');
        // dump($result);die;
    }

    // 轮播图
    public function banner(){
       $bannerModel = (new Banner());
       $data = $bannerModel->queryPage();
       $data = $this->withImageById($data,'image_id','image_path');
       return $this->renderSuccess($data);
    }
    
    // 广告图
    public function adviseBanner(){
       $bannerModel = (new Banner());
       $data = $bannerModel->adviseBanner();
       $data = $this->withImageById($data,'image_id','image_path');

       return $this->renderSuccess($data);
    }
    
    //弹窗公告日志记录
    public function bannerLog(){
        // dump(input());die;
        $this->user = $this->getUser(); 
        $BannerLog = new BannerLog();
        $data = input();
        // dump(($this->user)['user_id']);die;
        $data['user_id'] = ($this->user)['user_id'];
        if($BannerLog->bannerlog($data)){
           return $this->renderSuccess("操作成功"); 
        }
        return $this->renderError("操作失败");
    }
    
    // 弹窗公告图
    //逻辑描述：当用户没登录不提示，用户登录后提示
    public function noticeBanner(){
        $setting = SettingModel::detail('store')['values']['jumpbox'];
        $BannerLog= new BannerLog();
        //如果没登陆直接返回
        if(!input('token')){
            return $this->renderSuccess($data = []);
        }
        
        $this->user = $this->getUser(); 
        $bannerModel = (new Banner());
        $data = $bannerModel->noticeBanner();
        if(empty($data)){
            return $this->renderError($data = []);
        }
        $data = $this->withImageById($data,'image_id','image_path');
        $dataT = [];
        if($setting['mode'] ==10){
            return $this->renderBannerSuccess($data,count($data));
        }
        if($setting['mode'] ==20){
            $i = 0;
            foreach($data as $key => $value){
                $result = $BannerLog->where('banner_id',$value['id'])->where('user_id',($this->user)['user_id'])->find();
                if($result){
                    continue;
                }
                $dataT[$i] = $value;
                $i+=1;
            }
            // dump($dataT);die;
            return $this->renderBannerSuccess($dataT,count($dataT));
        }
        if($setting['mode'] ==30){
            return $this->renderSuccess($data = []);
        }
        return $this->renderSuccess($data);
    }
    
    //获取协议
    public function report_note(){
        $category_id = (new CategoryModel())->where(['belong'=>4])->value("category_id");
        $detail = ArticleModel::where(['category_id'=>$category_id])->find();
        return $this->renderSuccess(compact('detail'));
     }

    // 邀请入口
    public function invite(){
        $setting = (new Setting())->getShareSetting();
        $setting = json_decode($setting,true);
        return $this->renderSuccess($setting);
    }
    
    // 用户端各种参数设置
    public function service(){
        $store = (new SettingModel())->where(['key' => 'store'])->field('values')->find()->toArray();
        if(!empty($store['values']['cover_id'])){
             $store['values']['file_path'] = UploadFile::detail($store['values']['cover_id'])['file_path'];
        }
        $store["country"] = (new Country())->queryTopCountry();
        $store['userclient']= SettingModel::detail('userclient')['values'];
        if(!empty($store['userclient']['guide']['first_image'])){
             $store['userclient']['guide']['first_image'] = UploadFile::detail($store['userclient']['guide']['first_image'])['file_path'];
        }
        if(!empty($store['userclient']['guide']['second_image'])){
             $store['userclient']['guide']['second_image'] = UploadFile::detail($store['userclient']['guide']['second_image'])['file_path'];
        }
        if(!empty($store['userclient']['guide']['third_image'])){
             $store['userclient']['guide']['third_image'] = UploadFile::detail($store['userclient']['guide']['third_image'])['file_path'];
        }
        $store['copyright']= WxappModel::detail(input('wxapp_id'));
        $store['paytype']= SettingModel::detail('paytype')['values'];
        $store['keeper']= SettingModel::detail('keeper')['values'];
        return $this->renderSuccess($store);
    }
    
    //获取一个仓库的地址
    public function getStorageFirst(){
      $this->user = $this->getUser();
      $data = (new Shop())->getList();
      return $this->storageDetails($data[0]['shop_id']);
    }
    
    // 获取仓库列表
    public function storageList(){
      $this->user = $this->getUser(); 
      $data = (new Shop())->getList();
      $data= $data->toArray();
      //获取设置信息
      $setting = CommonSetting::getItem('store',input('wxapp_id'));
       if($setting['is_change_uid']==1){
          ($this->user)['user_code'] = ($this->user)['user_code'].'室';
          ($this->user)['user_id'] = ($this->user)['user_id'].'室';
       }
       $aiidentify = CommonSetting::getItem('aiidentify',input('wxapp_id'));
       if($aiidentify['is_enable']==1){
           ($this->user)['user_code'] = $aiidentify['keyword1'].($this->user)['user_code'].$aiidentify['keyword2'];
           ($this->user)['user_id'] = $aiidentify['keyword1'].($this->user)['user_id'].$aiidentify['keyword2'];
       }
      //0 显示ID, 1显示code 2 都显示
    //   dump($setting['usercode_mode']['is_show']);die;
    // dump($data);die;
       if($setting['usercode_mode']['is_show']==1){
           foreach ($data as $k => $v){
                if($v['type']==1){
                   $data[$k]['region']['province'] = '';
                   $data[$k]['region']['city'] = '';
                   $data[$k]['region']['region'] = '';
                }
                if($v['type']==1){
                    if($setting['link_mode'] == 10){
                     $data[$k]['linkman'] =$data[$k]['shop_name'].'-CODE:'.($this->user)['user_code'];
                    }
                    if($setting['link_mode'] == 20){
                         $data[$k]['linkman'] =$data[$k]['linkman'].'-CODE:'.($this->user)['user_code'];
                    }
                    if($setting['link_mode'] == 30){
                         $data[$k]['linkman'] =($this->user)['nickName'].'-CODE:'.($this->user)['user_code'];
                    }
                    if($setting['link_mode'] == 40){
                     $data[$k]['linkman'] =$data[$k]['shop_alias_name'].'-CODE:'.($this->user)['user_code'];
                    }
                    $data[$k]['address'] = 'CODE:'.$this->user['user_code'].'-'.$v['address'];
                    if($setting['link_mode'] == 50){
                        $data[$k]['address'] =  $v['address'].($this->user)['user_code'];
                        $data[$k]['linkman'] = ($this->user)['nickName'];
                    }
                }else{
                    if($setting['link_mode'] == 10){
                     $data[$k]['linkman'] =$data[$k]['shop_name'].'-CODE:'.($this->user)['user_code'];
                    }
                    if($setting['link_mode'] == 20){
                         $data[$k]['linkman'] =$data[$k]['linkman'].'-CODE:'.($this->user)['user_code'];
                    }
                    if($setting['link_mode'] == 30){
                         $data[$k]['linkman'] =($this->user)['nickName'].'-CODE:'.($this->user)['user_code'];
                    }
                    if($setting['link_mode'] == 40){
                     $data[$k]['linkman'] =$data[$k]['shop_alias_name'].'-CODE:'.($this->user)['user_code'];
                    }
                    $data[$k]['address'] = $v['address'].'(CODE:'.$this->user['user_code'].')';
                    if($setting['link_mode'] == 50){
                       $data[$k]['address'] =  $v['address'].($this->user)['user_code'];
                        $data[$k]['linkman'] = ($this->user)['nickName'];
                    }
                }
           }    
       }else if($setting['usercode_mode']['is_show']==0){
          foreach ($data as $k => $v){
               if($v['type']==1){
                   $data[$k]['region']['province'] = '';
                   $data[$k]['region']['city'] = '';
                   $data[$k]['region']['region'] = '';
                   $data[$k]['address'] = 'UID:'.$this->user['user_id'].'-'.$v['address'];
                    if($setting['link_mode'] == 10){
                     $data[$k]['linkman'] =$data[$k]['shop_name'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 20){
                         $data[$k]['linkman'] =$data[$k]['linkman'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 30){
                         $data[$k]['linkman'] =($this->user)['nickName'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 40){
                     $data[$k]['linkman'] =$data[$k]['shop_alias_name'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 50){
                        $data[$k]['address'] =  $v['address'].$this->user['user_id'];
                        $data[$k]['linkman'] = ($this->user)['nickName'];
                    }
               }else{
                    $data[$k]['address'] = $v['address'].'UID:'.$this->user['user_id'];
                    if($setting['link_mode'] == 10){
                     $data[$k]['linkman'] =$data[$k]['shop_name'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 20){
                         $data[$k]['linkman'] =$data[$k]['linkman'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 30){
                         $data[$k]['linkman'] =($this->user)['nickName'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 40){
                     $data[$k]['linkman'] =$data[$k]['shop_alias_name'].'-UID:'.($this->user)['user_id'];
                    }
                    if($setting['link_mode'] == 50){
                        $data[$k]['address'] =  $v['address'].$this->user['user_id'];
                        $data[$k]['linkman'] = ($this->user)['nickName'];
                    }
               }
                
            }
       }else if($setting['usercode_mode']['is_show']==2){
          foreach ($data as $k => $v){
               if($v['type']==1){
                   $data[$k]['region']['province'] = '';
                   $data[$k]['region']['city'] = '';
                   $data[$k]['region']['region'] = '';
               }
               if($v['type']==1){
                $data[$k]['address'] = 'UID:'.$this->user['user_id'].'-CODE:'.$this->user['user_code'].'-'.$v['address'];
                if($setting['link_mode'] == 10){
                 $data[$k]['linkman'] =$data[$k]['shop_name'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 20){
                     $data[$k]['linkman'] =$data[$k]['linkman'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 30){
                     $data[$k]['linkman'] =($this->user)['nickName'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 40){
                 $data[$k]['linkman'] =$data[$k]['shop_alias_name'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 50){
                    $data[$k]['address'] =  $v['address'].$this->user['user_id'].'/'.$this->user['user_code'];
                    $data[$k]['linkman'] = ($this->user)['nickName'];
                }
               }else{
                $data[$k]['address'] = $v['address'].'UID:'.$this->user['user_id'].'-CODE:'.$this->user['user_code'];
                if($setting['link_mode'] == 10){
                 $data[$k]['linkman'] =$data[$k]['shop_name'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 20){
                     $data[$k]['linkman'] =$data[$k]['linkman'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 30){
                     $data[$k]['linkman'] =($this->user)['nickName'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 40){
                 $data[$k]['linkman'] =$data[$k]['shop_alias_name'].'-UID:'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 50){
                    $data[$k]['address'] =  $v['address'].$this->user['user_id'].'/'.$this->user['user_code'];
                    $data[$k]['linkman'] = ($this->user)['nickName'];
                }
               }
            }
       }
      return $this->renderSuccess($data);
    }
    
    // 获取仓库详情
    public function storageDetails($id){
       $this->user = $this->getUser(); 
       $data = (new Shop())->getDetails($id);
       //当是国外仓库时候，不显示省市区
       if($data['type']==1){
           $data= $data->toArray();
           $data['region']['province'] = '';
           $data['region']['city'] = '';
           $data['region']['region'] = '';
       }
       $setting = CommonSetting::getItem('store',input('wxapp_id'));
        
       // ✅ Fallback: 如果user_code为空且is_show=1，自动切换到is_show=0（使用User ID）
       if($setting['usercode_mode']['is_show']==1 && empty(($this->user)['user_code'])){
           $setting['usercode_mode']['is_show'] = 0;
       }
       
       if($setting['is_change_uid']==1){
          ($this->user)['user_code'] = ($this->user)['user_code'].'室';
          ($this->user)['user_id'] = ($this->user)['user_id'].'室';
       }
       $aiidentify = CommonSetting::getItem('aiidentify',input('wxapp_id'));
       if($aiidentify['is_enable']==1){
           ($this->user)['user_code'] = $aiidentify['keyword1'].($this->user)['user_code'].$aiidentify['keyword2'];
           ($this->user)['user_id'] = $aiidentify['keyword1'].($this->user)['user_id'].$aiidentify['keyword2'];
       }
       //只显示CODE
        // dump($setting['usercode_mode']['is_show']);die;
       if($setting['usercode_mode']['is_show']==1){
           if($data['type']==1){
                
                if($setting['link_mode'] == 10){
                     $data['linkman'] =$data['shop_name'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 20){
                     $data['linkman'] =$data['linkman'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 30){
                     $data['linkman'] =($this->user)['nickName'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 40){
                     $data['linkman'] =$data['shop_alias_name'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 50){
                     $data['linkman'] = ($this->user)['nickName'];
                }
               // 根据地址的设置，生成不同的地址展示模式
                switch ($setting['address_mode']) {
                    case '10':
                        $data['address'] = $data['address'];
                        break;
                    case '20':
                        $data['address'] = $data['address'] = 'UID'.' '.$this->user['user_code'].' '.$data['address'];
                        break;
                    case '30':
                        $data['address'] = $data['address'] = 'UID'.' '.$this->user['user_code'].' '.$data['address'];
                        break;
                    default:
                        // code...
                        break;
                }
                
                
                
           }else{
                
                if($setting['link_mode'] == 10){
                     $data['linkman'] =$data['shop_name'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 20){
                     $data['linkman'] =$data['linkman'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 30){
                     $data['linkman'] =($this->user)['nickName'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 40){
                     $data['linkman'] =$data['shop_alias_name'].'-'.($this->user)['user_code'];
                }
                if($setting['link_mode'] == 50){
                     $data['address'] =  $data['address'].$this->user['user_code'];
                     $data['linkman'] = ($this->user)['nickName'];
                }
                // 根据地址的设置，生成不同的地址展示模式
                switch ($setting['address_mode']) {
                    case '10':
                        $data['address'] = $data['address'];
                        break;
                    case '20':
                        $data['address'] = $data['address'].$this->user['user_code'];
                        break;
                    case '30':
                        $data['address'] = $data['address'].$this->user['user_code'].'室';
                        break;
                    default:
                        // code...
                        break;
                }
           }
       }
       
      //只显示ID 
      if($setting['usercode_mode']['is_show']==0){
             
             if($data['type']==1){
                
                if($setting['link_mode'] == 10){
                     $data['linkman'] =$data['shop_name'].'-UID'.($this->user)['user_id'];
                }
                if($setting['link_mode'] == 20){
                     $data['linkman'] =$data['linkman'].'-UID'.($this->user)['user_id'];
                }
                if($setting['link_mode'] == 30){
                     $data['linkman'] =($this->user)['nickName'].'-UID'.($this->user)['user_id'];
                }
                if($setting['link_mode'] == 40){
                     $data['linkman'] =$data['shop_alias_name'].'-UID'.($this->user)['user_id'];
                }
                if($setting['link_mode'] == 50){
                     $data['linkman'] = ($this->user)['nickName'];
                }
                
                // 根据地址的设置，生成不同的地址展示模式
                switch ($setting['address_mode']) {
                    case '10':
                        $data['address'] = $data['address'];
                        break;
                    case '20':
                        $data['address'] = $data['address'] = 'UID'.' '.$this->user['user_id'].' '.$data['address'];
                        break;
                    case '30':
                        $data['address'] = $data['address'] = 'UID'.' '.$this->user['user_id'].' '.$data['address'];
                        break;
                    default:
                        // code...
                        break;
                }
                
             }else{
                
                if($setting['link_mode'] == 10){
                     $data['linkman'] =$data['shop_name'].($this->user)['user_id'];
                }
                if($setting['link_mode'] == 20){
                     $data['linkman'] =$data['linkman'].($this->user)['user_id'];
                }
                if($setting['link_mode'] == 30){
                     $data['linkman'] =($this->user)['nickName'].($this->user)['user_id'];
                }
                if($setting['link_mode'] == 40){
                     $data['linkman'] =$data['shop_alias_name'].($this->user)['user_id'];
                }
                if($setting['link_mode'] == 50){
                     $data['linkman'] = ($this->user)['nickName'];
                }
                
                // 根据地址的设置，生成不同的地址展示模式
                switch ($setting['address_mode']) {
                    case '10':
                        $data['address'] = $data['address'];
                        break;
                    case '20':
                        $data['address'] = $data['address'].$this->user['user_id'];
                        break;
                    case '30':
                        $data['address'] = $data['address'].$this->user['user_id'].'室';
                        break;
                    default:
                        // code...
                        break;
                }
             }
       }
       //ID和CODE都显示
       if($setting['usercode_mode']['is_show']==2){
            if($data['type']==1){
                
                if($setting['link_mode'] == 10){
                     $data['linkman'] =$data['shop_name'].'-UID'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 20){
                     $data['linkman'] =$data['linkman'].'-UID'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 30){
                     $data['linkman'] =($this->user)['nickName'].'-UID'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 40){
                     $data['linkman'] =$data['shop_alias_name'].'-UID'.($this->user)['user_id'].'-CODE:'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 50){
                     $data['linkman'] = ($this->user)['nickName'];
                }
                
                // 根据地址的设置，生成不同的地址展示模式
                switch ($setting['address_mode']) {
                    case '10':
                        $data['address'] = $data['address'];
                        break;
                    case '20':
                        $data['address'] = $data['address'] = 'UID'.' '.$this->user['user_id'].' '.'-CODE:'.$this->user['user_code'].$data['address'];
                        break;
                    case '30':
                        $data['address'] = $data['address'] = 'UID'.' '.$this->user['user_id'].' '.'-CODE:'.$this->user['user_code'].$data['address'];
                        break;
                    default:
                        // code...
                        break;
                }
                
            }else{
                
                if($setting['link_mode'] == 10){
                     $data['linkman'] =$data['shop_name'].'-UID'.($this->user)['user_id'].'-CODE'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 20){
                     $data['linkman'] =$data['linkman'].'-UID'.($this->user)['user_id'].'-CODE'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 30){
                     $data['linkman'] =($this->user)['nickName'].'-UID'.($this->user)['user_id'].'-CODE'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 40){
                     $data['linkman'] =$data['shop_alias_name'].'-UID'.($this->user)['user_id'].'-CODE'.$this->user['user_code'];
                }
                if($setting['link_mode'] == 50){
                     $data['linkman'] = ($this->user)['nickName'];
                }
                
                // 根据地址的设置，生成不同的地址展示模式
                switch ($setting['address_mode']) {
                    case '10':
                        $data['address'] = $data['address'];
                        break;
                    case '20':
                        $data['address'] = $data['address'].$this->user['user_id'];
                        break;
                    case '30':
                        $data['address'] = $data['address'].$this->user['user_id'].'室';
                        break;
                    default:
                        // code...
                        break;
                }
            }
       }
       
       
       return $this->renderSuccess($data);
    }

    // 最佳线路
    public function goods_line(){
       $data = (new Line())->goodsLine();
       foreach($data as $k => $v){
           if ($v['tariff']==0){
               $data[$k]['line_special'] = mb_substr($data[$k]['line_special'],0,26).'[查看详情]';
           }
       }
       if (!$data->isEmpty()){
           $data = $this->withImageById($data,'image_id');
       }
       $data = array_chunk($data->toArray(),2);
       return $this->renderSuccess($data);
    }
    
    // 最佳线路 - 驼峰命名别名（ThinkPHP 路由兼容）
    public function goodsLine(){
       return $this->goods_line();
    }
    
    // 更多线路
    public function getAllline(){
       $data = (new Line())->getListAll();
       foreach($data as $k => $v){
           if ($v['tariff']==0){
               $data[$k]['line_special'] = mb_substr($data[$k]['line_special'],0,26).'[查看详情]';
           }
       }
       return $this->renderSuccess($data);
    }
    
    //线路详情
    public function lineDetails($id){
      $data = (new Line())->find($id);
      if ($data['free_mode'] == 1){
          $data['free_rule'] = json_decode($data['free_rule'],true);
      }
      if ($data['free_mode'] == 2){
          $data['free_rule'] = json_decode($data['free_rule'],true);
      }
      if ($data['free_mode'] == 3){
          $data['free_rule'] = json_decode($data['free_rule'],true);
      }
      if ($data['free_mode'] == 4){
          $data['free_rule'] = json_decode($data['free_rule'],true);
      }
      $data['line_content'] = html_entity_decode($data['line_content']);
      $line_type_unit_map = [10 =>'g',20=>'kg',30=>'lb',40=>'CBM'];
      $data['line_type_unit_name'] = $line_type_unit_map[$data['line_type_unit']];
      return $this->renderSuccess($data);
    }
    
    /**
    * 运费查询
    * 最全运费查询
    */
    public function getfree(){
       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $height = $this->postData('height')[0];
       $weigth = !empty($this->postData('weigth'))?$this->postData('weigth')[0]:0;
       $country = $this->postData('country_id')[0];
       $category = $this->postData('class_ids')[0];
       $freeType = $this->postData('freeType')[0];
       $service = $this->postData('service')[0]; //增值服务
  
       $weigthV = 0; //初始化为0
       $weigthV_fi =0;
       $wxappId = $this->request->param('wxapp_id');
       $setting = SettingModel::getItem('store',$wxappId);
       $line_type_unit_map = [10 =>'g',20=>'kg',30=>'lb',40=>'CBM'];
   
       //这里用于计算路线国家的匹配度
        if($freeType==0){
            if(!empty($category)){
                $line = (new Line())->with('image')->where('FIND_IN_SET(:ids,categorys)', ['ids' => $category])->where('FIND_IN_SET(:id,countrys)', ['id' => $country])->where('line_type',$freeType)->where('line_type_unit',$setting['weight_mode']['mode'])->select();
            }else{
                $line = (new Line())->with('image')->where('FIND_IN_SET(:id,countrys)', ['id' => $country])->where('line_type',$freeType)->where('line_type_unit',$setting['weight_mode']['mode'])->select();
            }
        }else{
            if(!empty($category)){
                $line = (new Line())->with('image')->where('FIND_IN_SET(:ids,categorys)', ['ids' => $category])->where('FIND_IN_SET(:id,countrys)', ['id' => $country])->where('line_type',$freeType)->select();
            }else{
                $line = (new Line())->with('image')->where('FIND_IN_SET(:id,countrys)', ['id' => $country])->where('line_type',$freeType)->select();
            }
        }
       
       //还需要计算重量范围是否符合，物品属性是否匹配
        $lines =[];
        $k = 0;
        $oWeigth = $weigth;
        foreach ($line as $key => $value) {
            //校验长宽高是否存在
           if(!empty($width) && !empty($height) && !empty($length)){
             // 计算体积重 6000计算方式
             $weigthV = round(($length*$width*$height)/$value['volumeweight'],2);
          
             if($value['volumeweight_type']==20){
                 $weigthV = round(($oWeigth + (($length*$width*$height)/$value['volumeweight'] - $oWeigth)*$value['bubble_weight']/100),2); 
             }
             $oWeigth = $weigthV>$weigth?$weigthV:$weigth; 
           }

           if(isset($value['max_weight']) && isset($value['weight_min'])){
             if(($oWeigth < $value['weight_min']) || ($oWeigth > $value['max_weight'])){
                 continue;
             }
           }
         
       if($setting['is_discount']==1){
        $this->user = $this->getUser();
        // dump($this->user->toArray());die;
        $UserLine =  (new UserLine());
        $linedata= $UserLine->where('user_id',$this->user['user_id'])->where('line_id',$value['id'])->find();
            if($linedata){
               $value['discount']  = $linedata['discount'];
            }else{
                if(isset($this->user['grade']['equity']['discount']) && $this->user['grade']['status']==1){
                   $value['discount'] = isset($this->user['grade']['equity']['discount'])?($this->user['grade']['equity']['discount']/10):1;  
                }else{
                   $value['discount'] = 1;
                }
            }
        }else{
            $value['discount'] = isset($this->user['grade']['equity']['discount'])?($this->user['grade']['equity']['discount']/10):1;
        }
      
           $value['line_type_unit_name'] =  $line_type_unit_map[$value['line_type_unit']];
           $value['weight'] = $oWeigth;
           $value['sortprice'] ='';
           $value['predict'] =[];
           $value['service'] =0;
           $lines[$k] =$value;
           $k = $k+1;
        }
        $PackageService = new PackageService(); 
        //对剩余符合条件的路线进行计算费用；
       foreach ($lines as $key => $value) {
           $lines[$key]['predict'] = [
              'weight' => $oWeigth,
              'price' => '包裹重量超限',
           ]; 
           $oWeigth = $value['weight'];
            //税和增值服务费用
           $otherfree = 0;
           $reprice=0;
           switch ($value['free_mode']) {
             case '1':
               $free_rule = json_decode($value['free_rule'],true);
               $size = sizeof($free_rule);    
               if(($oWeigth>= $free_rule[0]['weight'][0]) && ($oWeigth<= $free_rule[$size-1]['weight'][1])){
   
                  foreach ($free_rule as $k => $v) {
                      if ($oWeigth>$v['weight'][1]){
                            $reprice += (floatval($v['weight'][1]) - floatval($v['weight'][0]))*floatval($v['weight_price']);
                            continue;
                      }else{
                           $reprice += ($oWeigth - floatval($v['weight'][0]))*$v['weight_price'];
                           break;
                      }
                  }
                    //   dump($free_rule[0]['weight_price']);die;
                  $lines[$key]['sortprice'] =($reprice+ floatval($free_rule[0]['weight_price']) * floatval($free_rule[0]['weight'][0])+$otherfree)*$value['discount'];
              
                  $lines[$key]['predict'] = [
                    'weight' => $oWeigth,
                    'price' => number_format(($reprice+ floatval($free_rule[0]['weight_price']) * floatval($free_rule[0]['weight'][0])+$otherfree)*$value['discount'],2),
                    'rule' => $free_rule
                  ];         
               }else{
                    break;
               }
               break;
             case '2':
               //首重价格+续重价格*（总重-首重）
           
               $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                  if ($oWeigth >= $v['first_weight']){
                          $lines[$key]['sortprice'] =($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$value['discount'];
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format(($v['first_price']+ ceil((($oWeigth-$v['first_weight'])/$v['next_weight']))*$v['next_price'] + $otherfree)*$value['discount'],2),
                              'rule' => $v
                          ]; 
                  }else{
                      $lines[$key]['sortprice'] = $v['first_price'];
                      $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format(($v['first_price']+ $otherfree)*$value['discount'],2),
                              'rule' => $v
                          ]; 
                  }
               }
               break;
              case '3':
                $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          $lines[$key]['sortprice'] =(floatval($v['weight_price']) + $otherfree)*$value['discount'] ;
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format((floatval($v['weight_price']) + $otherfree)*$value['discount'],2),
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
               
               case '4':
                $free_rule = json_decode($value['free_rule'],true);
                // dump($value['free_rule']);
               foreach ($free_rule as $k => $v) {
                   
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<=$v['weight'][1]){
                          !isset($v['weight_unit']) && $v['weight_unit']=1;
                          $lines[$key]['sortprice'] =(floatval($v['weight_price']) * ceil(floatval($oWeigth)/floatval($v['weight_unit'])) + floatval($otherfree))*$value['discount'] ;
                          $lines[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => number_format((floatval($v['weight_price']) * ceil(floatval($oWeigth)/floatval($v['weight_unit'])) + floatval($otherfree))*$value['discount'],2),
                              'rule' => $v,
                              'service' =>0,
                          ]; 
                      }
                   }
               }
               
               break;
             default:
               break;
           }
            $pricetwo = $pricethree = $lines[$key]['sortprice'];
            if($service){
              $servicelist = explode(',',$service);
              $servicelist = array_unique($servicelist);
            //   dump($servicelist);die;
              foreach ($servicelist as $val){
                  $servicedetail = $PackageService::detail($val);
                  if($servicedetail['type']==0){
                      $lines[$key]['service'] = $lines[$key]['service'] + $servicedetail['price'];
                      $pricethree = floatval($pricethree) + floatval($servicedetail['price']);
                  }
                  if($servicedetail['type']==1){
                      $lines[$key]['service'] = floatval($pricetwo)*floatval($servicedetail['percentage'])/100 + floatval($lines[$key]['service']);
                      $pricethree = floatval($pricetwo)* floatval($servicedetail['percentage'])/100 + floatval($pricethree);
                  }
              }
            }
            $lines[$key]['sortprice'] = number_format(floatval($pricethree),2);
       }
       
      $mapsort = [10=>'desc',20=>'asc',30=>'nat'];
      $mapmode = [10=>'sortprice',20=>'sort',30=>'id'];
      $line = $this->withImageById($lines,'image_id','image_path');
      $sortedAccounts = $this->list_sort_by($line, $mapmode[$setting['sort_mode']], $mapsort[$setting['is_sort']]);
      return $this->renderSuccess($sortedAccounts);
    }

     /**
	 * 对查询结果集进行排序
	 * @access public
	 * @param array $list 查询结果
	 * @param string $field 排序的字段名
	 * @param array $sortby 排序类型
	 * asc正向排序 desc逆向排序 nat自然排序
	 * @return array
	 */
	public function list_sort_by($list, $field, $sortby = 'asc'){
	    if (is_array($list)) {
	        $refer = $resultSet = array();
	        foreach ($list as $i => $data)
	            $refer[$i] = $data[$field];
	        switch ($sortby) {
	            case 'asc': // 正向排序
	                asort($refer);
	                break;
	            case 'desc':// 逆向排序
	                arsort($refer);
	                break;
	            case 'nat': // 自然排序
	                natcasesort($refer);
	                break;
	        }
	        foreach ($refer as $key => $val)
	            $resultSet[] = &$list[$key];
	        return $resultSet;
	    }
	    return false;
	}
    
    //银行账户列表 
    public function bankCardList(){
      $Bank = (new Bank());
      $list['data'] = $Bank->getList();
      $list['setting'] = html_entity_decode((new SettingModel())->getItem('bank')['setting']);
      return $this->renderSuccess($list);
    }
    
     //银行凭证设置
    public function getbanksetting(){
      $list = SettingModel::getItem('bank');
      return $this->renderSuccess($list);
    }
    
    /*新手问题*/
    public function problem(){
        $param =input();
        $list= (new ArticleModel())->getProblemList($limit = 15);
        return $this->renderSuccess($list);
    }
    
    /*违禁物品*/
    public function ban(){
        $param =input();
        $list= (new ArticleModel())->getBanList($limit = 15);
        return $this->renderSuccess($list);
    }
    
    //汉特支付的回调
    public function notify(){
        $hantePay = (new hantePay());
        $hantePay->notify();
    }
    
    /**
     * 获取客户联系配置
     * @return array
     */
    public function customer_contact() {
        $wxapp_id = $this->request->param('wxapp_id', 10001);
        $config = SettingModel::getItem('customer_contact', $wxapp_id);
        
        // 如果配置不存在，返回空对象
        if (empty($config)) {
            $config = [
                'hotline_th' => '',
                'line_support' => '',
                'wechat' => ''
            ];
        }
        
        return $this->renderSuccess($config);
    }
    
    // 驼峰命名别名（ThinkPHP 路由兼容）
    public function customerContact() {
        return $this->customer_contact();
    }
}
