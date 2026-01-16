<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">各状态物流模板设置</div>
                                <small style="padding-left:10px;color:#1686ef">(提示：当用户搜索物流单号，查找到的各个状态的物流信息)</small>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询入库日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[enter][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['enter']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[enter][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['enter']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">入库物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[enter][describe]"
                                           value="<?= $values['enter']['describe'] ?>">
                                    
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询提交打包日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[packageit][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['packageit']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[packageit][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['packageit']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">提交打包物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[packageit][describe]"
                                           value="<?= $values['packageit']['describe'] ?>">
                                    
                                </div>
                            </div>
    
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询查验完成日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[check][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['check']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[check][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['check']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">查验完成物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[check][describe]"
                                           value="<?= $values['check']['describe'] ?>">
                                    
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询支付完成日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[ispay][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['ispay']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[ispay][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['ispay']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">支付完成物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[ispay][describe]"
                                           value="<?= $values['ispay']['describe'] ?>">
                                    
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询发货完成日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[dosend][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['dosend']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[dosend][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['dosend']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">发货完成物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[dosend][describe]"
                                           value="<?= $values['dosend']['describe'] ?>">
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3">
                                    <small>需要显示国际单号就填写：{code}，否则可以不填！</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询已到货日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[reach][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['reach']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[reach][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['reach']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">已到货物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[reach][describe]"
                                           value="<?= $values['reach']['describe'] ?>">
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询已收货日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[take][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['take']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[take][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['take']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">已收货物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[take][describe]"
                                           value="<?= $values['take']['describe'] ?>">
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否支持查询问题件日志记录
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[problem][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['problem']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[problem][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['problem']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                     <span class="tpl-form-line-small-title">问题件物流信息</span>
                                </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input"
                                           name="notice[problem][describe]"
                                           value="<?= $values['problem']['describe'] ?>">
                                    
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否向17track注册用户预报的单号；
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_track_yubao][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_track_yubao']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_track_yubao][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_track_yubao']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否向17track注册订单发货的单号；
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_track_fahuo][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_track_fahuo']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_track_fahuo][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_track_fahuo']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否向17track注册订单转单的单号；
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_track_zhuandan][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_track_zhuandan']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_track_zhuandan][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_track_zhuandan']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    查询国际单号时，是否展示订单中的快递物流信息；
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_package][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_package']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_package][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_package']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                       
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    查询快递单号时，是否展示集运订单中的物流信息；
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_inpack][is_enable]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_inpack']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="notice[is_inpack][is_enable]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_inpack']['is_enable'] == '0' ? 'checked' : '' ?>>
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
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
   

    });
</script>
