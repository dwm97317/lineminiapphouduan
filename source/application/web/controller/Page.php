<?php
namespace app\web\controller;
use app\web\model\WxappPage;
use app\web\model\Banner;
use app\web\model\store\Shop;
use app\web\model\Article as ArticleModel;
use app\web\model\dealer\Setting;
use app\web\model\Setting as SettingModel;
use app\web\model\Line;
use app\web\model\Bank;
use app\web\model\User;
use app\web\model\Package;
use app\web\model\Inpack;
use app\web\model\SiteSms;
/**
 * 页面控制器
 * Class Index
 * @package app\web\controller
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
    
    // 站内消息
    public function message(){
        $SiteSms = new SiteSms;
        $list = $SiteSms->getList(['member_id'=>$this->user['user']['user_id']]);
        return $this->fetch('guide/message',compact('list'));
    }

    // 轮播图
    public function banner(){
       $bannerModel = (new Banner());
       $data = $bannerModel->queryPage();
       $data = $this->withImageById($data,'image_id','image_path');
       return $this->renderSuccess($data);
    }

    // 邀请入口
    public function invite(){
        $setting = (new Setting())->getShareSetting();
        $setting = json_decode($setting,true);
        return $this->renderSuccess($setting);
    }
    
    // 客服列表
    public function service(){
        $store = (new SettingModel())->where(['key' => 'store'])->field('values')->find();
        return $this->renderSuccess($store);
    }
    
    // 获取仓库列表
    public function storageList(){
      $this->user = $this->getUser(); 
      $keyword = $this->request->param('keyword');
      $where = [];
      if ($keyword){
         $where['shop_name'] = $keyword;
      }
      $data = (new Shop())->getQueryList($where);
      if (!$data->isEmpty()){
          foreach ($data as $k => $v){
                $data[$k]['address'] = $v['address'].'-UID'.$this->user['user_id'];
          }
          $data = $this->withImageById($data,'logo_image_id');
      }
      return $this->renderSuccess($data);
    }
    
    // 获取仓库详情
    public function storageDetails($id){
       $this->user = $this->getUser();     
       $data = (new Shop())->getDetails($id);
       $data['address'] = $data['address'].'-UID:'.$this->user['user_id'];
       return $this->renderSuccess($data);
    }

    // 最佳线路
    public function goods_line(){
       $data = (new Line())->goodsLine();
       foreach($data as $k => $v){
           if ($v['tariff']==0){
               $data[$k]['tariff'] = '包税';
           }
       }
       if (!$data->isEmpty()){
           $data = $this->withImageById($data,'image_id');
       }
       $data = array_chunk($data->toArray(),2);
       return $this->renderSuccess($data);
    }
    
    public function lineDetails($id){
      $data = (new Line())->find($id);
      if ($data['free_mode'] == 1){
          $data['free_rule'] = json_decode($data['free_rule'],true);
      }
      return $this->renderSuccess($data);
    }
    
    // 运费查询
    public function getfree(){

       $length = $this->postData('length')[0];
       $width = $this->postData('width')[0];
       $height = $this->postData('height')[0];
       $weigth = $this->postData('weigth')[0];
       $country = $this->postData('country_id')[0];
       $line_id  = $this->postData('line_id')[0];
       // 计算体检重
       $weigthV = round(($length*$width*$height)/6000,2);
       $filter = [];
       if ($line_id){
           $filter['id'] = $line_id;
       }
       // 取两者中 较重者 
       $oWeigth = $weigthV>$weigth?$weigthV:$weigth;
       $line = (new Line());
       if ($country){
           $line = $line -> where('FIND_IN_SET(:id,countrys)', ['id' => $country]);
       }
       $line = $line->where($filter)->select();
       foreach ($line as $key => $value) {
           $line[$key]['predict'] = [
              'weight' => $oWeigth,
              'price' => '无法预估价格',
           ]; 
           switch ($value['free_mode']) {
             case '1':
               # code...
               $free_rule = json_decode($value['free_rule'],true);
               foreach ($free_rule as $k => $v) {
                   if ($oWeigth >= $v['weight'][0]){
                      if (isset($v['weight'][1]) && $oWeigth<$v['weight'][1]){
                          $line[$key]['predict'] = [
                              'weight' => $oWeigth,
                              'price' => $oWeigth*$v['weight_price'],
                              'rule' => $v
                          ];   
                      }
                   }
               }
               break;
             case '2':
               break;
             default:
               # code...
               break;
           }
       }
       if (!$line->isEmpty())
          $line = $this->withImageById($line,'image_id','image_path');
       return $this->renderSuccess($line);
    }
    
     
    public function bankCardList(){
      $Bank = (new Bank());
      $list = $Bank->getList();
      return $this->renderSuccess($list);
    }
    
    /*常见问题*/
    public function problem(){
        $param =input();
        $newhand= (new ArticleModel())->alias('a')-> join('article_category b ','b.category_id= a.category_id','LEFT')->where('b.belong',2)->where('a.is_delete',0)->select();
        $bidding= (new ArticleModel())->alias('a')-> join('article_category b ','b.category_id= a.category_id','LEFT')->where('b.belong',1)->where('a.is_delete',0)->select();
       return $this->fetch('order/faq',compact('newhand','bidding'));
    }
    
    
    /*联系我们*/
    public function contact(){
       $setting = SettingModel::getItem('store');
    //   dump($setting);die;
       return $this->fetch('order/contact',compact('setting'));
    }
    
    /*会员数据中心*/
    public function data(){

        $user_id = $this->user['user']['user_id'];
        $userData = (new User())->where('user_id',$user_id)->find();
        $Package = new Package();  
        $param =input();
        $data['pack_count1']= $Package->where('member_id',$user_id)->where('status',1)->count();
        $data['pack_count2']= $Package->where('member_id',$user_id)->where('status',2)->count();
        $data['no_pay'] = (new Inpack())->where('member_id',$user_id)->where(['is_pay'=>2,'is_delete'=>0])->count();
        $data['yes_pay'] = (new Inpack())->where('member_id',$user_id)->where('status','in','6,7,8')->where('is_delete',0)->count();
        $list =  (new Inpack())->where('member_id',$user_id)->where('t_order_sn','not null')->where('status','in','1,2,3,4,5,6')->select();
        //获取1月到12月的所有订单
        
        $year = date("Y",time());
        //1月
        $January_first = date_format(date_create('First Day of January '.$year),"Y-m-d");
        $January_last = date_format(date_create('Last Day of January '.$year),"Y-m-d");
        $January_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$January_first, $January_last])->count();
        //2月
        $February_first = date_format(date_create('First Day of February '.$year),"Y-m-d");
        $February_last = date_format(date_create('Last Day of February '.$year),"Y-m-d");
        $February_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$February_first, $February_last])->count();
        //3月
        $March_first = date_format(date_create('First Day of March '.$year),"Y-m-d");
        $March_last = date_format(date_create('Last Day of March '.$year),"Y-m-d");
        $March_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$March_first, $March_last])->count();
        //4月
        $April_first = date_format(date_create('First Day of April '.$year),"Y-m-d");
        $April_last = date_format(date_create('Last Day of April '.$year),"Y-m-d");
        $April_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$April_first, $April_last])->count();
        //5月
        $May_first = date_format(date_create('First Day of May '.$year),"Y-m-d");
        $May_last = date_format(date_create('Last Day of May '.$year),"Y-m-d");
        $May_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$May_first, $May_last])->count();
        //6月
        $June_first = date_format(date_create('First Day of June '.$year),"Y-m-d");
        $June_last = date_format(date_create('Last Day of June '.$year),"Y-m-d");
        $June_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$June_first, $June_last])->count();
        //7月
        $July_first = date_format(date_create('First Day of July '.$year),"Y-m-d");
        $July_last = date_format(date_create('Last Day of July '.$year),"Y-m-d");
        $July_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$July_first, $July_last])->count();
        //8月
        $August_first = date_format(date_create('First Day of August '.$year),"Y-m-d");
        $August_last = date_format(date_create('Last Day of August '.$year),"Y-m-d");
        $August_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$August_first, $August_last])->count();
        //9月
        $September_first = date_format(date_create('First Day of September '.$year),"Y-m-d");
        $September_last = date_format(date_create('Last Day of September '.$year),"Y-m-d");
        $September_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$September_first, $September_last])->count();
        //10月
        $October_first = date_format(date_create('First Day of October '.$year),"Y-m-d");
        $October_last = date_format(date_create('Last Day of October '.$year),"Y-m-d");
        $October_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$October_first, $October_last])->count();
        //11月
        $November_first = date_format(date_create('First Day of November '.$year),"Y-m-d");
        $November_last = date_format(date_create('Last Day of November '.$year),"Y-m-d");
        $November_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$November_first, $November_last])->count();
        //12月
        $December_first = date_format(date_create('First Day of December '.$year),"Y-m-d");
        $December_last = date_format(date_create('Last Day of December '.$year),"Y-m-d");
        $December_count = $Package->where('member_id',$user_id)->where('created_time','between time', [$December_first, $December_last])->count();
        
        $zhe = "$January_count,$February_count,$March_count,$April_count,$May_count,$June_count,$July_count,$August_count,$September_count,$October_count,$November_count,$December_count";

       return $this->fetch('index/index',compact('userData','data','list','zhe'));
    }
}
