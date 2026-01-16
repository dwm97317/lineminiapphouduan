<?php
// 17 track 物流查询接口
namespace app\api\service\trackApi;  
use app\common\model\Setting;

Class TrackApi {

      public $api = 'https://api.17track.net/track/v2'; // API 请求地址
      public $secret = '5A0B5AEF8526C22944C92ADCC50C13CA'; // 秘钥
      public $wxappid = '';
      
      
    //   public function __construct(){
    //     //   dump($wxapp_id);die;
    //   } 

      // 封装header 头部数据
      private function header(){
          $setting = Setting::getItem("store",$this->wxappid);
  
          if ($setting['track17']['key']){
                $this->secret = $setting['track17']['key'];
          }
        
          return [
             '17token:'.$this->secret,
             'Content-Type:application/json'
          ];
      }
      
      private function carrierIdentify($body){
         $api = '/carrierIdentify';
         $data = json_encode($body);
         $res = $this->curl_post($api,$data);
   
         if (!$res['code']){
              echo "请求出错";
              die;
          }
          $resJson = json_decode($res['data'],true);
        
          if(isset($resJson['data']['accepted'])){
              return $resJson['data']['accepted'][0]['carrier'];
          }
          return null;
      }
      
      public function register($data){
          $this->wxappid = $data['wxapp_id'] ;      
          $body = [['number' => $data['track_sn'],'carrier' => $data['t_number']]];
          $api = '/register';
          $body[0]['auto_detection'] = false;
          $body[0]['tag'] = 'myId';
          $body[0]['param'] = !empty($data['phone'])?$data['phone']:'';
          
          $data = json_encode($body);
            
          $res = $this->curl_post($api,$data);
       
          if (!$res['code']){
              echo "请求出错";
              die;
          }
          $resJson = json_decode($res['data'],true);
         
          if ($resJson['code']) return false;
          $dataRes = $resJson['data']['rejected'];
          $bool = true;
      
          foreach ($dataRes as $v){
              if (isset($v['error'])){
                  $err = $v['error'];
                  if ($err['code']!='-18019901'){
                      $bool = false;
                  }
              }     
          }
         
          return $bool;
      }
      
      // 获取 物流轨迹
      /**
       * 传单号
       */
      public function track($data){
          $this->wxappid = $data['wxapp_id'] ; 
          $api = '/gettrackinfo';
          $body = [['number' => $data['track_sn'],'carrier' => $data['t_number']]];
          $datas = json_encode($body);
          $res = $this->curl_post($api,$datas);
    //   dump($res);die;
          if (!$res['code']){
              echo "请求出错";
              die;
          }
          $resJson = json_decode($res['data'],true);
        // dump($resJson);die;
          if (isset($resJson['data']['rejected'][0]) && $resJson['data']['rejected'][0]['error']['code']){
              if ($resJson['data']['rejected'][0]['error']['code']==-18019902){
                  $this->track($data);
              }
              return []; 
          }
      
          return $this->renderTrackList($resJson['data']['accepted'][0]);
      }
      
      // 渲染轨迹列表信息
      public function renderTrackList($data){
         $info = $data['track_info'];
       
         $package_status = [
            '0' => '无法识别', 
            '1' => '正常查有信息',  
            '2' => '尚无信息',  
            '10' => '网站错误',  
            '11' => '处理错误',  
            '12' => '查询错误',  
            '20' => '网站错误，使用缓存',  
            '21' => '处理错误，使用缓存',  
            '22' => '查询错误，使用缓存',  
         ]; 
         
         $countryMap = getFileData('assets/country.json');
         $countryMapKey = array_column($countryMap,null,'key');
         $rdata['number'] = $data['number'];
        //  $rdata['send'] = $countryMapKey[$info['b']]['_name'];
        //  $rdata['accept'] = $countryMapKey[$info['c']]['_name'];
        //  $tlist = [];
        //  foreach ($info['z1'] as $v){
        //      $tlist[] = [
        //       'date' => $v['a'],
        //       'address' => $v['c'],
        //       'content' => $v['z']
        //      ];
        //  }
        //  $rdata['track'] = $tlist;
         return $info;
      }

      public function curl_post($url,$data,$header=[],$timeout=30){
        
          $_curl = curl_init();
          $url = $this->api.$url;
          curl_setopt($_curl, CURLOPT_URL, $url);
          if (!empty($data)) {
              curl_setopt($_curl, CURLOPT_POSTFIELDS, $data);
              curl_setopt($_curl, CURLOPT_POST, true);
          }
          if(substr($url,0,5) == 'https'){
            curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, 2);
          }
          $header_common = $this->header();
          if ($header){
              $header_common = array_merge($header_common,$header);
          }
          curl_setopt($_curl, CURLOPT_HTTPHEADER, $header_common);
          curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
          $result = curl_exec($_curl);
          if (curl_errno($_curl)){
              return ['code'=>0,'msg'=>'请求出错'];
          }
          return ['code'=>1,'data'=>$result];
      }

}

?>