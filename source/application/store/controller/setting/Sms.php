<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Countries as CountryModel;


/**
 * 短信前缀
 * Class Delivery
 * @package app\store\controller\setting
 */
class Sms extends Controller
{


    /**
     * 短信国家列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = getFileData('assets/sms.json');
        return $this->fetch('index', compact('list'));
    }

    /**
     * 新增价格
     */
    // public function add(){
    //     if (!$this->request->isAjax()) {
    //         return $this->fetch('add');
    //     }
    //     // 新增记录
    //     $model = new CountryModel();
    //     if ($model->add($this->postData('country'))) {
    //         return $this->renderSuccess('添加成功', url('setting.country/index'));
    //     }
    //     return $this->renderError($model->getError() ?: '添加失败');
    // }


    /**
     * 编辑货架
     */
    // public function edit($id){
    //     // 模板详情
    //     $model = (new CountryModel())->details($id);
    //     if (!$this->request->isAjax()) {
    //         return $this->fetch('edit', compact('model'));
    //     }
    //     // 更新记录
    //     if ($model->edit($this->postData('country'))) {
    //         return $this->renderSuccess('更新成功', url('setting.country/index'));
    //     }
    //     return $this->renderError($model->getError() ?: '更新失败');
    // }
    
    // /**
    //  * 复用国家
    //  */
    // public function copy(){
    //     $model = (new CountryModel());
    //     $data = $model->select();
    //     if(count($data)>0){
    //       return $this->renderError($model->getError() ?: '请先删除现有的国家再复用'); 
    //     }
    //     if ($model->copy()) {
    //         return $this->renderSuccess('复用成功');
    //     }
    //     return $this->renderError($model->getError() ?: '复用失败');
    // }




//   /**
//      * 删除模板
//      * @param $delivery_id
//      * @return array
//      * @throws \think\Exception
//      * @throws \think\exception\DbException
//      * @throws \think\exception\PDOException
//      */
//     public function delete($id)
//     {
//         $model = (new CountryModel())->deletes($id);
//         if (!$model) {
//             return $this->renderError($model->getError() ?: '删除失败');
//         }
//         return $this->renderSuccess('删除成功');
//     }


}
