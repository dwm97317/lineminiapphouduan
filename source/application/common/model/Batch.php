<?php
namespace app\common\model;

use app\common\model\BatchTemplateItem;
use think\Hook;
use app\common\model\Logistics;
use app\store\model\Inpack;
/**
 * 批次模型
 * Class Shop
 * @package app\common\model\store
 */
class Batch extends BaseModel
{
    protected $name = 'batch';

    /**
     * 批次详情
     * @param $forwarder
     * @return static|null
     * @throws \think\exception\DbException
     */
    public static function detail($batch_id)
    {
        return static::get($batch_id);
    }
    
    public function setBatchLog($list){
        $Inpack = new Inpack;
        foreach ($list as $key=>$val){
            if($val['last_time']<=time()){
                $templatelist = (new BatchTemplateItem())->where('template_id',$val['template']['template_id'])->order('step_num asc')->select();
                
                $logtemplate = $templatelist[$val['step_num']];
                
                if(count($templatelist)>$val['step_num']+1){
                    $next_logtemplate = $templatelist[$val['step_num']+1];  
                }elseif(count($templatelist) ==$val['step_num']+1){
                    $next_logtemplate = $templatelist[$val['step_num']];  
                    $val->save(['is_over'=>1]);
                }else{
                    continue;
                }
                
                $next_logtemplate = $templatelist[$val['step_num']+1]; 
                $inpacklist = $Inpack->where('batch_id',$val['batch_id'])->where('is_delete',0)->select();
                foreach ($inpacklist as $v){
                    Logistics::addbatchLogs($v['order_sn'],$logtemplate['title'],$logtemplate['content']);
                }
                $val->save(['step_num'=>$val['step_num']+1,'last_time'=> time() + $next_logtemplate['wait_time']*3600]);
            }
        }
        return true;
    }
    
    /**
     * 订单模型初始化
     */
    public static function init()
    {
        parent::init();
        // 监听加盟商订单行为管理
        $static = new static;
        Hook::listen('Batch', $static);
    }
    
        
    public function template(){
        return $this->belongsTo('app\store\model\BatchTemplate','template_id','template_id');
    }

}