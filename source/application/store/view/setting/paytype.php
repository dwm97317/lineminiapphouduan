<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">支付设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    微信支付
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[wechat][is_open]" value="1"
                                               data-am-ucheck  <?= $values['wechat']['is_open'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[wechat][is_open]" value="0"
                                               data-am-ucheck <?= $values['wechat']['is_open'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[wechat][platfrom][MP-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['wechat']['platfrom']['MP-WEIXIN']==1?'checked' : '' ?>>
                                        小程序
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[wechat][platfrom][H5-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['wechat']['platfrom']['H5-WEIXIN']==1?'checked' : '' ?>>
                                        公众号
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[wechat][platfrom][H5]" value="1" data-am-ucheck
                                            <?= $values['wechat']['platfrom']['H5']==1?'checked' : '' ?>>
                                        H5
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[wechat][platfrom][APP]" value="1" data-am-ucheck
                                            <?= $values['wechat']['platfrom']['APP']==1?'checked' : '' ?>>
                                        APP
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[wechat][platfrom][WEB]" value="1" data-am-ucheck
                                            <?= $values['wechat']['platfrom']['WEB']==1?'checked' : '' ?>>
                                        WEB
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                               <label class="am-u-sm-3 am-form-label"></label>
                                    <small>注：微信支付是指用户可以使用微信支付直接支付集运单的运费；如未申请微信支付，可<a href="https://pay.weixin.qq.com/" target="_blank">去申请</a></small>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    余额支付
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[balance][is_open]" value="1"
                                               data-am-ucheck  <?= $values['balance']['is_open'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[balance][is_open]" value="0"
                                               data-am-ucheck <?= $values['balance']['is_open'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[balance][platfrom][MP-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['balance']['platfrom']['MP-WEIXIN']==1?'checked' : '' ?>>
                                        小程序
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[balance][platfrom][H5-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['balance']['platfrom']['H5-WEIXIN']==1?'checked' : '' ?>>
                                        公众号
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[balance][platfrom][H5]" value="1" data-am-ucheck
                                            <?= $values['balance']['platfrom']['H5']==1?'checked' : '' ?>>
                                        H5
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[balance][platfrom][APP]" value="1" data-am-ucheck
                                            <?= $values['balance']['platfrom']['APP']==1?'checked' : '' ?>>
                                        APP
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[balance][platfrom][WEB]" value="1" data-am-ucheck
                                            <?= $values['balance']['platfrom']['WEB']==1?'checked' : '' ?>>
                                        WEB
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                               <label class="am-u-sm-3 am-form-label"></label>
                                    <small>注：余额支付是指用户可以使用余额支付集运单的运费；</small>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    汉特支付(Hantepay)
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[Hantepay][is_open]" value="1"
                                               data-am-ucheck  <?= $values['Hantepay']['is_open'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[Hantepay][is_open]" value="0"
                                               data-am-ucheck <?= $values['Hantepay']['is_open'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[Hantepay][platfrom][MP-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['Hantepay']['platfrom']['MP-WEIXIN']==1?'checked' : '' ?>>
                                        小程序
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[Hantepay][platfrom][H5-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['Hantepay']['platfrom']['H5-WEIXIN']==1?'checked' : '' ?>>
                                        公众号
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[Hantepay][platfrom][H5]" value="1" data-am-ucheck
                                            <?= $values['Hantepay']['platfrom']['H5']==1?'checked' : '' ?>>
                                        H5
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[Hantepay][platfrom][APP]" value="1" data-am-ucheck
                                            <?= $values['Hantepay']['platfrom']['APP']==1?'checked' : '' ?>>
                                        APP
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[Hantepay][platfrom][WEB]" value="1" data-am-ucheck
                                            <?= $values['Hantepay']['platfrom']['WEB']==1?'checked' : '' ?>>
                                        WEB
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 商户号 </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="paytype[Hantepay][merchant_no]"
                                           value="<?= $values['Hantepay']['merchant_no'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 门店编号 </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="paytype[Hantepay][store_no]"
                                           value="<?= $values['Hantepay']['store_no'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> APIKEY </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="password" class="tpl-form-input" name="paytype[Hantepay][apikey]"
                                           value="<?= $values['Hantepay']['apikey'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                               <label class="am-u-sm-3 am-form-label"></label>
                                    <small>注：Hantepay仅美国公司可申请，如你满足条件；可点击<a href="https://docs.hantepay.cn/" target="_blank">去申请</a></small>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    OMIPAY（支持澳洲）
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[omipay][is_open]" value="1"
                                               data-am-ucheck  <?= $values['omipay']['is_open'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="paytype[omipay][is_open]" value="0"
                                               data-am-ucheck <?= $values['omipay']['is_open'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[omipay][platfrom][MP-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['omipay']['platfrom']['MP-WEIXIN']==1?'checked' : '' ?>>
                                        小程序
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[omipay][platfrom][H5-WEIXIN]" value="1" data-am-ucheck
                                            <?= $values['omipay']['platfrom']['H5-WEIXIN']==1?'checked' : '' ?>>
                                        公众号
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[omipay][platfrom][H5]" value="1" data-am-ucheck
                                            <?= $values['omipay']['platfrom']['H5']==1?'checked' : '' ?>>
                                        H5
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[omipay][platfrom][APP]" value="1" data-am-ucheck
                                            <?= $values['omipay']['platfrom']['APP']==1?'checked' : '' ?>>
                                        APP
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="paytype[omipay][platfrom][WEB]" value="1" data-am-ucheck
                                            <?= $values['omipay']['platfrom']['WEB']==1?'checked' : '' ?>>
                                        WEB
                                    </label>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> MID </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="paytype[omipay][mid]"
                                           value="<?= $values['omipay']['mid'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> APIKEY </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="password" class="tpl-form-input" name="paytype[omipay][apikey]"
                                           value="<?= $values['omipay']['apikey'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 选择支付货币 </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="paytype[omipay][currency]" value="AUD"
                                           data-am-ucheck  <?= $values['omipay']['currency'] == 'AUD' ? 'checked' : '' ?>>
                                    AUD（澳元）
                                </label>
                                <label class="am-radio-inline">
                                    <input type="radio" name="paytype[omipay][currency]" value="CNY"
                                           data-am-ucheck <?= $values['omipay']['currency'] == 'CNY' ? 'checked' : '' ?>>
                                    CNY（人民币）
                                </label>
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
