<?php

namespace app\store\model;

use app\common\model\Article as ArticleModel;
use app\store\model\article\Category;
/**
 * 文章模型
 * Class Article
 * @package app\store\model
 */
class Article extends ArticleModel
{
    /**
     * 获取文章列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->with(['image', 'category'])
            ->where('is_delete', '=', 0)
            ->order(['article_sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);

    }

    /**
     * 新增记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        if (empty($data['image_id'])) {
            $this->error = '请上传封面图';
            return false;
        }
        if (empty($data['article_content'])) {
            $this->error = '请输入文章内容';
            return false;
        }
        $where['category_id'] = $data['category_id'];
        $where['wxapp_id'] = self::$wxapp_id;
        $res=(new Category())->where($where)->find();
        $where['is_delete'] = 0 ;
        $res2= $this->where($where)->find();
           if(in_array($res['belong'],[3,4]) && $res2){
                $this->error = '此菜单只能添加一篇';
                return false;
           }
        $data['wxapp_id'] = self::$wxapp_id;
        return $this->allowField(true)->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        if (empty($data['image_id'])) {
            $this->error = '请上传封面图';
            return false;
        }
        if (empty($data['article_content'])) {
            $this->error = '请输入文章内容';
            return false;
        }
   
        $where['category_id'] = $data['category_id'];
        $where['wxapp_id'] = self::$wxapp_id;
        $res=(new Category())->where($where)->find();
        $where['is_delete'] = 0 ;
        $res2= $this->where($where)->find();
      
           if($res['belong']==3 && (isset($res2['article_id']) && $res2['article_id'] != $data['article_id'])){
                $this->error = '【关于我们】只能添加一篇';
                return false;
           }
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 获取文章总数量
     * @param array $where
     * @return int|string
     */
    public static function getArticleTotal($where = [])
    {
        $model = new static;
        !empty($where) && $model->where($where);
        return $model->where('is_delete', '=', 0)->count();
    }

}