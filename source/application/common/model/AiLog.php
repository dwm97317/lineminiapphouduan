<?php

namespace app\common\model;

use think\Request;
/**
 * 更新日志
 * Class UpdateLog
 * @package app\common\model
 */
class AiLog extends BaseModel
{
    protected $name = 'ailog';
  
    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($query)
    {
        !empty($query['start_time']) && $this->where('create_time', '>', strtotime($query['start_time']));
        !empty($query['end_time']) && $this->where('create_time', '<', strtotime($query['end_time']) + 86400);
        !empty($query['search']) && $this->where('user_id','like','%'.$query['search'].'%');
        return $this->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
    }
    
     /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {

        if (empty($data['content'])) {
            $this->error = '请输入内容';
            return false;
        }
        $data['create_time'] =time();
        return self::useGlobalScope(false)->insert($data);
    }
    
    
        /**
     * 文章详情：HTML实体转换回普通字符
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

}
