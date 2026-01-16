<?php
/*
 * @Author: your name
 * @Date: 2022-04-27 10:21:00
 * @LastEditTime: 2022-04-27 11:08:46
 * @LastEditors: your name
 * @Description: 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 * @FilePath: \zhuanyun\source\application\web\controller\wxapp\Formid.php
 */

namespace app\web\controller\wxapp;

use app\web\controller\Controller;

/**
 * form_id 管理 (已废弃)
 * Class Formid
 * @package app\web\controller\wxapp
 */
class Formid extends Controller
{
    /**
     * 新增form_id
     * (因微信模板消息已下线，所以formId取消不再收集)
     * @param $formId
     * @return array
     */
    public function save($formId)
    {
        return $this->renderSuccess();
    }

}