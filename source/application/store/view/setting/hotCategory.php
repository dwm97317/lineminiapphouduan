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
                                <div class="widget-title am-fl">系统设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 系统名称 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[name]"
                                           value="<?= $values['name'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 分享标题 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[title]"
                                           value="<?= $values['title'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 分享描述 </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[desc]"
                                           value="<?= $values['desc'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group" style='display:none'>
                                <label class="am-u-sm-3 am-form-label form-require"> 配送方式 </label>
                                <div class="am-u-sm-9">
                                    <?php foreach (DeliveryTypeEnum::data() as $item): ?>
                                        <label class="am-checkbox-inline">
                                            <input type="checkbox" name="store[delivery_type][]"
                                                   value="<?= $item['value'] ?>" data-am-ucheck
                                                <?= in_array($item['value'], $values['delivery_type']) ? 'checked' : '' ?>>
                                            <?= $item['name'] ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <div class="help-block">
                                        <small>注：配送方式至少选择一个</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启电话客服
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_phone]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_phone'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_phone]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_phone'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 客户服务名称 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="store[service_name]"
                                           value="<?= $values['service_name']??'' ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 客户服务电话 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="store[service_phone]"
                                           value="<?= $values['service_phone']??'' ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启微信客服
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_wechat]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_wechat'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_wechat]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_wechat'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 微信客服名称 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="store[wechat_name]"
                                           value="<?= $values['wechat_name']??'' ?>" required>
                                            <div class="help-block">
                                        <small>注：默认开启客服功能，修改名称，用户端对应的文字也将修改；</small>
                                </div>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启微信号展示
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_wechathao]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_wechathao'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_wechathao]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_wechathao'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 微信客服账号 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="store[wechathao]"
                                           value="<?= $values['wechathao']??'' ?>" required>
                                            <div class="help-block">
                                        <small>注：默认开启客服微信号，修改微信号，用户端对应的文字也将修改；</small>
                                </div>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启客服邮箱展示
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_kefuemail]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_kefuemail'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_kefuemail]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_kefuemail'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 邮箱客服名称 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="store[emailname]"
                                           value="<?= $values['emailname']??'' ?>" required>
                                            <div class="help-block">
                                        <small>注：默认开启客服邮箱，修改邮箱后，用户端对应的文字也将修改；</small>
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require"> 客服邮箱账号 </label>
                                <div class="am-u-sm-9">
                                     <input type="text" class="tpl-form-input" name="store[kefuemail]"
                                           value="<?= $values['kefuemail']??'' ?>" required>
                                            <div class="help-block">
                                </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否强制用户授权手机号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_getphone]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_getphone'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_getphone]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_getphone'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                     
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否强制用户修改昵称
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_setnickname]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_setnickname'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_setnickname]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_setnickname'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>注：用户初次登录默认昵称为【微信用户】，设置强制则用户必须修改；</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启引导公众号关注
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_wechatgzh]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_wechatgzh'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_wechatgzh]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_wechatgzh'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 自定义功能设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    首页弹窗设置
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[jumpbox][mode]" value="10"
                                               data-am-ucheck
                                            <?= $values['jumpbox']['mode'] == '10' ? 'checked' : '' ?>
                                               required>
                                        每次都弹
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[jumpbox][mode]" value="20"
                                               data-am-ucheck
                                            <?= $values['jumpbox']['mode']== '20' ? 'checked' : '' ?>>
                                        只弹一次
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[jumpbox][mode]" value="30"
                                               data-am-ucheck
                                            <?= $values['jumpbox']['mode'] == '30' ? 'checked' : '' ?>>
                                        一次不弹
                                    </label>
                                    <div class="help-block">
                                        <small>注意：开启每次都弹后只会展示第一个公告<a target="_blank" href="index.php?s=/store/setting.banner/index">点击跳转到公告设置</a></small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    底部菜单模式
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[menu_type]" value="10"
                                               data-am-ucheck
                                            <?= $values['menu_type'] == '10' ? 'checked' : '' ?>>
                                        A模式:首页/查询/快捷键/拼团/我的
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[menu_type]" value="20"
                                               data-am-ucheck
                                            <?= $values['menu_type']== '20' ? 'checked' : '' ?>>
                                        B模式:首页/查询/快捷键/运费/我的
                                    </label>
                                    <br>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[menu_type]" value="30"
                                               data-am-ucheck
                                            <?= $values['menu_type'] == '30' ? 'checked' : '' ?>>
                                        C模式:首页/查询/运费/我的
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[menu_type]" value="40"
                                               data-am-ucheck
                                            <?= $values['menu_type'] == '40' ? 'checked' : '' ?>>
                                        D模式:首页/查询/运费/拼团/我的
                                    </label>
                                    <div class="help-block">
                                        <small>注意：默认开启A模式，如需其他模式亲自行设置
                                              <a href="<?= url('store/setting.help/menuSet') ?>" target="_blank">点击查看效果图？</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否展示用户编号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[usercode_mode][is_show]" value="1" onclick="switchLineMode(this)"
                                               data-am-ucheck
                                            <?= $values['usercode_mode']['is_show'] == '1' ? 'checked' : '' ?>
                                               required>
                                        只显示编号CODE
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[usercode_mode][is_show]" value="0" onclick="switchLineMode(this)"
                                               data-am-ucheck
                                            <?= $values['usercode_mode']['is_show'] == '0' ? 'checked' : '' ?>>
                                        只显示ID
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[usercode_mode][is_show]" value="2" onclick="switchLineMode(this)"
                                               data-am-ucheck
                                            <?= $values['usercode_mode']['is_show'] == '2' ? 'checked' : '' ?>>
                                        都显示
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group usercoded" style="<?= $values['usercode_mode']['is_show'] == 0 ? 'display:none;' : 'display:block' ?>"> 
                                <label class="am-u-sm-3  am-form-label form-require"> 用户编号模式设置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="store[usercode_mode][mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['usercode_mode']['mode'] == 10 ? 'selected' : '' ?> >纯数字模式</option>
                                            <option value="20" <?= $values['usercode_mode']['mode'] == 20 ? 'selected' : '' ?>>纯英文模式</option>
                                            <option value="30" <?= $values['usercode_mode']['mode'] == 30 ? 'selected' : '' ?> >数字英文混合模式</option>
                                    </select>
                                    <div class="help-block">
                                        <small>目前支持纯数字模式，纯英文模式，数字英文混合模式</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 用户编号配置：纯数字模式 -->
                         
                            <div id="<?= UserCodeTypeEnum::SHUZI ?>" class="form-tab-group  <?= $values['usercode_mode']['mode'] == 10 ? 'active' : '' ?> <?= ($values['usercode_mode']['mode'] == 10 && $values['usercode_mode']['is_show'] == '0') ? 'disnone' : '' ?> " name="store[usercode_mode][mode]">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label form-require">数字个数，最佳是5位</label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input class="tpl-form-input" type="number"
                                               name="store[usercode_mode][<?= UserCodeTypeEnum::SHUZI ?>][number]" value="<?= $values['usercode_mode']['10']['number'] ??'' ?>">
                                        <small>填5位数生成的范围为：00001-99999</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 用户编号配置：纯英文模式 -->
                            <div id="<?= UserCodeTypeEnum::ZIMU ?>" class="form-tab-group  <?= $values['usercode_mode']['mode'] == 20 ? 'active' : '' ?> <?= ($values['usercode_mode']['mode'] == 20 && $values['usercode_mode']['is_show'] == '0') ? 'disnone' : '' ?>" name="store[usercode_mode][mode]">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3  am-form-label form-require"> 英文个数，最佳是5位数 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input"
                                               name="store[usercode_mode][<?= UserCodeTypeEnum::ZIMU ?>][char]" value="<?= $values['usercode_mode']['20']['char'] ??'' ?>">
                                                <small>填5位数随机生成的编号为：BHRTD，JGFDSA，OPRADS...</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 用户编号配置：数字英文混合模式 -->
                            <div id="<?= UserCodeTypeEnum::SHUMU ?>" name="store[usercode_mode][mode] " class="form-tab-group  <?= $values['usercode_mode']['mode'] == 30 ? 'active' : '' ?> 
                            <?= ($values['usercode_mode']['mode'] == 30 && $values['usercode_mode']['is_show'] == '0') ? 'disnone' : '' ?>">
                                <div class="am-form-group">
                                    <label class="am-u-sm-3  am-form-label form-require"> 头部英文，最佳为2-3个固定子母 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                         <input type="text" class="tpl-form-input"
                                               name="store[usercode_mode][<?= UserCodeTypeEnum::SHUMU ?>][char]" value="<?= $values['usercode_mode']['30']['char'] ??'' ?>">
                                    </div>
                                    <label class="am-u-sm-3  am-form-label form-require"> 数字个数，最佳是5位 </label>
                                    <div class="am-u-sm-9 am-u-end">
                                        <input type="text" class="tpl-form-input"  value="<?= $values['usercode_mode']['30']['number'] ??'' ?>"
                                               name="store[usercode_mode][<?= UserCodeTypeEnum::SHUMU ?>][number]">
                                                <small>子母填【JY】，数字填5生成的范围为：JY00001-JY99999</small>
                                    </div>
                                </div>
                            </div>
                
                            
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require"> 运费查询排序方式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="store[sort_mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['sort_mode'] == 10 ? 'selected' : '' ?>>按价格排序</option>
                                            <option value="20" <?= $values['sort_mode'] == 20 ? 'selected' : '' ?>>按路线sort排序</option>
                                            <option value="30" <?= $values['sort_mode'] == 30 ? 'selected' : '' ?>>按路线ID自然排序</option>
                                    </select>
                                    <div class="help-block">
                                        <small>目前支持纯数字模式，纯英文模式，数字英文混合模式</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    路线查询排序方式
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_sort]" value="10"
                                               data-am-ucheck
                                            <?= $values['is_sort'] == '10' ? 'checked' : '' ?>
                                               required>
                                        从大到小
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_sort]" value="20"
                                               data-am-ucheck
                                            <?= $values['is_sort'] == '20' ? 'checked' : '' ?>>
                                        从小到大
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启路线折扣
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_discount]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_discount'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_discount]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_discount'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>路线折扣开启后，所有的用户运费查询，运费计算的运费将在标准价格基础上乘以折扣比例</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require"> 重量单位设置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="store[weight_mode][mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['weight_mode']['mode'] == 10 ? 'selected' : '' ?>>克/g</option>
                                            <option value="20" <?= $values['weight_mode']['mode'] == 20 ? 'selected' : '' ?>>千克/kg</option>
                                            <option value="30" <?= $values['weight_mode']['mode'] == 30 ? 'selected' : '' ?> >磅/lb</option>
                                    </select>
                                    <div class="help-block">
                                        <small>目前仅支持克、千克、磅等单位的切换,默认全局重量单位</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require"> 计费单位设置 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="store[price_mode][mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['price_mode']['mode'] == 10 ? 'selected' : '' ?>>元/¥</option>
                                            <option value="20" <?= $values['price_mode']['mode'] == 20 ? 'selected' : '' ?>>美元/$</option>
                                            <option value="30" <?= $values['price_mode']['mode'] == 30 ? 'selected' : '' ?> >加币/C$</option>
                                            <option value="40" <?= $values['price_mode']['mode'] == 40 ? 'selected' : '' ?> >欧元/€</option>
                                    </select>
                                    <div class="help-block">
                                        <small>目前支持纯数字模式，纯英文模式，数字英文混合模式</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启自提点
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_packagestation]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_packagestation'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_packagestation]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_packagestation'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>开启自提点后，请到【设置】->【自提点管理】中添加自提点</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3  am-form-label form-require"> 用户复制仓库地址收件人显示模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select name="store[link_mode]"
                                            data-am-selected="{btnSize: 'sm', placeholder: '请选择', maxHeight: 400}">
                                            <option value="10" <?= $values['link_mode']== 10 ? 'selected' : '' ?>>仓库名+UID</option>
                                            <option value="20" <?= $values['link_mode'] == 20 ? 'selected' : '' ?>>仓库联系人+UID</option>
                                            <option value="30" <?= $values['link_mode'] == 30 ? 'selected' : '' ?>>用户昵称+UID</option>
                                    </select>
                                    <div class="help-block">
                                        <small>目前支持纯数字模式，纯英文模式，数字英文混合模式</small>
                                    </div>
                                </div>
                            </div>
                           
                            
                            
                            
                            
                            
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 功能开启隐藏设置</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    运费查询是否关联物品类目
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_categorysearch]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_categorysearch'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_categorysearch]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_categorysearch'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    首页是否展示导航
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_navigation]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_navigation'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_navigation]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_navigation'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    首页是否展示广告图
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_yaoqing]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_yaoqing'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_yaoqing]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_yaoqing'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    首页是否展示评论
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_pinglun]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_pinglun'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_pinglun]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_pinglun'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    首页是否展示最佳路线
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_line]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_line'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_line]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_line'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启邀请好友广告图
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_fyaoqing]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_fyaoqing'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_fyaoqing]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_fyaoqing'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启余额
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_balance]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_balance'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_balance]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_balance'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启站内信
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_sitesms]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_sitesms'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_sitesms]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_sitesms'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启优惠券
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_cuppon]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_cuppon'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_cuppon]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_cuppon'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启积分
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_point]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_point'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_point]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_point'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启集运订单
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_jiyun]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_jiyun'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_jiyun]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_jiyun'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启拼团功能
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_pintuan]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_pintuan'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_pintuan]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_pintuan'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    个人中心是否开启分销入口
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_fenxiao]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_fenxiao'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_fenxiao]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_fenxiao'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启下单拍照功能
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_camera]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_camera'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_camera]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_camera'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>此功能默认关闭，当你的操作电脑上了解了摄像头时可以启用，用于入库拍照使用，否则请关闭；</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否展示待认领包裹图片
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_ren_image]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_ren_image'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_ren_image]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_ren_image'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>如果你使用了电子秤自动拍照上传，请勿开启。</small>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    仓管员入库是否开启热门商品类目
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_hotcategory]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_hotcategory'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_hotcategory]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_hotcategory'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block">
                                         <small>开启后仓管员入库时，会多出一个快速选择热门商品类目的区域
                                              <a href="<?= url('store/setting.help/hotCategory') ?>" target="_blank">点击查看效果图？</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 个人中心功能开关</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启申请团长
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_tuanzhang]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_tuanzhang'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_tuanzhang]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_tuanzhang'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启绑定邮箱
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_email]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_email'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_email]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_email'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启仓库地址
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_warehouse]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_warehouse'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_warehouse]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_warehouse'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启优惠券中心
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_daifu]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_daifu'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_daifu]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_daifu'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启申请查验
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_chayan]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_chayan'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_chayan]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_chayan'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                             <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启关于我们
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_about]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_about'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_about]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_about'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启包裹认领
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_renling]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_renling'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_renling]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_renling'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启新手问题
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_problem]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_problem'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_problem]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_problem'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启收货地址
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_address]" value="1"
                                               data-am-ucheck
                                            <?= $values['is_address'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[is_address]" value="0"
                                               data-am-ucheck
                                            <?= $values['is_address'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 用户地址动态设置开关</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启电话前缀
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_tel_code]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_tel_code'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_tel_code]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_tel_code'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启个人通关代码
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_clearancecode]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_clearancecode'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_clearancecode]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_clearancecode'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启身份证号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_identitycard]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_identitycard'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_identitycard]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_identitycard'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启省/州
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_province]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_province'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_province]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_province'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启城市
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_city]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_city'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_city]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_city'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启街道
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_street]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_street'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_street]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_street'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启门牌号
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_door]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_door'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_door]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_door'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启详细地址
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_detail]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_detail'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_detail]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_detail'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮箱
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_email]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_email'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_email]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_email'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label form-require">
                                    是否开启邮编
                                </label>
                                <div class="am-u-sm-9">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_code]" value="1"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_code'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="store[address_setting][is_code]" value="0"
                                               data-am-ucheck
                                            <?= $values['address_setting']['is_code'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"> 物流查询API</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 快递100 Customer </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[kuaidi100][customer]"
                                           value="<?= $values['kuaidi100']['customer'] ?>">
                                    <small>用于查询物流信息，<a href="https://www.kuaidi100.com/openapi/"
                                                       target="_blank">快递100申请</a></small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 快递100 Key </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[kuaidi100][key]"
                                           value="<?= $values['kuaidi100']['key'] ?>">
                                </div>
                            </div>
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-form-label"> 17TRACK Key </label>
                                <div class="am-u-sm-9">
                                    <input type="text" class="tpl-form-input" name="store[track17][key]"
                                           value="<?= $values['track17']['key']??'' ?>">
                                    <small>用于查询国际物流信息，<a href="https://user.17track.net/zh-cn/register?gb=api.17track.net#maybe=16"
                                                       target="_blank">17TRACK密钥申请</a></small>       
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

    function switchLineMode(_this){
        var _mode = _this.value;
        console.log($('.active'));
        console.log($('.active'));
        
        if(_mode==0){
          $('.usercoded').css('display','none');
          $('.usercodes').css('display','none');
          $('.active').addClass('goog');
          $('.active').removeClass('active');
        }
        if(_mode==1 || _mode==2){
          $('.active').removeClass('disnone');
          $('.usercoded').css('display','block');
          $('.goog').addClass('active');
          $('.goog').removeClass('disnone');
        }
    }
    $(function () {
        // 切换打印机类型
        $("select[name='store[usercode_mode][mode]']").on('change', function (e) {
            $('.form-tab-group').removeClass('active');
            $('.form-tab-group').removeClass('goog');
            $('#' + e.currentTarget.value).addClass('active');
        });
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
