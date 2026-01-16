<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="tips am-margin-top-sm">
                                <div class="pre">
                                    <p class="">
                                        1. 订阅消息仅支持小程序 "<strong>物流服务/仓储服务</strong>" 类目，请登录 "<a
                                                href="https://mp.weixin.qq.com" target="_blank">小程序运营平台</a>"，左侧菜单栏 "设置"
                                        - "基本设置" - "服务类目" 中添加
                                    </p>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订阅消息通知</div>
                            </div>
                            
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 包裹入库通知 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="submsg[order][enter][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['order']['enter']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="submsg[order][enter][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['order']['enter']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                    
                                </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 模板ID </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="submsg[order][enter][template_id]"
                                           value="<?= $values['order']['enter']['template_id'] ?>">
                                    <div class="help-block">
                                        <small>关键词：[包裹编号、物流单号、仓库名称、入库时间、备注]</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 订单发货通知 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="submsg[order][delivery][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['order']['delivery']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="submsg[order][delivery][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['order']['delivery']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                    
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 模板ID </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input"
                                           name="submsg[order][delivery][template_id]"
                                           value="<?= $values['order']['delivery']['template_id'] ?>">
                                    <div class="help-block">
                                        <small>关键词：[订单编号、发货时间、快递公司、快递单号]</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 物流状态更新通知 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                     <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="submsg[order][logistics][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['order']['logistics']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="submsg[order][logistics][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['order']['logistics']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                    
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 模板ID </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="submsg[order][logistics][template_id]"
                                           value="<?= $values['order']['logistics']['template_id'] ?>">
                                    <div class="help-block">
                                        <small>关键词：[运单号、包裹单号、包裹动态、更新时间]</small>
                                    </div>
                                </div>
                            </div>
                            
                              <div class="widget-head am-cf">
                                <div class="widget-title am-fl">分销商消息通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 分销商入驻审核通知 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input" name="submsg[dealer][apply][template_id]"
                                           value="<?= $values['dealer']['apply']['template_id'] ?>">
                                    <div class="help-block">
                                        <small>关键词：[申请时间、审核状态、审核时间、备注信息]</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 提现成功通知 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input"
                                           name="submsg[dealer][withdraw_01][template_id]"
                                           value="<?= $values['dealer']['withdraw_01']['template_id'] ?>">
                                    <div class="help-block">
                                        <small>关键词：[提现金额、打款方式、打款原因]</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 提现失败通知 </label>
                                <div class="am-u-sm-9 am-u-md-6 am-u-lg-5 am-u-end">
                                    <input type="text" class="tpl-form-input"
                                           name="submsg[dealer][withdraw_02][template_id]"
                                           value="<?= $values['dealer']['withdraw_02']['template_id'] ?>">
                                    <div class="help-block">
                                        <small>关键词：[提现金额、申请时间、原因]</small>
                                    </div>
                                </div>
                            </div>
                            <div id="shuttle" class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    
                                    <button type="submit"
                                            class="j-submit am-btn am-btn-sm am-btn-secondary am-margin-right-sm">保存
                                    </button>
                                  
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 一键配置
        $('.j-shuttle').on('click', function () {
            var url = "<?= url('wxapp.submsg/shuttle') ?>";
            var load = layer.load();
            layer.confirm('该操作将自动为您的小程序添加订阅消息<br>请先确保 "订阅消息" - "我的模板" 中没有记录<br>确定添加吗？', {
                title: '友情提示'
            }, function (index) {
                $.post(url, {}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                    layer.close(load);
                });
                layer.close(index);
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
