<?php
namespace app\common\model;

/**
 * 增值服务模型
 * Class OrderAddress
 * @package app\common\model
 */
class LineService extends BaseModel
{
    protected $name = 'line_services';
    protected $updateTime = false;
    
    public static  function detail($id){
        return (new static()) ->find($id);
    }
}
