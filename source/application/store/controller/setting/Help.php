<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;

/**
 * 设置-帮助信息
 * Class Help
 * @package app\store\controller\setting
 */
class Help extends Controller
{
    public function tplMsg()
    {
        return $this->fetch('tplMsg');
    }
    
    
    public function menuset()
    {
        return $this->fetch('menuSet');
    }
    
     public function hotCategory()
    {
        return $this->fetch('hotCategory');
    }

}