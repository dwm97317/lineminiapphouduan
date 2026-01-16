<!DOCTYPE html>
<html lang="zh">
<head>
<title><?= $setting['name'] ;?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="<?= $setting['name'] ;?>" />
<meta name="description" content="<?= $setting['desc'] ;?>" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
		function hideURLbar(){ window.scrollTo(0,1); } </script>
<link href="/template/template1/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
<link href="/template/template1/css/style.css" rel="stylesheet" type="text/css" media="all" />
<link href="/template/template1/css/font-awesome.css" rel="stylesheet"> 
</head>
	
<body>
<!-- banner -->
<div class="main_section_agile">
		<div class="agileits_w3layouts_banner_nav">
		   
			<nav class="navbar navbar-default">
				<div class="navbar-header navbar-left">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				<h1>
				    <a class="navbar-brand" href="<?=urlCreate('/index.php/web/track/search') ?>"><i class="fa fa-plane" aria-hidden="true"></i> <?= $setting['name'] ;?> </a></h1>

				</div>
			</nav>	
			<div class="clearfix"> </div> 
		</div>
</div>
<!--/ banner -->
<div class="banner1">
			
		<div class="w3_agileits_service_banner_info">
			<h2> <?= $setting['name'] ;?></h2>
		</div>
	</div>
<div class="inner_main_agile_section">
		<div class="container">
			<h3 class="w3l_header w3_agileits_header">物流轨迹 <span>查询</span></h3>
			<div class="grid_3 grid_4 w3layouts"></div>
			<div class="row">

				<div class="col-lg-12 in-gp-tb">
					<div class="input-group">
						<input id="express_num" name="express_num" style="height: 66px;" type="text" class="form-control" placeholder="请输入单号">
						<span class="input-group-btn">
							<button style="padding:22px 12px" class="btn btn-default" type="button">查询</button>
						</span>
					</div><!-- /input-group -->
				</div><!-- /.col-lg-6 -->
			</div><!-- /.row -->
			<div class="grid_3 grid_5 w3ls list">
				
			</div>
		</div>
	</div>
<!-- //typo -->

	
<!-- footer -->
	<div class="footer">
		<div class="container">
			<!--<div class="w3ls_address_mail_footer_grids">-->
			<!--	<div class="col-md-4 w3ls_footer_grid_left">-->
			<!--		<div class="wthree_footer_grid_left">-->
			<!--			<i class="fa fa-map-marker" aria-hidden="true"></i>-->
			<!--		</div>-->
			<!--		<p><?= $setting['desc'] ;?></p>-->
			<!--	</div>-->
			<!--	<div class="col-md-4 w3ls_footer_grid_left">-->
			<!--		<div class="wthree_footer_grid_left">-->
			<!--			<i class="fa fa-phone" aria-hidden="true"></i>-->
			<!--		</div>-->
			<!--		<p><?= $setting['service_phone'] ;?></p>-->
			<!--	</div>-->
			<!--	<div class="col-md-4 w3ls_footer_grid_left">-->
			<!--		<div class="wthree_footer_grid_left">-->
			<!--			<i class="fa fa-envelope-o" aria-hidden="true"></i>-->
			<!--		</div>-->
			<!--		<p><a href="mailto:info@example.com"><?= $setting['kefuemail'] ;?></a></p>-->
			<!--	</div>-->
			<!--	<div class="clearfix"> </div>-->
			<!--</div>-->
			<div class="agileinfo_copyright">
				<p>© 2017-2023 <?= $setting['name'] ;?>. All Rights Reserved </p>
			</div>
		</div>
	</div>
<!-- //footer -->
<script id="tpl-inpack" type="text/template">
{{ if data.length>0 }}
    {{each data value}}
    <div style="border-left: 2px solid #b5ccae;position: relative;display: flex;flex-wrap: nowrap;flex-direction: row;align-content: center;justify-content: flex-start;align-items: baseline;">
        <div class="dian" style="width: 10px;height: 10px;border-radius: 10px;background: #b5cdad;position: absolute;left: -6px;top:22px;"></div>
        <div class="alert alert-success" role="alert" style="margin-left: 20px;">
			<strong>{{ value.created_time}}</strong> {{value.logistics_describe}}
		</div>
	</div>
	<div style="clear:both;"></div>
    {{/each}}
{{ else }}
    <div class="alert alert-danger" role="alert">
		    暂无轨迹
		</div>
{{/if}}
</script>
<!-- start-smoth-scrolling -->
<!-- js -->
<script type="text/javascript" src="/template/template1/js/jquery-2.1.4.min.js"></script>
<script src="/assets/common/js/art-template.js"></script>

<!-- //js -->
<script type="text/javascript" src="/template/template1/js/move-top.js"></script>
<script type="text/javascript" src="/template/template1/js/easing.js"></script>
<script>
 var url = "<?=urlCreate('/index.php/web/track/search') ?>";
     $(".btn").click(res=>{
     var formData = $('#express_num').serializeArray(); 
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
                alert(res.msg);
            }else{
                //   console.log(res,999)
               var list = template('tpl-inpack',res.data)
             
                $('.list').html(list);
            }
            // setTimeout((res)=>{
            //     window.location.href = url;
            // },1000);
        }
     })
    return false;
 })
</script>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$(".scroll").click(function(event){		
			event.preventDefault();
			$('html,body').animate({scrollTop:$(this.hash).offset().top},1000);
		});
	});
</script>
<!-- start-smoth-scrolling -->
<!-- for bootstrap working -->
	<script src="/template/template1/js/bootstrap.js"></script>
<!-- //for bootstrap working -->
<!-- here stars scrolling icon -->
	<script type="text/javascript">
		$(document).ready(function() {
				
			$().UItoTop({ easingType: 'easeOutQuart' });
								
			});
	</script>
<!-- //here ends scrolling icon -->
</body>
</html>