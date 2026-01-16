<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Line as LineModel;
use app\store\model\Countries;
use app\store\model\Category;
use app\common\model\Setting;
use app\store\model\LineService;

/**
 * 线路设置
 * Class Delivery
 * @package app\store\controller\setting
 */
class Line extends Controller
{
    /**
     * 线路列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new LineModel();
        $query = $this->getData();
        $list = $model->getList($query);
        $list = dataMapRender($list,'free_mode',[
          1 => '阶梯计费',
          2 => '首/续重计费',
          3 => '范围区间计费',
          4 => '重量区间计费'
        ]);
 
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $model = (new LineModel())->details($id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加配送模板
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $set = Setting::detail('store')['values'];
        $lineservice = (new LineService())->getListAll();
        // dump($lineservice);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('add',compact('set','lineservice'));
        }
        // 新增记录
        $model = new LineModel();
        if ($model->add($this->postData('line'))) {
            return $this->renderSuccess('添加成功', url('setting.line/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    
    /**
     * 复用路线
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function copyline($id)
    {
        // 新增记录
        $model = new LineModel();
        if(!$id){
           return $this->renderError($model->getError() ?: '路线id不能为空'); 
        }
        $linedata = $model->where('id',$id)->find();
        unset($linedata['id']);
        $linedata['name'] = $linedata['name']."副本";
        $linedata['created_time'] = time();
        if ($model->insert($linedata->toArray())) {
            return $this->renderSuccess('复用成功', url('setting.line/index'));
        }
        return $this->renderError($model->getError() ?: '复用失败');
    }

    /**
     * 编辑配送模板
     * @param $delivery_id
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        //模板详情
        $model = (new LineModel())->details($id);
        $result = [];
        $model['free_rule'] = json_decode($model['free_rule'],true);
        if($model['free_mode']==4 || $model['free_mode']==3){
            foreach ($model['free_rule'] as $key=> $val){
                !isset($val['weight_unit']) && $val['weight_unit'] = 1;
                $result[] = $val;
            }
            $model['free_rule']  = $result;
        }
       
    
        $country = [];
        $category = [];
        $country = (new Countries())->where('status','=',1)->select();
        $category = (new Category())->where('parent_id','<>',0)->select();
        $countryId = array_column($country->toArray(),null,'id');
        $categoryId = array_column($category->toArray(),null,'category_id');
        $lineservice = (new LineService())->getListAll();
        $country_text = []; //城市
        $category_text = [];//分类
        if ($model['countrys']){
            $modelCountryIds = explode(',',$model['countrys']);
            foreach ($modelCountryIds as $value) {
                $cres = (new Countries())->where('id',$value)->where('status',1)->find();
                if(!empty($cres)){
                    $country_text[] = $countryId[$value];
                }
                
            }
        }

        if ($model['categorys']){
            $modelCategoryIds = explode(',',$model['categorys']);
            foreach ($modelCategoryIds as $value) {
                $category_text[] = $categoryId[$value];
            }
        }
          
        $model['country'] = $country_text;
        $model['category'] = $category_text;
        $set = Setting::detail('store')['values'];
  
        if (!$this->request->isAjax()) {
            // dump($set);die;
            return $this->fetch('edit', compact('model','country','set','lineservice'));
        }
        //  dump($this->postData('line'));die;
        // 更新记录
        if ($model->edit($this->postData('line'))) {
            return $this->renderSuccess('更新成功', url('setting.line/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
