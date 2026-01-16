 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">新增收件人地址</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-12">
                <form role="form" id="ajaxForm">
                    
                    <div class="form-group row">
                        
                        <label class="col-sm-1 col-form-label control-label">收件人姓名</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[name]" placeholder="收件人姓名" required="true">
                        </div>
                        
                        <?php if($setting['address_setting']['is_identitycard']) : ?>
                        <label class="col-sm-1 col-form-label control-label">身份证号</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="address[identitycard]" placeholder="身份证号" required="">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_clearancecode']) : ?>
                        <label class="col-sm-1 col-form-label control-label">通关代码</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control" name="address[clearancecode]" placeholder="通关代码" required="">
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">手机号</label>
                        <?php if($setting['address_setting']['is_tel_code']) : ?>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="address[tel_code]" placeholder="前缀/区号" required="">
                        </div>
                        <?php endif; ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[phone]" placeholder="收件人手机号" required="">
                        </div>
                        <div class="col-sm-2">
                            <input type="hidden" class="form-control" name="address[addressty]" value="0" placeholder="收件人手机号" required="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">寄送国家 *</label>
                        <div class="col-sm-2">
                                <select class="form-control" name="address[country_id]">
                                <?php if (isset($countryList) && !$countryList->isEmpty()):
                                foreach ($countryList as $countryitem): ?>
                                         <option value="<?= $countryitem['id'] ?>"><?= $countryitem['title'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <?php if($setting['address_setting']['is_province']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[province]" placeholder="省/州" required="">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_city']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[city]" placeholder="城市">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_region']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[region]" placeholder="区">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_street']) : ?>
                        <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[street]" placeholder="街道">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-1 col-form-label control-label">详细地址</label>
                        <?php if($setting['address_setting']['is_detail']) : ?>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="address[detail]" placeholder="详细地址">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_door']) : ?>
                        <div class="col-sm-1">
                            <input type="text" class="form-control" name="address[door]" placeholder="门牌">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_code']) : ?>
                         <div class="col-sm-1">
                            <input type="text" class="form-control" name="address[code]" placeholder="邮编">
                        </div>
                        <?php endif; ?>
                        <?php if($setting['address_setting']['is_email']) : ?>
                         <div class="col-sm-2">
                            <input type="text" class="form-control" name="address[email]" placeholder="邮箱">
                        </div>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-gradient-success address">提交保存</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
window.onload = function(){
 $(".address").click(res=>{
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

     $.ajax({
        url:'', 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                  console.log(res.msg);
                  var url = "<?php echo(urlCreate('/web/user/address')) ?>";
                  window.location.href= url;
            }

        }
     })
    return false;
 })
}
</script>						
						