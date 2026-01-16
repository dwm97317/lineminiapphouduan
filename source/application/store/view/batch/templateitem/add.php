<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">添加节点</div>
                                <input type="hidden" class="tpl-form-input" name="batch[title]"
                                           placeholder="请输入节点名称" required>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 节点名称 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input type="text" class="tpl-form-input" name="batch[title]"
                                           placeholder="请输入节点名称" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 节点物流内容 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input type="text" class="tpl-form-input" name="batch[content]"
                                           placeholder="请输入节点物流内容" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 第几步 </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input type="number" min='1' max='50' class="tpl-form-input" name="batch[step_num]"
                                           placeholder="请输入第几步" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 触发等待时间（小时） </label>
                                <div class="am-u-sm-3 am-u-end">
                                    <input type="text" class="tpl-form-input" name="batch[wait_time]"
                                           placeholder="请输入触发等待时间" required>
                                </div>
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