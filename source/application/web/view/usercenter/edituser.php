<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">个人资料</h4>
        
    </div>
    <div class="detail-head">
         <div class="p-v-10 p-h-20 d-inline-block">	
            <img class="thumb-img img-circle pull-left" alt="" src="<?= $user['user']['avatarUrl'] ?>">
        </div>
        
    </div>
    <div class="card-body">
        <form role="form" id="ajaxForm">
        <div class="row">
            <div class="col-md-4">
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">昵称:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="nickName" placeholder="昵称"  value="<?= $detail['nickName'] ?>">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">手机号:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="mobile" placeholder="昵称"  value="<?= $detail['mobile'] ?>">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">性别:</label>
                    <div class="col-sm-9">
                        <div class="radio">
                            <input id="radio1" name="gender" value="1" type="radio" <?= $detail['gender']['value']==1?'checked':'' ?>>
                            <label for="radio1">男</label>
                        </div>
                        <div class="radio">
                            <input id="radio2" name="gender" value="2" type="radio" <?= $detail['gender']['value']==2?'checked':'' ?>>
                            <label for="radio2">女</label>
                        </div>
                        <div class="radio">
                            <input id="radio3" name="gender" value="0" type="radio" <?= $detail['gender']['value']==0?'checked':'' ?>>
                            <label for="radio3">未知</label>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">用户ID:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $user['user']['user_id'] ?></p>
                    </div>
                </div>
				<div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">Code</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $user['user']['user_code'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">邮箱:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="email" placeholder="邮箱"  value="<?= $detail['email'] ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">微信号:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="wechat" placeholder="微信号"  value="<?= $detail['wechat'] ?>">
                    </div>
                </div>
				<div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">生日</label>
                    <div class="col-sm-9">
                        <input  id="datepicker-1" name="birthday"  value="<?= $detail['birthday']=='0000-00-00 00:00:00'?'2000-06-01': $detail['birthday'] ?>" data-provide="datepicker" type="text" class="datepickerss form-control">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">身份ID:</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="identification_card" placeholder="身份ID"  value="<?= $detail['identification_card'] ?>">
                    </div>
                </div>
            </div>
        </div>

        </form>
        <button onclick="saveuser()" class="btn btn-primary btn-rounded">保存</button>            
    </div>
</div>
<script>
   function saveuser(){
         var formData = $('#ajaxForm').serializeArray(); 
         var formJson = {};
         formData.forEach((val)=>{
            formJson[val['name']] = val['value']; 
         });
         
        $.ajax({
            url:'<?=urlCreate('/index.php/web/user/editperson') ?>', 
            type:'POST',
            dataType:"json",
            data:{formData},
            success:function(res){
                if (res['code']==1){
                    window.location.href =res.data;
                }
    
            }
     })
   };
</script>
