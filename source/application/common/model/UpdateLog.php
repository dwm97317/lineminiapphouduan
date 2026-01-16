<?php

namespace app\common\model;

use think\Request;
/**
 * 更新日志
 * Class UpdateLog
 * @package app\common\model
 */
class UpdateLog extends BaseModel
{
    protected $name = 'updatelog';
  
    /**
     * 获取列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return self::useGlobalScope(false)->order(['create_time' => 'desc'])
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

        if (empty($data['log_content'])) {
            $this->error = '请输入日志内容';
            return false;
        }
        $data['create_time'] =time();
        $data['update_time'] =time();
        return self::useGlobalScope(false)->insert($data);
    }
    

    /**
     * 系统更新日志
     * @param $article_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($article_id)
    {
        return self::get($article_id);
    }
    
        /**
     * 文章详情：HTML实体转换回普通字符
     * @param $value
     * @return string
     */
    public function getLogContentAttr($value)
    {
        return htmlspecialchars_decode($value);
    }

}
