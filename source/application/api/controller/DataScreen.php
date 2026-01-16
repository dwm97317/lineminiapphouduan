<?php
namespace app\api\controller;
use app\api\model\WxappPage;
use app\api\model\Banner;
use app\api\model\Inpack;
use app\api\model\store\Shop;
use app\api\model\Article as ArticleModel;
use app\api\model\dealer\Setting;
use app\api\model\Setting as SettingModel;
use app\api\model\Line;
use app\api\model\Bank;
use app\common\model\Setting as CommonSetting;
use app\store\model\user\UserLine;
use app\common\model\Wxapp as WxappModel;
use app\api\model\User;
use app\api\service\trackApi\TrackApi;
use app\api\model\BannerLog;
use app\common\library\wechat\WxPay;
use app\common\library\payment\HantePay\hantePay;
use think\Hook;
use app\common\model\UploadFile;
use  app\api\model\PackageService;
use app\api\model\Package;
use app\api\model\article\Category as CategoryModel;
/**
 * 页面控制器
 * Class Index
 * @package app\api\controller
 */
class DataScreen   extends Controller
{
    
    //获取第一排的统计信息
    public function getUserTotal(){
        $user = new User;
        $countuser = $user->where('is_delete',0)->count();
        $startTime = strtotime(date("Y-m-d",time()));
        $todayuser = $user->where('is_delete',0)->where('create_time','between',[$startTime,$startTime+86400])->count();
        
        $today=date("Y-m-d");
        $zuixiao = date('Y-m-01', strtotime($today));
        $zuida = strtotime(date('Y-m-d', strtotime("$zuixiao +1 month -1 day")));
        $mouth = $user->where('is_delete',0)->where('create_time','between',[strtotime($zuixiao),$zuida])->count();
        $data = [
            'title' =>'用户总数量',
            'z_title'=>'总数据',
            'z_date'=>date("Y-m",time()),
            'formatNumberStrs'=> str_pad($countuser,7,'0',STR_PAD_LEFT),
            'todaytitle'=>'今日新增',
            'todayup'=>$todayuser,
            'targettitle'=>'本月新增',
            'targetup'=>$mouth,
        ];
        return $this->renderSuccess($data);
    }
    
     //获取第二排的统计信息
    public function getUserUp(){
        $UserModel = new User();
        $now_year = date('Y');
        $now_month = date('m');
        $date1 = $date2 = $date3 = $date4 = array();
        for ($i = 1; $i <= $now_month; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $date1[] = $now_year . "-" . $i .'-01';
            $date3[] = $now_year . "-" . $i ;
        }
        
        for ($i = (count($date1) + 1); $i <= 12 ; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $date2[] = ($now_year - 1) . "-" . $i .'-01';
            $date4[] = ($now_year - 1) . "-" . $i;
        }
        $date = array_merge($date1, $date2);
        $datey = array_merge($date4, $date3);
        $datex = array_merge($date2, $date1);
        foreach ($datex as $val){
          $series[] = $UserModel->where('create_time','between',[strtotime($val),strtotime("$val +1 month -1 day")])->count();
        }
      return $this->renderSuccess(['yAxis' => [0 => ['data' => $datey]],'series'=> [0 => ['data'=>$series]  ]]);
    }
    
    //获取第三排的统计信息
    public function getPackUp(){
        $UserModel = new User();
        $Inpack = new Inpack;
        $now_year = date('Y');
        $now_month = date('m');
        $date1 = $date2 = $date3 = $date4 = array();
        for ($i = 1; $i <= $now_month; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $date1[] = $now_year . "-" . $i .'-01';
            $date3[] = $now_year . "-" . $i ;
        }
        
        for ($i = (count($date1) + 1); $i <= 12 ; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $date2[] = ($now_year - 1) . "-" . $i .'-01';
            $date4[] = ($now_year - 1) . "-" . $i;
        }
        $date = array_merge($date1, $date2);
        $datey = array_merge($date4, $date3);
        $datex = array_merge($date2, $date1);
        foreach ($datex as $val){
          $series[] = $UserModel->where('create_time','between',[strtotime($val),strtotime("$val +1 month -1 day")])->count();
          $yAxis[] =  $Inpack->where(['is_delete'=>0])->where('created_time','between',[$val,date('Y-m-d', strtotime("$val +1 month -1 day"))])->count();
        }
        return $this->renderSuccess(['data' => [0 => $datey],'series'=> [0 => ['data'=>$series],1=>['data'=>$yAxis]]]);
    }
    
