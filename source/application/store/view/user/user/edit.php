<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑用户</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户ID </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <span class="am-form-static"><?= $detail['user_id'] ?></span>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户昵称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[nickName]" 
                                           value="<?= $detail['nickName'] ?>">
                                </div>
                            </div>
                            <?php if($set['is_show']!=0) :?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户编号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[user_code]"
                                           value="<?= $detail['user_code'] ?>">
                                </div>
                            </div>
                            <?php endif ;?>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">用户性别 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[gender]" value="1" data-am-ucheck <?= $detail['gender']['value']==1?'checked':'' ?>>
                                        男
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[gender]" value="2" data-am-ucheck <?= $detail['gender']['value']==2?'checked':'' ?>>
                                        女
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="user[gender]" value="0" data-am-ucheck <?= $detail['gender']['value']==0?'checked':'' ?>>
                                        未知
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户手机号 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="user[mobile]"
                                           value="<?= $detail['mobile'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label">用户邮箱 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" autocomplete="off" name="user[email]"
                                           value="<?= $detail['email'] ?>">
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-5 am-u-lg-2 am-form-label">所属客服 </label>
                                <div class="am-u-sm-9  am-u-end">
                                    <select name="user[service_id]"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}" >
                                        <option value=""></option>
                                        <?php if (isset($service) && !$service->isEmpty()):
                                            foreach ($service as $item): ?>
                                                <?php if(isset($detail['service_id'])): ?>
                                                   <option value="<?= $item['clerk_id'] ?>" <?= $detail['service_id'] == $item['clerk_id'] ? 'selected' : '' ?> ><?= $item['real_name'].'-'.$item['mobile'] ?></option>
                                                <?php else: ?>  
                                                   <option value="<?= $item['clerk_id'] ?>"><?= $item['real_name'].'-'.$item['mobile'] ?></option>
                                                <?php endif; ?>
                                                
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>用户绑定归属客服后，客服人员可以进行客勤跟踪管理</small>
                                    </div>
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
