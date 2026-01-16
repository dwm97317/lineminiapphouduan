<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= $storetitle ?> - 登录</title>

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
        <div class="layout bg-gradient-info">
            <div class="container">
                <div class="row full-height align-items-center">
                    <div class="col-md-5 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <div class="p-15">
                                    <div class="m-b-30">
                                        <img class="img-responsive inline-block" src="static/picture/logo.png" alt="">
                                        <h2 class="inline-block pull-right m-v-0 p-t-15">登录</h2>
                                    </div>
                                    <p class="m-t-15 font-size-13">请输入用户名密码进行登录</p>
                                    <form id="ajaxForm">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="account" placeholder="请输入用户名">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control" name="password" placeholder="请输入密码">
                                        </div>
                                        <div class="checkbox font-size-13 d-inline-block p-v-0 m-v-0">
                                            <input id="agreement" name="agreement" type="checkbox">
                                            <label for="agreement">记住密码</label>
                                        </div>
                                        <div class="pull-right">
                                            <a href="">忘记密码</a>
                                        </div>
                                        <div class="m-t-20 text-right">
                                            <button class="btn btn-gradient-success">登录</button>
                                        </div>
                                        <small>没有账号请先点击注册 <a href="<?=urlCreate('/index.php/web/passport/register') ?>">立即注册</a></small>
                                    </form>
                                </div>
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
 var url = "<?php echo(urlCreate('/index.php/web/index')) ?>";
 console.log(url,54768);
 $(".btn").click(res=>{
    
     var formData = $('#ajaxForm').serializeArray(); 
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
</script>
</html>