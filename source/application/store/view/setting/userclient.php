<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">预报功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    单个快递单号预报
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_single]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_single'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_single]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_single'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    多个快递单号预报
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_more]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_more'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_more]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_more'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报国家
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_country]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_country'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_country]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_country'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_country_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_country_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报仓库
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_shop]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_shop'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_shop]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_shop'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_shop_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_shop_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报快递公司
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressname]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_expressname'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_expressname]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_expressname'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_expressname_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_expressname_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要预报类目
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_category]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_category'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_category]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_category'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_category_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_category_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品价值
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_price]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_price'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_price]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_price'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_price_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_price_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写备注
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_remark]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_remark'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_remark]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_remark'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_remark_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_remark_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_images]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_images'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_images]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_images'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_images_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_images_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要确认阅读协议
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_xieyi]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_xieyi'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_xieyi]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_xieyi'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_xieyi_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_xieyi_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写物品信息
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_goodslist]" value="1"
                                               data-am-ucheck  <?= $values['yubao']['is_goodslist'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[yubao][is_goodslist]" value="0"
                                               data-am-ucheck <?= $values['yubao']['is_goodslist'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[yubao][is_goodslist_force]" value="1" data-am-ucheck
                                            <?= $values['yubao']['is_goodslist_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户资料功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自定义身份证别名
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="userclient[userinfo][identification_card]"
                                           value="<?= $values['userinfo']['identification_card'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    自定义身份证照片别名
                                </label>
                                <div class="am-u-sm-9">
                                    <input autocomplete="off" type="text" class="tpl-form-input" name="userclient[userinfo][identification_card_image]"
                                           value="<?= $values['userinfo']['identification_card_image'] ?>" >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要上传身份证图片
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_identification_card]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_identification_card'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_identification_card]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_identification_card'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_identification_card_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_identification_card_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写生日
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_birthday]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_birthday'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_birthday]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_birthday'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_birthday_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_birthday_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写微信号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_wechat]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_wechat'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_wechat]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_wechat'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_wechat_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_wechat_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写邮箱
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_email]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_email'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_email]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_email'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_email_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_email_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否需要填写手机号
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_mobile]" value="1"
                                               data-am-ucheck  <?= $values['userinfo']['is_mobile'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[userinfo][is_mobile]" value="0"
                                               data-am-ucheck <?= $values['userinfo']['is_mobile'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <label class="am-checkbox-inline">
                                        <input type="checkbox" name="userclient[userinfo][is_mobile_force]" value="1" data-am-ucheck
                                            <?= $values['userinfo']['is_mobile_force']==1?'checked' : '' ?>>
                                        是否必填
                                    </label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户打包功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在提交打包前需要完善用户资料
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_force]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_force'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_force]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_force'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否在提交打包前可以填写代收款（代收货款）
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_waitreceivedmoney]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_waitreceivedmoney'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_waitreceivedmoney]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_waitreceivedmoney'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    开启自提点
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_packagestation]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_packagestation'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_packagestation]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_packagestation'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>选择不开启后，在用户端提交打包时则无法查看到自提点</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    开启送货上门
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_todoor]" value="1"
                                               data-am-ucheck  <?= $values['packit']['is_todoor'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[packit][is_todoor]" value="0"
                                               data-am-ucheck <?= $values['packit']['is_todoor'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>选择不开启后，在用户端提交打包时则无法选择用户地址</small>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户登录功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    在小程序或公众号中是否开启账号密码登录方式
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_passwordlogin]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_passwordlogin'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_passwordlogin]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_passwordlogin'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>必填项请在上方【用户资料功能设置】中设置</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    H5端登录是否展示手机号前缀
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_phone]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_phone'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_phone]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_phone'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>关闭后，H5用户端则不需要选择手机号前缀，如果开启国际短信，请选择开启</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否注册了微信开放平台
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_wxopen]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_wxopen'] == 1 ? 'checked' : '' ?>>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_wxopen]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_wxopen'] == 0 ? 'checked' : '' ?>>
                                        不开启
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>开启微信开放平台后可以对微信小程序用户发送模板消息，没有注册则小程序用户只能接收订阅消息<a target="_blank" href="https://open.weixin.qq.com/">微信开放平台注册地址</a></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    新用户注册时，是否合并用户数据，实现多端账户统一（必须先注册微信开放平台）
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_merge_user]" value="1"
                                               data-am-ucheck  <?= $values['loginsetting']['is_merge_user'] == 1 ? 'checked' : '' ?>>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[loginsetting][is_merge_user]" value="0"
                                               data-am-ucheck <?= $values['loginsetting']['is_merge_user'] == 0 ? 'checked' : '' ?>>
                                        否
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>可以实现多种客户端的账号统一，例如H5、微信小程序、APP。如果未开启，则不同端的用户无法合并<a target="_blank" href="https://open.weixin.qq.com/">微信开放平台注册地址</a></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户端下单流程功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第一步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][first_title]"
                                           value="<?= $values['newuserprocess']['first_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第一步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][first_remark]"
                                           value="<?= $values['newuserprocess']['first_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第二步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][second_title]"
                                           value="<?= $values['newuserprocess']['second_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第二步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][second_remark]"
                                           value="<?= $values['newuserprocess']['second_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第三步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][third_title]"
                                           value="<?= $values['newuserprocess']['third_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第三步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][third_remark]"
                                           value="<?= $values['newuserprocess']['third_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第四步的标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][fourth_title]"
                                           value="<?= $values['newuserprocess']['fourth_title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 第四步的说明文字 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[newuserprocess][fourth_remark]"
                                           value="<?= $values['newuserprocess']['fourth_remark'] ?>" required>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">用户端首页引导区功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否使用原始默认
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][is_default]" value="1"
                                               data-am-ucheck  <?= $values['guide']['is_default'] == 1 ? 'checked' : '' ?>>
                                        默认
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][is_default]" value="0"
                                               data-am-ucheck <?= $values['guide']['is_default'] == 0 ? 'checked' : '' ?>>
                                        自定义
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>使用默认则下方设置不会生效，使用自定义，则请上传完整的图片和跳转路径，否则可能无法跳转</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">第一张图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file1 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['guide']['first_image'])?$values['guide']['first_file_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['guide']['first_file_path'])?$values['guide']['first_file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[guide][first_image]" value="<?=$values['guide']['first_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <div class="help-block am-u-sm-12">
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第一张图跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][first_url_type]" value="1"
                                               data-am-ucheck  <?= $values['guide']['first_url_type'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][first_url_type]" value="2"
                                               data-am-ucheck <?= $values['guide']['first_url_type'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第一张图跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[guide][first_url]"
                                           value="<?= $values['guide']['first_url'] ?>">
                                           <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">第二张图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file2 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['guide']['second_image'])?$values['guide']['second_file_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['guide']['second_file_path'])?$values['guide']['second_file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[guide][second_image]" value="<?=$values['guide']['second_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第二张图跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][second_url_type]" value="1"
                                               data-am-ucheck  <?= $values['guide']['second_url_type'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][second_url_type]" value="2"
                                               data-am-ucheck <?= $values['guide']['second_url_type'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第二张图跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[guide][second_url]"
                                           value="<?= $values['guide']['second_url'] ?>">
                                    <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label">第三张图 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file3 am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                                <div class="file-item">
                                                    <a href="<?= isset($values['guide']['third_image'])?$values['guide']['third_file_path']:'' ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= isset($values['guide']['third_file_path'])?$values['guide']['third_file_path']:'' ?>">
                                                    </a>
                                                    <input type="hidden" name="userclient[guide][third_image]" value="<?=$values['guide']['third_image'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                        <small>图片比例宽高比3:5,像素160*250</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    第三张图跳转地址类型
                                </label>
                                  <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][third_url_type]" value="1"
                                               data-am-ucheck  <?= $values['guide']['third_url_type'] == 1 ? 'checked' : '' ?>>
                                        站内
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="userclient[guide][third_url_type]" value="2"
                                               data-am-ucheck <?= $values['guide']['third_url_type'] == 2 ? 'checked' : '' ?>>
                                        站外
                                    </label>
                                    <div class="help-block am-u-sm-12">
                                        <small>站内请使用下方的链接库，站外链接请先在微信小程序官网后台添加域名授权</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 第三张图跳转地址 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="userclient[guide][third_url]"
                                           value="<?= $values['guide']['third_url'] ?>">
                                    <small>注意：小程序内部链接使用链接库中的<a target="_blank" href="index.php?s=/store/wxapp.page/links">点击打开链接库</a></small>
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
<script src="/web/static/js/selectize.min.js"></script>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script>
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
         // 选择图片
        $('.upload-file1').selectImages({
            name: 'userclient[guide][first_image]'
        });
        $('.upload-file2').selectImages({
            name: 'userclient[guide][second_image]'
        });
        $('.upload-file3').selectImages({
            name: 'userclient[guide][third_image]'
        });
    });
</script>
