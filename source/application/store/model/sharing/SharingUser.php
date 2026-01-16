<?php
namespace app\store\model\sharing;
use app\common\model\sharing\SharingUser as SharingUserModel;
use app\common\model\User;
class SharingUser extends SharingUserModel {
    
    public function getList($query=[]){
        return $this->setListQueryWhere($query)
        ->with('user')
        ->where(['status'=>2])
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }
    
    public function getListX($query=[]){
        return $this->setListQueryWhere($query)
        ->with('user')
        ->where(['status'=>1])
        ->paginate(10,false,[
            'query'=>\request()->request()
        ]);
    }
    
    public function modifyStatus($ids,$form){
        if ($form['status'] == '3'){
            if ($form['reason']==''){
                $this->error = '请输入拒绝原因';
                return false;
            }
        }
        $data = $this->where('id','in',$ids)->select();
        $userIds = array_column($data->toArray(),'user_id');
        if ($form['status'] == 1){
            (new User())->where('user_id','in',$userIds)->update(['is_sharp'=>1]);
        }
        return $this->where('id','in',$ids)->update($form);
    }

    public function setListQueryWhere($query){
        return $this;
    }
    
    public function user(){
        return $this->belongsTo('app\store\model\User','user_id');
    }
}  

?>