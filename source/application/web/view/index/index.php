   <!-- Content Wrapper START -->
   <div class="row">
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="media justify-content-between">
                                            <div>
                                                <p class="">未入库</p>
                                                <h2 class="font-size-28 font-weight-light"><?= $data['pack_count1']??0 ?></h2>
                                            </div>
                                            <div class="align-self-end">
                                                <i class="ti-credit-card font-size-70 text-success opacity-01"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="media justify-content-between">
                                            <div>
                                                <p class="">已入库</p>
                                                <h2 class="font-size-28 font-weight-light"><?= $data['pack_count2']??0 ?></h2>
                                                </span>
                                            </div>
                                            <div class="align-self-end">
                                                <i class="ti-pie-chart font-size-70 text-info opacity-01"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="media justify-content-between">
                                            <div>
                                                <p class="">未付款订单</p>
                                                <h2 class="font-size-28 font-weight-light"><?= $data['no_pay']??0 ?></h2>
                                            </div>
                                            <div class="align-self-end">
                                                <i class="ti-bar-chart font-size-70 text-danger opacity-01"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="media justify-content-between">
                                            <div>
                                                <p class="">已发货订单</p>
                                                <h2 class="font-size-28 font-weight-light"><?= $data['yes_pay']??0 ?></h2>
                                            </div>
                                            <div class="align-self-end">
                                                <i class="ti-user font-size-70 text-primary opacity-01"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">账户信息</h4>
                                        <div class="card-toolbar">
                                            <ul>
                                                <li>
                                                    <a class="text-gray" href="javascript:void(0)">
                                                        <i class="mdi mdi-dots-vertical font-size-20"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="border bottom">
                                        <div class="card-body p-v-15">
                                            <div class="row align-items-center">
                                                <div class="col-sm">
                                                    <p class="m-b-0">余额（元）</p>
                                                    <h2 class="font-weight-light m-b-0 font-size-28"><?= $userData['balance']??0 ?></h2>
                                                </div>
                                                <div class="col-sm">
                                                    <div class="text-right m-t-20">
                                                        <button class="btn btn-info m-b-0 m-r-5">
                                                            <i class="mdi mdi-credit-card-plus font-size-16 m-r-5"></i>
                                                            <span>充值</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card widget-credit-card border-radius-8 bg-gradient-info">
                                                    <div class="card-body">
                                                        <div class="m-b-40">
                                                            <h3 class="font-weight-light d-inline-block"><?= $user['user']['user_name'] ?></h3>
                                                            <div class="float-right">
                                                                <img style="width: 68px;border-radius: 68px;" src="<?= $user['user']['avatarUrl']?$user['user']['avatarUrl']:'/web/static/picture/thumb-16.jpg' ?>" alt="">
                                                            </div>    
                                                        </div>
                                                        <div class="m-b-20">
                                                            <span class="text-semibold">手机号</span>
                                                            <span class="m-l-5"><?= $user['user']['mobile'] ?></span>
                                                        </div>
                                                        <h3 class="font-weight-light">
                                                            <span class="m-r-5">会员ID</span>
                                                            <span class="m-r-5"><?= $user['user']['user_id'] ?></span>
                                                        </h3>
                                                        <span class="font-weight-light">会员CODE:<?= $user['user']['user_code'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div> 
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">包裹数量</h4>
                                        <div class="card-toolbar">
                                            <ul>
                                                <li>
                                                    <a class="text-gray" href="javascript:void(0)">
                                                        <i class="mdi mdi-dots-vertical font-size-20"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <ul class="list-inline">
                                                    <li class="m-r-20">
                                                        <a href="" class="text-semibold text-gray ">年度包裹量（个）</a>
                                                    </li>
                                                </ul>
                                                <div class="m-t-20">
                                                    <canvas id="account-chart" class="chart" style="height: 320px"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             
                        </div>
                        <!--<div class="row">-->
                        <!--    <div class="col-md-12">-->
                        <!--        <div class="card">-->
                        <!--            <div class="card-header">-->
                        <!--                <h4 class="card-title">最新订单</h4>-->
                        <!--                <div class="card-toolbar">-->
                        <!--                    <ul>-->
                        <!--                        <li>-->
                        <!--                            <a class="text-gray" href="javascript:void(0)">-->
                        <!--                                <i class="mdi mdi-dots-vertical font-size-20"></i>-->
                        <!--                            </a>-->
                        <!--                        </li>-->
                        <!--                    </ul>-->
                        <!--                </div>-->
                        <!--            </div>-->
                        <!--            <table class="table table-xl">-->
                        <!--                <tbody>-->
                        <!--                    <?php if ( !empty($list) && !$list->isEmpty()): foreach ($list as $item): ?>-->
                        <!--                    <tr>-->
                        <!--                        <td class="text-center"><span><?= $item['country'] ?></span></td>-->
                        <!--                        <td><span class="font-size-13"><?= $item['t_order_sn'] ?></span></td>-->
                        <!--                        <td class="m-b-0 text-success"><span class="font-size-13"><a class="j-search" data-id="<?= $item['t_order_sn'] ?>" href="javascript:void(0)">查询</a></span></td>-->
                        <!--                    </tr>-->
                        <!--                    <?php endforeach; else: ?>-->
                        <!--                        <tr>-->
                        <!--                            <td colspan="11" class="am-text-center">暂无记录</td>-->
                        <!--                        </tr>-->
                        <!--                    <?php endif; ?>-->
                        <!--                </tbody>-->
                        <!--            </table>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                      
                <!-- Content Wrapper END -->
<script>
window.onload = function(){
   +function($, window){
	var bankDashboard = {};

	bankDashboard.init = function() {
		//Account Chart
		var accountCtx = document.getElementById('account-chart').getContext('2d');		
		var accountChart = new Chart(accountCtx, {
			type: 'line',
			data: {
				labels: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一", "十二"],
				datasets: [
				{
					label: 'Series B',
					backgroundColor: app.colors.transparent,
					borderColor: app.colors.success,
					data: [<?= $zhe ?>]
				}],
			},
			options: {
				legend: {
					display: false
				},
				maintainAspectRatio: false,
				elements: {
					line: {
						tension: 0.3,
						borderWidth: 2.2
					}
				},
				scales: {
					xAxes: [{gridLines: { color: app.colors.transparent }}],
					yAxes: [{gridLines: { color: app.colors.borderColor }}]
				}
			}
		});
	};	

	window.bankDashboard = bankDashboard;

}(jQuery, window);

// initialize app
+function($) {
	bankDashboard.init();		
}(jQuery); 

var url = "<?php echo(urlCreate('/web/package/trajectory')) ?>";
$(".j-search").click(
        function(){
             var $tabs, data = $(this).data();
             location.replace(url+ '&express_num=' + data.id);
        })

    
}
</script>