 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">编辑收件人地址</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <form role="form" id="ajaxForm">
                    
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">收件人姓名</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="name" placeholder="收件人姓名" required="true" value="<?= $model['name'] ?>">
                        </div>
                        <?php if($setting['address_setting']['is_identitycard']) : ?>
                        <label class="col-sm-1 col-form-label control-label">身份证号</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="identitycard" placeholder="身份证号" required="" value="<?= $model['identitycard'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_clearancecode']) : ?>
                        <label class="col-sm-1 col-form-label control-label">通关代码</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="clearancecode" placeholder="通关代码" required="" value="<?= $model['clearancecode'] ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">手机号</label>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="tel_code" placeholder="前缀/区号" required="" value="<?= $model['tel_code'] ?>">
                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="phone" placeholder="收件人手机号" required="" value="<?= $model['phone'] ?>">
                        </div>
                        <div class="col-sm-2">
                            <input type="hidden" class="form-control" name="addressty"  required="" value="<?= $model['addressty'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">寄送国家 *</label>
                        <div class="col-sm-2">
                                <select class="form-control" name="country_id">
                                <?php if (isset($countryList) && !$countryList->isEmpty()):
                                foreach ($countryList as $countryitem): ?>
                                         <option value="<?= $countryitem['id'] ?>"><?= $countryitem['title'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <?php if($setting['address_setting']['is_province']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="province" placeholder="省/州" required="" value="<?= $model['province'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_city']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="city" placeholder="城市" value="<?= $model['city'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_region']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="region" placeholder="区" value="<?= $model['region'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_street']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="street" placeholder="街道" value="<?= $model['street'] ?>">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">详细地址</label>
                        <?php if($setting['address_setting']['is_detail']) : ?>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="detail" placeholder="详细地址" value="<?= $model['detail'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_door']) : ?>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="door" placeholder="门牌" value="<?= $model['door'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_code']) : ?>
                         <div class="col-sm-1">
                            <input type="text" class="form-control" name="code" placeholder="邮编" value="<?= $model['code'] ?>">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_email']) : ?>
                         <div class="col-sm-2">
                            <input type="text" class="form-control" name="email" placeholder="邮箱" value="<?= $model['email'] ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-gradient-success address" value="<?= $model['address_id'] ?>">提交保存</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.onload = function(){
 $(".address").click(res=>{
     var address_id =$(".address")[0].value ;
     var formData = $('#ajaxForm').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     })
    
     console.log(formJson,567);
   
     if(formJson['address[name]']==''){
         layer.alert('请输入收件人姓名');
         return false;
     }
     
    if(formJson['address[phone]']==''){
         layer.alert('请输入手机号');
         return false;
     }
     var url = "<?php echo(urlCreate('/web/address/edit')) ?>";
     $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:{address_id:address_id,formJson},
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                var urld = "<?php echo(urlCreate('/web/user/address')) ?>";
                window.location.href= urld;
            }

        }
     })
    return false;
 })
}
</script>						
						