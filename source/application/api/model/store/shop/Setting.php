<?php
namespace app\api\model\store\shop;

use app\common\model\store\shop\Setting as SettingModel;

/**
 * 加盟商设置模型
 * Class Setting
 * @package app\api\model\dealer
 */
class Setting extends SettingModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    public function getShareSetting(){
        return $this->where(['key'=>'basic'])->find();
    }
    
    public function getDealerSetting(){
        return $this->where(['key'=>'qrcode'])->find();
    }
}