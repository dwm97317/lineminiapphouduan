<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="tips am-margin-top-sm am-margin-bottom-sm">
                                <div class="pre">
                                    <p>
                                        模板消息仅用于微信小程序向用户发送服务通知，因微信限制，每笔支付订单可允许向用户在7天内推送最多3条模板消息。
                                        <a href="<?= url('store/setting.help/tplmsg') ?>" target="_blank">如何获取模板消息ID？</a>
                                    </p>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">是否为老模板消息（微信模板消息更改为了类目消息，2023年8月后注册的公众号请选择否）</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[is_oldtps]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_oldtps'] == '1' ? 'checked' : '' ?>
                                               required>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[is_oldtps]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_oldtps'] == '0' ? 'checked' : '' ?>>
                                        否
                                    </label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹入库通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payment][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['payment']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payment][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['payment']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[payment][template_id]"
                                           value="<?= $values['payment']['template_id'] ?>">
                                    <div class="help-block am-margin-top-xs">
                                        <small>模板编号OPENTM418500641，关键词 (到货仓库、包裹单号、入库时间)</small>
                                    </div>
                                </div>
                            </div>
        

                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">运单状态更新通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[delivery][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['delivery']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[delivery][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['delivery']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[delivery][template_id]"
                                           value="<?= $values['delivery']['template_id'] ?>">
                                    <small>模板编号OPENTM202199304，关键词 (运单号、时间、状态)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹打包申请通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[packageit][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['packageit']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[packageit][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['packageit']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[packageit][template_id]"
                                           value="<?= $values['packageit']['template_id'] ?>">
                                    <small>模板编号OPENTM418551488，关键词 (用户备注名、用户会员账号、用户打包申请时间、用户申请打包数量、包裹出库编号)</small>
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单支付成功通知</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paymessage][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['paymessage']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paymessage][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['paymessage']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[paymessage][template_id]"
                                           value="<?= $values['paymessage']['template_id'] ?>">
                                    <small>关键词 (用户备注名、用户会员账号、用户打包申请时间、用户申请打包数量、包裹出库编号)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹入库提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[inwarehouse][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['inwarehouse']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[inwarehouse][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['inwarehouse']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[inwarehouse][template_id]"
                                           value="<?= $values['inwarehouse']['template_id'] ?>">
                                    <small>模板编号45458，关键词 (入库仓库、快递单号、入库时间、入库重量、物品)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">包裹出库提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outwarehouse][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['outwarehouse']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outwarehouse][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['outwarehouse']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[outwarehouse][template_id]"
                                           value="<?= $values['outwarehouse']['template_id'] ?>">
                                    <small>模板编号47689，关键词 (包裹单号、重量、仓库、包裹状态、出库时间)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单支付成功通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paysuccess][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['paysuccess']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[paysuccess][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['paysuccess']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[paysuccess][template_id]"
                                           value="<?= $values['paysuccess']['template_id'] ?>">
                                    <small>模板编号47030，关键词 (订单号、支付金额、订单件数、订单重量)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">订单打包完成通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[dabaosuccess][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['dabaosuccess']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[dabaosuccess][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['dabaosuccess']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[dabaosuccess][template_id]"
                                           value="<?= $values['dabaosuccess']['template_id'] ?>">
                                    <small>模板编号50795，关键词 (订单号、仓库名称、新包裹重量、新包裹体积)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">出库申请提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outapply][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['outapply']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[outapply][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['outapply']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[outapply][template_id]"
                                           value="<?= $values['outapply']['template_id'] ?>">
                                    <small>模板编号42835，关键词 (单号、货主名称、数量、库房名称、时间)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">货物到仓通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[toshop][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['toshop']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[toshop][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['toshop']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[toshop][template_id]"
                                           value="<?= $values['toshop']['template_id'] ?>">
                                    <small>模板编号48064，关键词 (运单号、仓库、到仓时间)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">发货通知(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[sendpack][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['sendpack']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[sendpack][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['sendpack']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[sendpack][template_id]"
                                           value="<?= $values['sendpack']['template_id'] ?>">
                                    <small>模板编号44375，关键词 (订单号、运单号、发货量、承运商、发货时间)</small>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">付款单生成提醒(新类目模板)</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否启用
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payorder][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['payorder']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="tplMsg[payorder][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['payorder']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="tplMsg[payorder][template_id]"
                                           value="<?= $values['payorder']['template_id'] ?>">
                                    <small>模板编号45318，关键词 (订单号、客户代号、重量、金额、时间)</small>
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
