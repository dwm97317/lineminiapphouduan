<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border bottom">
                <h4 class="card-title">修改密码</h4>
            </div>
            <div class="card-body">
                <p class="width-90 m-b-30">修改密码后请仔细保管，如忘记密码可联系客服</p>
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="m-b-30">
    							<label class="control-label">请输入原始密码</label>
                                <input type="text" class="form-control" name="user[oldpassword]">
                            </div>
                            <div class="m-b-20">
                                <label for="noty-position">请输入新密码</label>
                                <input type="text" class="form-control" name="user[newpassword]">
                            </div>
    						 <div class="m-b-20">
                                <label for="noty-position">请再次输入新密码</label>
                                <input type="text" class="form-control" name="user[password_confirm]">
                            </div>
                            <div class="m-b-30">
                                <div class="m-t-30">
                                    <button class="btn btn-primary show-noty j-submit ">
                                        <span>确认修改</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>

window.onload = function(){
  var url = "<?php echo(urlCreate('/index.php/web/passport/login')) ?>";
  $(".btn").click(res=>{
     console.log(54678);
     var formData = $('#my-form').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     })
     $.ajax({
        url:'', 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            if (res['code']==0){
                swal(res.msg);
            }
            setTimeout((res)=>{
                window.location.href = url;
            },1000);
        }
     })
    return false;
 })   
};

</script>