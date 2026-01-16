<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title>集运管理系统登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <!--<link rel="stylesheet" href="assets/store/css/login/style.css?v=<?= $version ?>"/>-->
    
    <link rel="stylesheet" href="assets/store/css/bootstrap.css">
    <link href="assets/store/css/login.css" rel="stylesheet">
</head>
<body class="page-login-v3">
    <div class="app">
        <div class="layout bg-gradient-info">
            <div class="container">
                <div class="row full-height align-items-center">
                    <div class="col-md-7 d-none d-md-block">
                        <img class="img-fluid" src="assets/store/img/wuliu.png" alt="" style="max-width: 85%;">
                        <!--<div class="m-t-15 m-l-20">-->
                        <!--    <h1 class="font-weight-light font-size-35 text-white">集运管理系统</h1>-->
                        <!--    <p class="text-white width-70 text-opacity m-t-25 font-size-16">想一千次，不如去做一次。华丽的跌倒，胜过无谓的徘徊</p>-->
                        <!--    <div class="m-t-60">-->
                        <!--        <a href="###" class="text-white text-link m-r-15"></a>-->
                        <!--        <a href="###" class="text-white text-link">2022年10月08日</a>-->
                        <!--    </div>-->
                        <!--</div>-->
                    </div>
                    <div class="col-md-5">
                        <div class="card card-shadow">
                            <div class="card-body">
                                <div class="p-h-15 p-v-40">
                                    <h2>集运管理系统</h2>
                                    <p class="m-b-15 font-size-13"></p>
                                    <form id="login-form" class="login-form">
                                        <div class="form-group">
                                            <input type="text"  name="User[user_name]" class="form-control" placeholder="用户名">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="User[password]" class="form-control" placeholder="密码">
                                        </div>
                                        <button id="btn-submit" class="btn btn-block btn-lg btn-gradient-success">登录</button>
                                        <!--<div class="text-center m-t-30">-->
                                        <!--    <a href="" class="text-gray text-link text-opacity">忘记密码?</a>-->
                                        <!--</div>-->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!--<div class="container">-->
<!--    <div id="wrapper" class="login-body">-->
<!--        <div class="login-content">-->
<!--            <div class="brand">-->
<!--                <img alt="logo" class="brand-img" src="assets/store/img/login/logo.png?v=<?= $version ?>">-->
<!--                <h2 class="brand-text">商协会系统</h2>-->
<!--            </div>-->
<!--            <form id="login-form" class="login-form">-->
<!--                <div class="form-group">-->
<!--                    <input class="" name="User[user_name]" placeholder="请输入用户名" type="text" required>-->
<!--                </div>-->
<!--                <div class="form-group">-->
<!--                    <input class="" name="User[password]" placeholder="请输入密码" type="password" required>-->
<!--                </div>-->
<!--                <div class="form-group">-->
<!--                    <button id="btn-submit" type="submit">-->
<!--                        登录-->
<!--                    </button>-->
<!--                </div>-->
<!--            </form>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
</body>
<script src="assets/common/js/jquery.min.js"></script>
<script src="assets/common/plugins/layer/layer.js?v=<?= $version ?>"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script>
    $(function () {
        // 表单提交
        var $form = $('#login-form');
        $form.submit(function () {
            var $btn_submit = $('#btn-submit');
            $btn_submit.attr("disabled", true);
            $form.ajaxSubmit({
                type: "post",
                dataType: "json",
                // url: '',
                success: function (result) {
                    $btn_submit.attr('disabled', false);
                    if (result.code === 1) {
                        layer.msg(result.msg, {time: 1500, anim: 1}, function () {
                            window.location = result.url;
                        });
                        return true;
                    }
                    layer.msg(result.msg, {time: 1500, anim: 6});
                }
            });
            return false;
        });
    });
</script>
</html>
