<?php
namespace app\common\model;
use app\common\service\Message as MessageService;
use app\common\enum\OrderType as OrderTypeEnum;
/**
 * 包裹模型
 * Class OrderAddress
 * @package app\common\model
 */
class Package extends BaseModel
{
    protected $name = 'package';
    protected $updateTime = false;
    public function getWxappId(){
        return self::$wxapp_id;
    }
    /**
     * 确认入库发送消息通知
     * @param $orderList
     * @return bool
     */
    public function sendEnterMessage($orderList)
    {
        // 发送消息通知
    
        foreach ($orderList as $item) {
        
            // 发送微信通知（保留原有功能）
            MessageService::send('order.enter', [
                'order' => $item,
                'order_type' => OrderTypeEnum::MASTER,
            ]);
            
            // 发送LINE通知
            try {
                // 如果传入的是数组，需要加载图片关联数据
                if (is_array($item) && isset($item['id'])) {
                    $packageWithImages = self::with(['packageimage' => function($query) {
                        $query->with('file');
                    }])->find($item['id']);
                    
                    if ($packageWithImages && !empty($packageWithImages['packageimage'])) {
                        // 将图片数据添加到item中
                        $item['packageimage'] = $packageWithImages['packageimage']->toArray();
                    }
                }
                
                $lineService = new \app\common\service\message\line\Inwarehouse();
                $lineService->send($item);
            } catch (\Exception $e) {
                // 记录错误但不影响主流程
                log_write([
                    'describe' => 'LINE入库通知发送失败',
                    'package_id' => $item['id'] ?? 0,
                    'member_id' => $item['member_id'] ?? 0,
                    'error' => $e->getMessage(),
                    'time' => date('Y-m-d H:i:s')
                ]);
            }
           
        }
           
        return true;
    }
    
    // /**
    //  * 获取包裹单号的货架
    //  * @param $orderList
    //  * @return bool
    //  */
    // public function getShelfNo($pack_id){
        
        
    // }
    
    
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function packageimage()
    {
        return $this->hasMany('PackageImage','package_id','id')->order(['id' => 'asc']);
    }
    
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function packagexpress()
    {
        return $this->hasOne('PackageImage')->order(['id' => 'asc']);
    }
    
     /**
     * 关联包裹图片表
     * @return \think\model\relation\HasMany
     */
    public function shelfunititem()
    {
        return $this->hasOne('ShelfUnitItem','pack_id','id');
    }

   
}
