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
        <div class="row">
            <div class="col-md-4">
                
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">昵称:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $detail['nickName'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">手机号:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $detail['mobile'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">性别:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $detail['gender']['text'] ?></p>
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
                        <p class="form-control-plaintext"><?= $detail['email'] ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">微信号:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $detail['wechat'] ?></p>
                    </div>
                </div>
				<div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">生日</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $detail['birthday'] ?></p>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label control-label text-dark">身份ID:</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext"><?= $detail['identification_card'] ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
                 
            <div class="col-sm-3">
                <div class="card">
                    <div class="p-30">
                        <p class="m-b-15">身份ID图片</p>
                        <img class="img-fluid w-100" src="<?= $detail['userimage']['file_path'] ?>" alt="">
                    </div>
                </div>
        
         
            </div>
        </div>
        <button onclick="edituser()" class="btn btn-primary btn-rounded">编辑资料</button>            
    </div>
</div>
<script>
   function edituser(){ 
     window.location.href = "<?=urlCreate('/index.php/web/user/editperson') ?>";
   };
</script>