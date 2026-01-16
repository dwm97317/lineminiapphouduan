<?php
namespace app\store\model\sharing;
use app\common\model\sharing\SharingOrderAddress as SharingOrderAddressModel;

class SharingOrderAddress extends SharingOrderAddressModel {
    
    public function getList($query){
        return $this
            ->setWhere($query)
            ->with(['country','User','storage'])
            ->paginate(15,false, [
                'query' => \request()->request()
            ]);
    }
    
}  

?>