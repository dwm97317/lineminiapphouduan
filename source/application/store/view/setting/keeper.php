<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货仓扫码入库</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示入库仓库
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_shop]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_shop]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示所属用户ID
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_user]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_user'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_user]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_user'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_user_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_user_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示所属货架
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_shelf]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_shelf'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_shelf]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_shelf'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_shelf_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_shelf_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示物品品类
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_category]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_category]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_category_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_category_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示热门分类
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_hot]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_hot'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_hot]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_hot'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示重量
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_weight]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_weight'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_weight]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_weight'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_weight_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_weight_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示包裹体积
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_vol]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_vol'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_vol]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_vol'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_vol_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_vol_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示用户备注
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_remark]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_remark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_remark]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_remark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                              
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否显示管理员包裹备注
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_adminremark]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_adminremark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_adminremark]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_adminremark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_adminremark_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_adminremark_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要拍照
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_photo]" value="1"
                                               data-am-ucheck  <?= $values['fahuocang']['is_photo'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="keeper[fahuocang][is_photo]" value="0"
                                               data-am-ucheck <?= $values['fahuocang']['is_photo'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="keeper[fahuocang][is_photo_force]" value="1" data-am-ucheck
                                            <?= $values['fahuocang']['is_photo_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
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