    //获取中间第一排的统计信息
    public function getPackAllTotal(){
        $datad = $this->request->param();
        $packModel = (new Package());
        $map['status']  = array('in',[2,3,4,5,6,7]);
        $maps['status']  = array('in',[2,3,4,5,6,7,8]);
        if($datad['shop_id']==-1){
            $weiruku = $packModel->where(['status' => 1,'is_delete'=>0])->count();
            $yiruku = $packModel->where(['status' => 2,'is_delete'=>0,])->count();
            $zaituzhong = $packModel->where(['status' => 9,'is_delete'=>0])->count();
            $yiwancheng = $packModel->where(['status' => 11,'is_delete'=>0])->count();
            $daidabao = $packModel->where($map)->where(['is_delete'=>0,'is_take' =>2])->count();
            $daifahuo = $packModel->where(['status' => 8,'is_delete'=>0])->count();
            $dairenling = $packModel->where(['is_take' => 1,'is_delete'=>0])->count();
            $wentijian = $packModel->where(['status' => -1,'is_delete'=>0])->count();
            $usertotal = $packModel->where($maps)->where(['is_delete'=>0])->count();
            $total = $packModel->where(['is_delete'=>0])->count();
            $total==0 && $total=100;
        }else{
            $weiruku = $packModel->where(['status' => 1,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $yiruku = $packModel->where(['status' => 2,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $zaituzhong = $packModel->where(['status' => 9,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $yiwancheng = $packModel->where(['status' => 11,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $daidabao = $packModel->where($map)->where(['is_delete'=>0,'storage_id' => $datad['shop_id'],'is_take' =>2])->count();
            $daifahuo = $packModel->where(['status' => 8,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $dairenling = $packModel->where(['is_take' => 1,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $wentijian = $packModel->where(['status' => -1,'is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $usertotal = $packModel->where($maps)->where(['is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $total = $packModel->where(['is_delete'=>0,'storage_id' =>$datad['shop_id']])->count();
            $total==0 && $total=100;
        }
        // dump($datad);die;
        $data = [
            'title' =>'全部包裹数量',
            'num'=>str_pad($total,7,'0',STR_PAD_LEFT),
            'z_title'=>'问题件占比',
            'z_num'=> round($wentijian/$total,2).'%',
            'x_title'=>'待打包占比',
            'x_num'=> round($daidabao/$total,2).'%',
            'weiruku'=> ['value' => $weiruku,'text'=>'未入库包裹'],
            'yiruku'=> ['value' => $yiruku,'text'=>'已入库包裹'],
            'zaituzhong'=> ['value' => $zaituzhong,'text'=>'在途中包裹'],
            'yiwancheng'=> ['value' => $yiwancheng,'text'=>'已完成包裹'],
            'daidabao'=> ['value' => $daidabao,'text'=>'待打包包裹'],
            'daifahuo'=> ['value' => $daifahuo,'text'=>'待发货包裹'],
            'dairenling'=> ['value' => $dairenling,'text'=>'待认领包裹'],
            'wentijian'=> ['value' => $wentijian,'text'=>'问题件包裹'],
            'usertotal'=> ['value' => $usertotal,'text'=>'在仓总数'],
            'chulilv'=> ['value' => '34%','text'=>'处理率'],
        ];
        return $this->renderSuccess($data);
    }
    
    
     //获取中间第二排的统计信息
    public function getInpackUp(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        //获取日期
        $now_year = date('Y');
        $now_month = date('m');
        $date1 = $date2 = $date3 = $date4 = array();
        for ($i = 1; $i <= $now_month; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $date1[] = $now_year . "-" . $i .'-01';
            $date3[] = $now_year . "-" . $i ;
        }
        
        for ($i = (count($date1) + 1); $i <= 12 ; $i++) {
            if ($i < 10) {
                $i = '0' . $i;
            }
            $date2[] = ($now_year - 1) . "-" . $i .'-01';
            $date4[] = ($now_year - 1) . "-" . $i;
        }
        $date = array_merge($date1, $date2);
        $datey = array_merge($date4, $date3);
        $datex = array_merge($date2, $date1);
        foreach ($datex as $val){
           $series[] = $Inpack->where(['is_delete'=>0,'storage_id'=>$data['shop_id']])->where('created_time','between',[$val,date('Y-m-d', strtotime("$val +1 month -1 day"))])->count();
        }
        $dates = $datey;
        return $this->renderSuccess(['data' => [0 => $dates],'series'=> [0 => ['data'=>$series]]]);
    }
    
    
    //获取右边第一排的统计信息
    public function getInpackTotal(){
        $datad = $this->request->param();
        $Inpack = new Inpack;
        if($datad['shop_id'] == -1){
            $daichayan = $Inpack->where(['is_delete'=>0,'status'=>1])->count();
            $daifahuo = $Inpack->where(['is_delete'=>0,'status' =>['in',[2,3,4,5]]])->count();
            $zaituzhong = $Inpack->where(['is_delete'=>0,'status' =>['in',[6,7,8]]])->count();
        }else{
            $daichayan = $Inpack->where(['is_delete'=>0,'status'=>1,'storage_id'=>$datad['shop_id']])->count();
            $daifahuo = $Inpack->where(['is_delete'=>0,'storage_id'=>$datad['shop_id'],'status' => ['in',[2,3,4,5]]])->count();
            $zaituzhong = $Inpack->where(['is_delete'=>0,'storage_id'=>$datad['shop_id'],'status' => ['in',[6,7,8]]])->count();
        }
        
        $total = $daichayan + $daifahuo + $zaituzhong;
        $data = [
            'title' =>'集运订单监控',
            'z_title'=>'总数据',
            'z_date'=>date("Y-m",time()),
            'formatNumberStrs'=>$total,
            'todaytitle'=>'处理中订单总数',
            'daichayan'=> ['value' => $daichayan,'text'=>'待查验'],
            'daifahuo'=> ['value' => $daifahuo,'text'=>'待发货'],
            'zaituzhong'=> ['value' => $zaituzhong,'text'=>'在途中'],
        ];
        return $this->renderSuccess($data);
    }
    
    //排名
    public function getRankingList(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        $line = new Line();
        $linedata = $line->getListAll([]);
        if($data['shop_id'] != -1){
            $total = $Inpack->where('storage_id',$data['shop_id'])->count();
            foreach ($linedata as $key=>$val){
               $datas[$key]['name'] = $val['name'];
               $datas[$key]['num'] = $Inpack->where('storage_id',$data['shop_id'])->where('line_id',$val['id'])->count(); 
               $datas[$key]['total'] = $total;
            }
        }else{
            $total = $Inpack->count();
            foreach ($linedata as $key=>$val){
               $datas[$key]['name'] = $val['name'];
               $datas[$key]['num'] = $Inpack->where('line_id',$val['id'])->count();
               $datas[$key]['total'] = $total;
            }
        }
        $sort = array_column($datas,'num');
        array_multisort($sort,SORT_DESC,$datas);
        return $this->renderSuccess([
            'result'=>array_slice($datas,0,10),
        ]);
    }
    
    
    //地图
    public function getMapList(){
        $arcData = [ 0=>[
            0=>['to'=> ['lat'=>40.07733, 'lng'=> 116.60039]],
            1=>['from'=> ['lat'=>31.23037, 'lng'=> 121.4737]],    
            2=>['count'=>2335467]    
            ],
            1=>[
            0=>['to'=> ['lat'=>4.07733, 'lng'=> 116.60039]],
            1=>['from'=> ['lat'=>31.23037, 'lng'=> 116.60039]],    
            2=>['count'=>2335467]    
            ]
            
        ];
        $data = [
            'center'=> ['lat' => 37.80787,'lng'=>112.269029],
            'arcData '=> $arcData
        ];
        return $this->renderSuccess($data);
    }
    
    //地图上的数据
    public function getMapPackData(){
        $data = $this->request->param();
        $Inpack = new Inpack;
        $today=date("Y-m-d");
        $zuixiao = date('Y-m-01', strtotime($today));
        $zuida = date('Y-m-d', strtotime("$zuixiao +1 month -1 day"));
        $daichayan = $Inpack->where(['storage_id'=>$data['shop_id'],'status'=>1])->where('created_time','between',[$zuixiao,$zuida])->count();
        $daifahuo = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[2,3,4,5]] ])->where('created_time','between',[$zuixiao,$zuida])->count();
        $zaituzhong = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[6,7,8]]  ])->where('created_time','between',[$zuixiao,$zuida])->count();
        $total = $daichayan + $daifahuo + $zaituzhong;
        
        $zuidayy = date('Y-m-d', strtotime("$zuixiao -1 month -1 day"));
        $zuixiaoyy = date('Y-m-01', strtotime($zuidayy));
        $daichayanyy = $Inpack->where(['storage_id'=>$data['shop_id'],'status'=>1])->where('created_time','between',[$zuixiaoyy,$zuidayy])->count();
        $daifahuoyy = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[2,3,4,5]] ])->where('created_time','between',[$zuixiaoyy,$zuidayy])->count();
        $zaituzhongyy = $Inpack->where(['storage_id'=>$data['shop_id'],'status' => ['in',[6,7,8]]  ])->where('created_time','between',[$zuixiaoyy,$zuidayy])->count();
        $totalyy = $daichayanyy + $daifahuoyy + $zaituzhongyy;
        $totalyy == 0 && $totalyy=1;
        $daichayanyy == 0 && $daichayanyy=1;
        $daifahuoyy == 0 && $daifahuoyy=1;
        $zaituzhongyy == 0 && $zaituzhongyy=1;
        $arcData = [ 
            0=>[
                'name'=> "订单总数",
                'num'=> $total,
                'seqNum'=>round(($total-$totalyy)/$totalyy*100,2).'%'
            ],
            1=>[
                'name'=> "待查验包裹",
                'num'=> $daichayan,
                'seqNum'=>round(($daichayan-$daichayanyy)/$daichayanyy*100,2).'%',
            ],
            2=>[
                'name'=> "待发货包裹",
                'num'=> $daifahuo,
                'seqNum'=>round(($daifahuo-$daifahuoyy)/$daifahuoyy*100,2).'%',
            ],
            3=>[
                'name'=> "在途中包裹",
                'num'=> $zaituzhong,
                'seqNum'=>round(($zaituzhong-$zaituzhongyy)/$zaituzhongyy*100,2).'%',
            ],
        ];
        return $this->renderSuccess($arcData);
    }
    
    
    // 获取仓库列表
    public function storageList(){
      $data = (new Shop())->getList();
      $data= $data->toArray();
      $setting = SettingModel::detail('store');
        // dump($setting['values']['name']);die;
      return $this->renderSuccess(['data'=>$data,'systitle'=>$setting['values']['name'].'数据大屏']);
    }
    
    //获取集运定位信息
    public function getMapData(){
        $latlng = ['阿富汗' => [67.709953, 33.93911],'安哥拉' => [17.873887, -11.202692],'阿尔巴尼亚'=>[20.168331, 41.153332]];
        $XAData = [
            0=>[
                ['name'=>'广州'],
                ['name'=>'广州','value'=>12]
            ],
            1=>[
                ['name'=>'广州'],
                ['name'=>'基里巴斯','value'=>152]
            ], 
            2=>[
                ['name'=>'广州'],
                ['name'=>'阿尔巴尼亚','value'=>132]
            ], 
            3=>[
                ['name'=>'广州'],
                ['name'=>'布隆迪','value'=>112]
            ], 
            4=>[
                ['name'=>'广州'],
                ['name'=>'白俄罗斯','value'=>712]
            ], 
            5=>[
                ['name'=>'广州'],
                ['name'=>'不丹','value'=>122]
            ], 
            6=>[
                ['name'=>'广州'],
                ['name'=>'美国,华盛顿','value'=>123]
            ], 
            7=>[
                ['name'=>'广州'],
                ['name'=>'加拿大,温尼伯','value'=>12]
            ], 
        ];
        return $this->renderSuccess(['xadata'=>$XAData,'latlng'=>$latlng]);
        
    }
}