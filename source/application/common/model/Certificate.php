<?php

namespace app\common\model;

use think\Db;

/**
 * 凭证模型
 * Class Comment
 * @package app\common\model
 */
class Certificate extends BaseModel
{
    protected $name = 'certificate';
    protected $pk ='id';
    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('User');
    }
    
     
   /**
     * 关联图片表
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('CertificateImage','cert_id')->order(['id' => 'asc']);
    }
    /**
     * 凭证详情
     * @param $id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($id)
    {
        return self::get($id);
    }
    
    /**
     * 删除记录
     * @return bool|int
     */
    public function remove($id)
    {
        return $this->delete($id);
    }
    
     /**
     * 修改状态
     * @return bool|int
     */
    public function edit($id,$cert_status)
    {
        return $this->where('id',$id)->update(['cert_status'=>$cert_status]);
    }


    


    /**
     * 添加图片
     * @param $images
     * @return int
     */
    private function addCertificateImages($images)
    {
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::$wxapp_id
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($scoreType)
    {
        switch ($scoreType) {
            case '1':
                return $this
                ->where('wxapp_id', '=',self::$wxapp_id)
                ->where('cert_status', '=',1)
                ->order('create_time','desc')
                ->paginate(15, false, [
                    'query' => request()->request()
                ]);
                break;
                
            case '2':
                return $this
                ->where('wxapp_id', '=',self::$wxapp_id)
                ->where('cert_status', '=',2)
                ->order('create_time','desc')
                ->paginate(15, false, [
                    'query' => request()->request()
                ]);
                break;
                
            case '3':
                return $this
                ->where('wxapp_id', '=',self::$wxapp_id)
                ->where('cert_status', '=',3)
                ->order('create_time','desc')
                ->paginate(15, false, [
                    'query' => request()->request()
                ]);
                break;    
            default:
                $res=  $this->with(['user','image.file'])
                ->where('wxapp_id', '=',self::$wxapp_id)
                ->order('create_time','desc')
                ->paginate(15, false, [
                    'query' => request()->request()
                ]);
               
                return $res;
                break;
        }
        return false;
    }

}