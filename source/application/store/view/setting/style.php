<?php
use app\common\enum\UserCodeType as UserCodeTypeEnum;
use app\common\enum\DeliveryType as DeliveryTypeEnum;
?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">后台样式设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    后台主题
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="wxapp[system_style]" value="10"
                                               data-am-ucheck  <?= $model['system_style'] == 10 ? 'checked' : '' ?>>
                                        原始主题
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="wxapp[system_style]" value="20"
                                               data-am-ucheck <?= $model['system_style'] == 20 ? 'checked' : '' ?>>
                                        蓝色主题
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="wxapp[system_style]" value="30"
                                               data-am-ucheck <?= $model['system_style'] == 30 ? 'checked' : '' ?>>
                                        科技主题
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="wxapp[system_style]" value="40"
                                               data-am-ucheck <?= $model['system_style'] == 40 ? 'checked' : '' ?>>
                                        科技粒子
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                               <label class="am-u-sm-3 am-form-label"></label>
                                    
                                    <small>注：后台主题切换后请先退出，重新登录后即可查看到最新主题；</small>
                            </div>
                            
               
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
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
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
