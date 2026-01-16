<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>集运系统登录 - 注册</title>
    <!-- core dependcies css -->
    <link rel="stylesheet" href="/web/static/css/bootstrap.css">
    <link rel="stylesheet" href="/web/static/css/pace-theme-minimal.css">
    <link rel="stylesheet" href="/web/static/css/perfect-scrollbar.min.css">

    <!-- page css -->
    
    <!-- core css -->
    <link href="/web/static/css/font-awesome.min.css" rel="stylesheet">
    <link href="/web/static/css/themify-icons.css" rel="stylesheet">
    <link href="/web/static/css/sweet-alert.css" rel="stylesheet">
    <link href="/web/static/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="/web/static/css/animate.min.css" rel="stylesheet">
    <link href="/web/static/css/app.css" rel="stylesheet">
</head>

<body>
    <div class="app">
        <div class="layout bg-white full-height">
            <div class="row no-gutters">
                <div class="col-md-8 d-none d-md-block align-self-end" style="background-image: url('/web/static/image/img-32.jpg')">
                    <div class="row full-height">
                        <div class="col-md-10 align-self-center">
                            <div class="m-b-50 m-l-70">
                                <img class="img-fluid" src="/web/static/picture/logo-white.png" alt="">
                                <div class="m-t-15 m-l-20">
                                    <h1 class="font-weight-light font-size-35 text-white">Exploring The World</h1>
                                    <p class="text-white width-60 text-opacity m-t-25 font-size-16">Climb leg rub face on everything give attitude nap all day for under the bed. Chase mice attack feet but rub face on everything hopped up.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 align-self-center">
                    <div class="row">
                        <div class="col-10 offset-1 col-sm-8 offset-sm-2">
                            <div class="p-v-25">
                                <h1 class="m-b-30">注册账号</h1>
                                <form id="ajaxForm">
                                    <div class="form-group">
                                        <label class="control-label">用户名</label>
                                        <input type="text" name="username" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">手机号</label>
                                        <input type="phone" name="mobile" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">密码</label>
                                        <input type="password" name="password" class="form-control" placeholder="密码">
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">确认密码</label>
                                        <input type="password" name="password_cfd" class="form-control" placeholder="确认密码">
                                    </div>
                                    <div class="checkbox font-size-13">
                                        <input id="agreement" name="agreement" type="checkbox" checked="">
                                        <label for="agreement">记住账号</label>
                                    </div>
                                    <div class="form-group m-t-20">
                                        <button type="button" class="btn btn-gradient-success btn-block btn-lg">注册</button>
                                    </div>
                                </form>
                                <p>已有账号? <a href="<?=urlCreate('/index.php/web/passport/login') ?>">登录</a></p>
                                <hr>
                                <small>注册完成即代表你同意 <a href="">《用户隐私协议》</a></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="/web/static/js/vendor.js"></script>
    <script src="/web/static/js/sweet-alert.js"></script>
    <script src="/web/static/js/app.min.js"></script>

    <!-- page js -->
    
</body>
<script>
 $(".btn").click(res=>{
    
     var formData = $('#ajaxForm').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     });
     if (formJson['password'] != formJson['password_cfd']){
         swal('请确认两次输入密码保持一致');
         return;
     }
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
                window.location.href = "<?php echo(urlCreate('/index.php/web/index')) ?>";
            },1000);    
        }
     })
    return false;
 })
</script>
</html>