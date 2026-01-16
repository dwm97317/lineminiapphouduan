<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-8 am-u-lg-8">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">汇款账号</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.bank/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.bank/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>开户行</th>
                                <th>银行支行</th>
                                <th>银行卡号</th>
                                <th>银行行号</th>
                                <th>开户人</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                            <?php $taker_status = [1=>'正常',2=>'禁用']; ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['bank_name'] ?></td>
                                        <td class="am-text-middle"><?= $item['child_bank_name'] ?></td>
                                        <td class="am-text-middle"><?= $item['bank_card'] ?></td>
                                        <td class="am-text-middle"><?= $item['bank_no'] ?></td>
                                        <td class="am-text-middle"><?= $item['open_name'] ?></td>
                                        <td class="am-text-middle"><?= $taker_status[$item['status']]; ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('setting.bank/edit')): ?>
                                                    <a href="<?= url('setting.bank/edit',
                                                        ['id' => $item['id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.bank/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['id'] ?>"><i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="am-u-sm-12 am-u-md-4 am-u-lg-4">
        <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">汇款温馨提示</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">提示 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <!-- 加载编辑器的容器 -->
                                     <textarea id="container" name="bank_setting[setting]"
                                              type="text/plain"><?= $values['setting'] ?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-form-label form-require">
                                    是否需要填写订单号/流水号
                                </label>
                                <div class="am-u-sm-7">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_liushui]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_liushui'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_liushui]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_liushui'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-form-label form-require">
                                    是否需要填写充值金额
                                </label>
                                <div class="am-u-sm-7">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_jine]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_jine'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_jine]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_jine'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-form-label form-require">
                                    是否需要选择充值银行
                                </label>
                                <div class="am-u-sm-7">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_yinhang]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_yinhang'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_yinhang]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_yinhang'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-form-label form-require">
                                    是否需要选择充值币种
                                </label>
                                <div class="am-u-sm-7">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_bizhong]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_bizhong'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_bizhong]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_bizhong'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-form-label form-require">
                                    是否需要上传凭证图片
                                </label>
                                <div class="am-u-sm-7">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_pingzhengimage]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_pingzhengimage'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_pingzhengimage]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_pingzhengimage'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-form-label form-require">
                                    是否需要选择充值日期
                                </label>
                                <div class="am-u-sm-7">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_chongzhidate]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_chongzhidate'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="bank_setting[is_chongzhidate]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_chongzhidate'] == '0' ? 'checked' : '' ?>>
                                        关闭
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
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'article[image_id]'
        });

        // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 300
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
        
         // 删除元素
        var url = "<?= url('store/setting.bank/delete') ?>";
        $('.item-delete').delete('id', url);

    });
</script>

