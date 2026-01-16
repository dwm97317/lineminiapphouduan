<link rel="stylesheet" href="assets/store/css/index.css">
<div id="app" v-cloak class="page-statistics-data row-content am-cf">
   <!-- 排行榜 -->
    <div class="row">
        <div class="am-u-sm-12 am-margin-bottom">
            <div class="widget-ranking widget am-cf">
                <div class="widget-head">
                    <div class="widget-title">渠道排行榜</div>
                </div>
                <div class="widget-body am-cf">
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-center" width="15%">排名</th>
                            <th class="am-text-left" width="15%">渠道</th>
                            <th class="am-text-center" width="10%">渠道总订单</th>
                            <th class="am-text-center" width="10%">超时订单量</th>
                            <th class="am-text-center" width="10%">渠道超时率</th>
                            <th class="am-text-center" width="20%">订单总额</th>
                            <th class="am-text-center" width="20%">查看路线数据</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(item, index) in ditchRanking">
                            <td class="am-text-middle am-text-center">
                                <div v-if="index < 3 && item.total_sales_num > 0" class="ranking-img">
                                    <img :src="'assets/store/img/statistics/ranking/0' + (index + 1) + '.png'" alt="">
                                </div>
                                <span v-else>{{ index + 1 }}</span>
                            </td>
                            <td class="am-text-middle">
                                <p class="ranking-item-title am-text-truncate">{{ item.ditch_name }}</p>
                            </td>
                            <td class="am-text-middle am-text-center">{{ item.total_sales_num }}</td>
                            <td class="am-text-middle am-text-center">{{ item.total_exced }}</td>
                            <td class="am-text-middle am-text-center">{{ item.exced_ratio }}</td>
                            <td class="am-text-middle am-text-center">{{ item.sales_volume }}</td>
                            <td class="am-text-middle am-text-center">
                                <div class="tpl-table-black-operation">
                                    <a href="javascript:void(0);" class="j-wuliu tpl-table-black-operation-green" onclick="linedata(this)"  :data-id='item.ditch_id' > 
                                        <i class="iconfont icon-755danzi "></i> 查看路线数据
                                    </a>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/echarts.min.js"></script>
<script src="assets/common/js/echarts-walden.js"></script>
<script src="assets/common/js/vue.min.js?v=1.1.35"></script>
<script src="assets/store/js/index.js"></script>
<script id="tpl-wuliu" type="text/template">
    <div class="am-padding-xs am-padding-top">
        <form class="am-form tpl-form-line-form" method="post" action="">
            <div class="am-tab-panel am-padding-0 am-active">
            
                <div class="widget-body am-cf">
                    <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black">
                        <thead>
                        <tr>
                            <th class="am-text-left" width="15%">渠道</th>
                            <th class="am-text-center" width="10%">渠道总订单</th>
                            <th class="am-text-center" width="10%">超时订单量</th>
                            <th class="am-text-center" width="10%">渠道超时率</th>
                            <th class="am-text-center" width="20%">订单总额</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{each data value}}
                        <tr>
                            <td class="am-text-middle">
                                <p class="ranking-item-title am-text-truncate">{{ value.name }}</p>
                            </td>
                            <td class="am-text-middle am-text-center">{{ value.total_order }}</td>
                            <td class="am-text-middle am-text-center">{{ value.exceed }}</td>
                            <td class="am-text-middle am-text-center">{{ value.exced_ratio }}</td>
                            <td class="am-text-middle am-text-center">{{ value.total_free }}</td>

                        </tr>
                        {{/each}} 
                        </tbody>
                    </table>
                </div>
              
            </div>
        </form>
    </div>
</script>
<script>
    function linedata(_this){
        var ditch_id = _this.getAttribute('data-id');
        $.ajax({
			type: 'post',
			url: "<?= url('store/tr_order/getlinedata') ?>",
			data: {ditch_id: ditch_id},
			dataType: "json",
			success: function(res) {
				if (res.code == 1) {
				    console.log(res.data,87);
        				$.showModal({
                         title: '渠道下的路线数据情况'
                        , area: '800px'
                        , content: template('tpl-wuliu', res.data)
                        , uCheck: false
                        , success: function (index) {
                            
                        }
                        ,yes: function (index) {
                            window.location.reload();
                        }
                    });
				}
			}
		})
    } 
</script>
<script type="text/javascript">
    
    new Vue({
        el: '#app',
        data: {
            ditchRanking: <?= \app\common\library\helper::jsonEncode($ditchRanking) ?>,
        },

        mounted() {

        },

        methods: {

            // 监听事件：日期选择快捷导航
            onFastDate: function (days) {
                var startDate, endDate;
                // 清空日期
                if (days === 0) {
                    this.survey.dateValue = [];
                } else {
                    startDate = $.getDay(-days);
                    endDate = $.getDay(0);
                    this.survey.dateValue = [startDate, endDate];
                }
                // api: 获取数据概况
                this.__getApiData__survey(startDate, endDate);
            },

            // 监听事件：日期选择框改变
            onChangeDate: function (e) {
                // api: 获取数据概况
                this.__getApiData__survey(e[0], e[1]);
            },

            // 获取数据概况
            __getApiData__survey: function (startDate, endDate) {
                var app = this;
                // 请求api数据
                app.survey.loading = true;
                // api地址
                var url = '<?= url('statistics.data/survey') ?>';
                $.post(url, {
                    startDate: startDate,
                    endDate: endDate
                }, function (result) {
                    app.survey.values = result.data;
                    app.survey.loading = false;
                });
            },
        }

    });

</script>