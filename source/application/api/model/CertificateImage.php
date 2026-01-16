<?php

namespace app\api\model;
use app\common\model\CertificateImage as CertificateImageP;
/** 
 * 商品图片模型
 * Class GoodsImage
 * @package app\api\model
 */
class CertificateImage extends CertificateImageP
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
    ];
    

}
