<?php

namespace app\api\model;

use app\common\model\Bank as BankModel;

class Bank extends BankModel
{

   public function getList($query=[]){
        return $this->setListQueryWhere($query)
        ->alias('a')
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }

    public function setListQueryWhere($query){
      return $this;
    }

}