<?php
namespace app\api\model;
use app\common\model\Logistics as LogisticsModel;
use app\common\library\express\Kuaidi100;
use app\api\service\trackApi\TrackApi;
use app\common\model\Setting;
use app\common\model\Inpack;
use app\api\model\Package;
use app\api\model\Express;
/**
 * 线路模型
 * Class Express
 * @package app\api\model
 */
class Logistics extends LogisticsModel
{ 
     public function setQuery($sn){
         return $this->where('order_sn',$sn);
     }
     
     public function getorderno($sn){
         $data =  $this->where('order_sn',$sn)->where('express_num',null)->order('created_time desc')->select()->toArray();
        //  dump($this->getLastsql());die;
         return $data;
     }
     
     public function getlogisticssn($sn){
         $data =  $this->where('logistics_sn',$sn)->where('express_num',null)->order('created_time desc')->select()->toArray();
        //  dump($this->getLastsql());die;
         return $data;
     }
     
     // 获取系统内部信息
     public function getList($sn){
        $data = $this->where('express_num',$sn)->order('created_time desc')->select()->toArray();
        return $data;
     }
     
     //查询转单包裹的物流信息
     public function getZdList($sn,$number,$wxappId){
        $setting = Setting::detail("store")['values'];
        $data = [];

        //查询17track物流信息
        if (!empty($setting['track17']['key'])){
        $track = (new TrackApi())->track(['track_sn'=>$sn,'t_number'=>$number,'wxapp_id'=> $wxappId]);
        if(!empty($track)){
                foreach ($track['tracking']['providers'][0]['events'] as $v){
                    $data[] = [
                      'logistics_describe' => $v['description'], 
                      'status_cn' => $v['location'],
                      'created_time' => str_replace(array('T','Z'),' ',$v['time_utc'])
                    ];
               }
           }
        }
        return $data;
     }
 
}