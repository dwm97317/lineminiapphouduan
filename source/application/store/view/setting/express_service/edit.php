<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">编辑快递标签</div>
                </div>
                <div class="widget-body am-fr">
                    <form class="am-form tpl-form-line-form" method="post">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label form-require">标签名称 </label>
                            <div class="am-u-sm-9">
                                <input type="text" class="tpl-form-input" name="service_name"
                                       value="<?= $model['service_name'] ?>" required>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-form-label form-require">排序 </label>
                            <div class="am-u-sm-9">
                                <input type="number" class="tpl-form-input" name="sort"
                                       value="<?= $model['sort'] ?>">
                                <small>数字越小越靠前</small>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
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
        $('.j-submit').superForm();
    });
</script>
