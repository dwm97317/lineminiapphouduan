<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑文章分类</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="category[name]"
                                           value="<?= $model['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类类别 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="category[belong]" value="0" <?= $model['belong'] == 0 ? 'checked' : '' ?> data-am-ucheck>
                                        普通文章
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="category[belong]" value="1" <?= $model['belong'] == 1 ? 'checked' : '' ?> data-am-ucheck>
                                        违禁物品
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="category[belong]" value="2" <?= $model['belong'] == 2 ? 'checked' : '' ?> data-am-ucheck>
                                        新手问题
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="category[belong]" value="3" <?= $model['belong'] == 3? 'checked' : '' ?> data-am-ucheck>
                                        关于我们
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="category[belong]" value="4" <?= $model['belong'] == 4? 'checked' : '' ?> data-am-ucheck>
                                        包裹预报协议
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">分类排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" class="tpl-form-input" name="category[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
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
