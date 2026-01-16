<?php

namespace app\common\model;

/**
 * 商品评价图片模型
 * Class CommentImage
 * @package app\common\model
 */
class CertificateImage extends BaseModel
{
    protected $name = 'certificate_image';
    protected $updateTime = false;
    protected $autoWriteTimestamp = false; // Disable automatic timestamp handling

    /**
     * 关联文件库
     * @return \think\model\relation\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo('UploadFile', 'image_id', 'file_id')
            ->bind(['file_path', 'file_name', 'file_url']);
    }

}
