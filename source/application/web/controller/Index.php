<?php
namespace app\web\controller;

use think\Db;
use think\Session;
/**
 * web
 * Class Index
 * @package app\web\controller
 */
class Index extends Controller
{
     
    public function index(){
        $zhe = '';    
       return $this->fetch('index',compact('data','zhe','list'));
    }
    
    public function passport(){
        Session::clear('yoshop_user');
        $this->redirect(urlCreate('passport/login'));
    }
     
}
