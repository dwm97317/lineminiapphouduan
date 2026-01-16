<?php
namespace app\api\controller;
use app\api\model\Banner;
use app\api\model\store\Shop;
use app\api\model\Article as ArticleModel;
use app\api\model\dealer\Setting;
use app\api\model\Line;
use app\api\model\Bank;
use app\common\model\Setting as CommonSetting;
use app\store\model\user\UserLine;
use app\common\model\Wxapp as WxappModel;
use app\api\service\trackApi\TrackApi;
use app\api\model\BannerLog;
use app\common\library\wechat\WxPay;
use app\common\library\payment\HantePay\hantePay;
use think\Hook;
use app\common\model\UploadFile;
use  app\api\model\PackageService;
use app\api\model\article\Category as CategoryModel;
use app\api\model\Country;
use app\api\model\Wxapp;
use app\api\model\Package;
use app\api\model\PackageImage;
use app\store\controller\Upload;
use app\common\library\storage\Driver as StorageDriver;
use app\store\model\Setting as SettingModel;
use think\Request;
use app\common\library\AITool\BaiduOcr;
use app\api\model\User as UserModel;
use app\common\model\AiLog;
/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class ApiPost extends Controller
{
    private $config;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        // 存储配置信息
        $this->config = SettingModel::getItem('storage',$this->wxapp_id);
    }
    
    /**
     * 预报
     * Class Passport
     * @package app\api\controller
     */
    public function reportpack(){
        $param = $this->request->param();
    
        if(!isset($param['token']) || empty($param['token'])){
            return $this->renderError("TOKEN不能为空");
        }
        if(!isset($param['expressnum']) || empty($param['expressnum'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['shop_id']) || empty($param['shop_id'])){
            return $this->renderError("仓库号不能为空");
        }
        $Wxapp = new Wxapp;
        $Package = new Package;
        $detail = $Wxapp::detail($param['wxapp_id']);
        if($detail['token'] != $param['token']){
            return $this->renderError("TOKEN错误");
        }
       
        
        $result = $Package->where(['express_num'=>$param['expressnum'],'is_delete'=>0])->find();
        if(!empty($_FILES['file']['name'])){
            $StorageDriver = new StorageDriver($this->config);
            // dump($this->config);die;
            $file = Request::instance()->file();
            // 设置上传文件的信息
            $StorageDriver->setUploadFile('file');
            // 上传图片
            if (!$StorageDriver->upload()) {
                return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
            }
    
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 添加文件库记录
            $uploadFile = $this->addUploadFile($group_id = -1, $fileName, $fileInfo, 'image');
            // dump();die;$uploadFile['file_id']
        }
         
        
        if(!empty($result)){
            $result->save([
                'status'=>2,
                'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
                'length'=>isset($param['length'])?$param['length']:$result['length'],
                'width'=>isset($param['width'])?$param['width']:$result['width'],
                'height'=>isset($param['height'])?$param['height']:$result['height'],
                'entering_warehouse_time'=>getTime()
            ]);
            
            if(isset($uploadFile)){
                $imgdata = [
                    'package_id'=> $result['id'],
                    'image_id'=>$uploadFile['file_id'],
                    'wxapp_id'=>$param['wxapp_id'],
                    'create_time'=>time()
                ];
                (new PackageImage())->save($imgdata);
            }
            return $this->renderSuccess("更新成功");
        }
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['expressnum'],
            'status'=>2,
            'storage_id'=>$param['shop_id'],
            'wxapp_id'=>$param['wxapp_id'],
            'weight'=>$param['weight'],
            'source'=>9,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        if(isset($uploadFile)){
            $imgdata = [
                'package_id'=> $id,
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        return $this->renderSuccess("预报成功");
    }
    
    /**
     * 电子秤二号
     * Class Passport
     * @package app\api\controller
     */
    public function reportpacktwo(){
        $param = $this->request->param();
        $wxapp_id = $param['wxapp_id'];
        $param = $param[0];
        if(!isset($param['ticketsNum']) || empty($param['ticketsNum'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['weight']) || empty($param['weight'])){
            return $this->renderError("包裹重量不能为空");
        }
        $Package = new Package;
        $result = $Package->where(['express_num'=>$param['ticketsNum'],'is_delete'=>0])->find();
        if(!empty($result)){
            $result->save([
                'status'=>2,
                // 'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
                'length'=>$param['length'],
                'height'=>$param['height'],
                'width'=>$param['width'],
                'volume'=>$param['volume'],
                'entering_warehouse_time'=>getTime()
            ]);
            return ['result'=>"true",'message'=>"入库成功"];
        }
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['ticketsNum'],
            'status'=>2,
            // 'storage_id'=>$param['shop_id'],
            'wxapp_id'=>$wxapp_id,
            'weight'=>$param['weight'],
            'length'=>$param['length'],
            'height'=>$param['height'],
            'width'=>$param['width'],
            'source'=>9,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        return ['result'=>"true",'message'=>"入库成功"];
    }

    /**
     * 图片上传接口
     * @return array
     * @throws \think\Exception
     */
    public function image()
    {
        // 实例化存储驱动
        
        $param = $this->request->param();
        // $data =  explode ( ',' ,  $param['file'] );  //截取data:image/png;base64, 这个逗号后的字符
        $data =  base64_decode($param['file']);
        // dump($data);die;
        $path = 'uploads/'.time().rand(10000,99999).'.jpg';
        file_put_contents($path,$data);
        // 设置上传文件的信息
        $this->config = SettingModel::getItem('storage',$param['wxapp_id']);
        $StorageDriver = new StorageDriver($this->config);
        //   dump($path);die;
        $StorageDriver->setUploadFileByReal($path);
        // dump(34);die;
        // 上传图片
        if (!$StorageDriver->put()) {
            return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
        }
        // dump(34);die;
        // 设置上传文件的信息
        // 图片上传路径
        $fileName = $StorageDriver->getFileName();
        // 图片信息
        $fileInfo = $StorageDriver->getFileInfo();
        // 添加文件库记录
        $uploadFile = $this->addUploadFiles($fileName, $fileInfo, 'image');
        $Package = new Package;
        $result = $Package->where(['express_num'=>$param['ticketsNum'],'is_delete'=>0])->find();
        if(isset($uploadFile) && !empty($result)){
            $imgdata = [
                'package_id'=> $result['id'],
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        // 图片上传成功
        return json(['isOK' => 1]);
    }

    /**
     * 添加文件库上传记录
     * @param $fileName
     * @param $fileInfo
     * @param $fileType
     * @return UploadFile
     */
    private function addUploadFiles($fileName, $fileInfo, $fileType)
    {
        // 存储引擎
        $storage = $this->config['default'];

        // 存储域名
        $fileUrl = isset($this->config['engine'][$storage]['domain'])
            ? $this->config['engine'][$storage]['domain'] : '';
        // 添加文件库记录
                
        $model = new UploadFile;
        $model->addImage([
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_size' => $fileInfo['size'],
            'file_type' => $fileType,
            'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
            'is_user' => 0,
            'wxapp_id'=>$this->wxapp_id
        ]);
      
        return $model;
    }
/**
     * 添加文件库上传记录
     * @param $group_id
     * @param $fileName
     * @param $fileInfo
     * @param $fileType
     * @return UploadFile
     */
    private function addUploadFile($group_id, $fileName, $fileInfo, $fileType)
    {
        // 存储引擎
        $storage = $this->config['default'];
        // 存储域名
        $fileUrl = isset($this->config['engine'][$storage]['domain'])
            ? $this->config['engine'][$storage]['domain'] : '';
        // 添加文件库记录
        $model = new UploadFile;
        $model->add([
            'group_id' => $group_id > 0 ? (int)$group_id : 0,
            'storage' => $storage,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_size' => $fileInfo['size'],
            'file_type' => $fileType,
            'extension' => pathinfo($fileInfo['name'], PATHINFO_EXTENSION),
        ]);
        return $model;
    }
    
        /**
     * 预报
     * Class Passport
     * @package app\api\controller
     */
    public function reportpackToBaidu(){
        $param = $this->request->param();
        $setting = SettingModel::getItem('aiidentify',$this->wxapp_id);
        if($setting['is_enable']==0){
            return $this->renderError("尚未开启智能AI识别功能，请更改API");
        }
        // dump($setting);die;
        $BaiduOcr = new BaiduOcr($setting);
        if(!isset($param['token']) || empty($param['token'])){
            return $this->renderError("TOKEN不能为空");
        }
        if(!isset($param['expressnum']) || empty($param['expressnum'])){
            return $this->renderError("快递单号不能为空");
        }
        if(!isset($param['shop_id']) || empty($param['shop_id'])){
            return $this->renderError("仓库号不能为空");
        }
        $Wxapp = new Wxapp;
        $Package = new Package;
        $UserModel = new UserModel;
        $AiLog = new AiLog;
        $detail = $Wxapp::detail($param['wxapp_id']);
        if($detail['token'] != $param['token']){
            return $this->renderError("TOKEN错误");
        }
       
        $userinfo = [];
        $result = $Package->where(['express_num'=>$param['expressnum'],'is_delete'=>0])->find();
        if(!empty($_FILES['file']['name'])){
            $StorageDriver = new StorageDriver($this->config);
            $file = Request::instance()->file();
            //调用百度文字识别接口
            // 设置上传文件的信息
            $StorageDriver->setUploadFile('file');
            // 上传图片
            if (!$StorageDriver->upload()) {
                return json(['code' => 0, 'msg' => '图片上传失败' . $StorageDriver->getError()]);
            }
    
            // 图片上传路径
            $fileName = $StorageDriver->getFileName();
            // 图片信息
            $fileInfo = $StorageDriver->getFileInfo();
            // 添加文件库记录
            $uploadFile = $this->addUploadFile($group_id = -1, $fileName, $fileInfo, 'image');
            //使用百度AI识别结果
            if(!empty($result) && $result['is_take']==1){
                $ailogresult = $BaiduOcr->generalBasic($uploadFile->file_url.'/'.$uploadFile->file_name);
                $AiLog->add([
                    'user_id'=>$ailogresult['user_id'],
                    'content'=>$ailogresult['words'],
                    'wxapp_id'=>$this->wxapp_id,
                ]);
                $detail->setDec('baiduai',1); 
            }
            
            $storesetting = SettingModel::getItem('store',$this->wxapp_id);
            
            //  dump($user_id);die;
            if(!empty($ailogresult['user_id'])){
                if($storesetting['usercode_mode']['is_show']==1){
                    $userinfo = $UserModel::detail(['user_code'=>$ailogresult['user_id']]);
                }else{
                    $userinfo = $UserModel::detail($ailogresult['user_id']);
                }
            }  
            //查询是否存在该用户id，不存在则跳过；存在就将包裹跟用户id绑定；
        }
           
        //当包裹已经预报时
        if(!empty($result)){
            $result->save([
                'status'=>2,
                'member_id'=>!empty($userinfo)?$userinfo['user_id']:$result['member_id'],
                'storage_id'=>$param['shop_id'],
                'weight'=>$param['weight'],
                'is_take'=>!empty($userinfo)?2:$result['is_take'],
                'entering_warehouse_time'=>getTime()
            ]);
            
            if(isset($uploadFile)){
                $imgdata = [
                    'package_id'=> $result['id'],
                    'image_id'=>$uploadFile['file_id'],
                    'wxapp_id'=>$param['wxapp_id'],
                    'create_time'=>time()
                ];
                (new PackageImage())->save($imgdata);
            }
            return $this->renderSuccess("更新成功");
        }
         //当包裹没有预报时
        $data = [
            'order_sn'=> createSn(),
            'express_num'=>$param['expressnum'],
            'status'=>2,
            'storage_id'=>$param['shop_id'],
            'wxapp_id'=>$param['wxapp_id'],
            'weight'=>$param['weight'],
            'source'=>9,
            'member_id'=>!empty($userinfo)?$userinfo['user_id']:'',
            'is_take'=>!empty($userinfo)?2:1,
            'entering_warehouse_time'=>getTime()
        ];
        $id = $Package->saveData($data);
        // dump($id.'-'.$uploadFile);die;
        if(isset($uploadFile)){
            $imgdata = [
                'package_id'=> $id,
                'image_id'=>$uploadFile['file_id'],
                'wxapp_id'=>$param['wxapp_id'],
                'create_time'=>time()
            ];
            (new PackageImage())->save($imgdata);
        }
        return $this->renderSuccess("预报成功");
    }
}
