<?php

namespace app\store\model;

use think\Cache;
use app\common\model\Category as CategoryModel;

/**
 * 商品分类模型
 * Class Category
 * @package app\store\model
 */
class Category extends CategoryModel
{
      public function getList($name){
            return $this
            ->where(function($query) use ($name) {
               $query->where('name','like','%'.$name.'%');
            })
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    //只获取顶级分类
    public function getListTop($name){
            return $this
            ->where('parent_id','=',0)
            ->where(function($query) use ($name) {
               $query->where('name','like','%'.$name.'%');
            })
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    //只获取指定顶级分类的子分类
    public function getListTopChild($categoryid){
            return $this
            ->where(function($query) use ($categoryid) {
               $query->where('parent_id','=',$categoryid);
            })
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    //只获取子分类
    public function getListChild($name){
            return $this
            ->where('parent_id','>',0)
            ->where(function($query) use ($name) {
               $query->where('name','like','%'.$name.'%');
            })
            ->paginate(300,false, [
                'query' => \request()->request()
            ]);
    }
    
    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::$wxapp_id;
//        if (!empty($data['image'])) {
//            $data['image_id'] = UploadFile::getFildIdByName($data['image']);
//        }
        $this->deleteCache();
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        // 验证：一级分类如果存在子类，则不允许移动
        if ($data['parent_id'] > 0 && static::hasSubCategory($this['category_id'])) {
            $this->error = '该分类下存在子分类，不可以移动';
            return false;
        }
        $this->deleteCache();
        !array_key_exists('image_id', $data) && $data['image_id'] = 0;
        return $this->allowField(true)->save($data) !== false;
    }

    /**
     * 删除商品分类
     * @param $categoryId
     * @return bool|int
     */
    public function remove($categoryId)
    {
        // 判断是否存在子分类
        if (static::hasSubCategory($categoryId)) {
            $this->error = '该分类下存在子分类，请先删除';
            return false;
        }
        $this->deleteCache();
        return $this->delete();
    }

    /**
     * 删除缓存
     * @return bool
     */
    private function deleteCache()
    {
        return Cache::rm('category_' . static::$wxapp_id);
    }

}
