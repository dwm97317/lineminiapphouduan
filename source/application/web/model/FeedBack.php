<?php
namespace app\web\model;
use app\common\model\FeedBack as FeedBackModel;

/**
 * 意见反馈模型
 * Class Express
 * @package app\web\model
 */
class FeedBack extends FeedBackModel
{
    
    protected $createTime = null;
    protected $updateTime = null;
    
    /**
     * 建议反馈
     * @param $data
     * @return mixed
     */
    public function add($data)
    { 
        $data['store_id'] = self::$wxapp_id;
        return $this->save($data);            
    }

}