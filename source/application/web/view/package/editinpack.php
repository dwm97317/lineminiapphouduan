
 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">新建订单</h4>
    </div>
    <!---第一层---->
    <form role="form" id="ajaxForm">
    <div class="card-body">
        <div class="row border bottom">
            <!---第一层第一列---->
            
            <div class="col-sm-4">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label dropdown-active">运输方式</label>
    	                <div class="col-md-10">
                                <select id="selectize-group" name="line_id">
                                    <option value="">请选择运输方式</option>
                                    <optgroup label="渠道路线">
                                        <?php if (count($line)>0): foreach ($line as $item): ?>
                                            <option value="<?= $item['id'] ?>"   <?= $detail['line']['id'] == $item['id']?'selected':'' ?> ><?= $item['name'] ?></option>
                                        <?php endforeach;endif; ?>
                                    </optgroup>
                                </select>
                        </div> 
                    </div>
            </div>
            <!---第一层第二列---->
            <div class="col-sm-4">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">订单号</label>
                        <div class="col-sm-10">
                            <input  type="text" class="form-control" name="order_no" placeholder="选填订单号" value="<?= $detail['t_order_sn'] ?>" >
                        </div>
                    </div>

            </div>
            <!---第一层第三列---->
            <div class="col-sm-4">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">备注</label>
                        <div class="col-sm-10">
                            <input  type="text" class="form-control" name="remark" placeholder="备注" value="<?= $detail['remark'] ?>" >
                        </div>
                    </div>
            </div>
             
        </div>
    </div>
     <!---收件人-->
    <div class="card-body">
        <div class="" style="margin-bottom:10px;">
            <span style="padding: 13px 18px;" data-toggle="modal" data-target="#basic-modal" class="badge badge-primary">选择收件人</span>
        </div>
        <div class="row border bottom">
            <div class="col-sm-4">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label dropdown-active">选择国家</label>
    	                <div class="col-md-9">
    	                        <select id="selectize-dropdown" name="address[country_id]">
                                    <?php if (count($countryList)>0): foreach ($countryList as $item): ?>
                                            <option value="<?= $item['id'] ?>"  <?= $detail['address']['country_id'] == $item['id']?'selected':'' ?>><?= $item['title'] ?></option>
                                    <?php endforeach;endif; ?>
                                </select>
                        </div> 
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">收件省(洲)</label>
                        <div class="col-sm-9">
                        <input id='addresss_id' type="hidden" class="form-control" name="address[address_id]" placeholder="选填" value="<?= $detail['address']['address_id'] ?>" >
                        <input id='province' type="text" class="form-control" name="address[province]" placeholder="选填" value="<?= $detail['address']['province'] ?>" >
                        <input id='country_id' type="hidden" class="form-control" name="address[country_id]" placeholder="选填" value="<?= $detail['address']['country_id'] ?>" >
                        <input id='country' type="hidden" class="form-control" name="address[country]" placeholder="选填" value="<?= $detail['address']['country'] ?>" >
                        <input id='inpack' type="hidden" class="form-control" name="inpackid" placeholder="选填" value="<?= $detail['id'] ?>" >
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">街道号</label>
                        <div class="col-sm-9">
                            <input id="street" type="text" autocomplete="off" class="form-control" name="address[street]" placeholder="街道号"  value="<?= $detail['address']['street'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">邮箱</label>
                        <div class="col-sm-9">
                            <input id="email" type="text" autocomplete="off" class="form-control" name="address[email]" placeholder="邮箱"  value="<?= $detail['address']['email'] ?>">
                        </div>
                    </div>
            </div>
            <div class="col-sm-4">
                   
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件姓名</label>
                        <div class="col-sm-9">
                            <input id="name" type="text" class="form-control" name="address[name]" placeholder="收件姓名" value="<?= $detail['address']['name'] ?>" >
                        </div>
                    </div>
					
					 <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件城市</label>
                        <div class="col-sm-9">
                            <input id="city"  type="text" autocomplete="off" class="form-control" name="address[city]" placeholder="收件城市"  value="<?= $detail['address']['city'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">门牌号</label>
                        <div class="col-sm-9">
                            <input id="door"  type="text" autocomplete="off" class="form-control" name="address[door]" placeholder="门牌号"  value="<?= $detail['address']['door'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">详细地址</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" autocomplete="off"  id="detail" placeholder="详细地址" rows="2"><?= $detail['address']['detail'] ?></textarea>
                        </div>
                    </div>
                    
            </div>
            <div class="col-sm-4">
                   
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">手机号</label>
                        <div class="col-sm-9">
                            <input id="phone" type="text" class="form-control" name="address[phone]" placeholder="手机号" value="<?= $detail['address']['phone'] ?>" >
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件人区</label>
                        <div class="col-sm-9">
                            <input id="region"  type="text" autocomplete="off" class="form-control" name="address[region]" placeholder="收件人区"  value="<?= $detail['address']['region'] ?>">
                        </div>
                    </div>
					 <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件邮编</label>
                        <div class="col-sm-9">
                            <input id="code"  type="text" autocomplete="off" class="form-control" name="address[code]" placeholder="收件邮编"  value="<?= $detail['address']['code'] ?>">
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <!---寄件人-->
    <div class="card-body">
        <div class="" style="margin-bottom:10px;">
            <span style="padding: 13px 18px;" data-toggle="modal" data-target="#address-modal" class="badge badge-primary">选择寄件人</span>
        </div>
        <div class="row border bottom">
            <div class="col-sm-4">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label dropdown-active">选择国家</label>
    	                <div class="col-md-9">
    	                        <select id="jcountry" name="jaddress[country_id]">
                                    <?php if (count($countryList)>0): foreach ($countryList as $item): ?>
                                            <option value="<?= $item['id'] ?>" <?= $detail['jaddress']['country_id'] == $item['id']?'selected':'' ?>><?= $item['title'] ?></option>
                                    <?php endforeach;endif; ?>
                                </select>
                        </div> 
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">收件省(洲)</label>
                        <div class="col-sm-9">
                            <input id='jaddresss_id' type="hidden" class="form-control" name="jaddress[address_id]" placeholder="选填" value="<?= $detail['jaddress']['address_id'] ?>" >
                            <input id='jprovince' type="text" class="form-control" name="jaddress[province]" placeholder="选填" value="<?= $detail['jaddress']['province'] ?>" >
                            <input id='jcountry_id' type="hidden" class="form-control" name="jaddress[country_id]" placeholder="选填" value="<?= $detail['jaddress']['country_id'] ?>" >
                            <input id='jcountryy' type="hidden" class="form-control" name="jaddress[country]" placeholder="选填" value="<?= $detail['jaddress']['country'] ?>" >
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">街道号</label>
                        <div class="col-sm-9">
                            <input id="jstreet" type="text" autocomplete="off" class="form-control" name="jaddress[street]" placeholder="街道号"  value="<?= $detail['jaddress']['street'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">邮箱</label>
                        <div class="col-sm-9">
                            <input id="jemail" type="text" autocomplete="off" class="form-control" name="jaddress[email]" placeholder="邮箱"  value="<?= $detail['jaddress']['email'] ?>">
                        </div>
                    </div>
            </div>
            <div class="col-sm-4">
                   
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件姓名</label>
                        <div class="col-sm-9">
                            <input id="jname" type="text" class="form-control" name="jaddress[name]" placeholder="收件姓名" value="<?= $detail['jaddress']['name'] ?>" >
                        </div>
                    </div>
					
					 <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件城市</label>
                        <div class="col-sm-9">
                            <input id="jcity"  type="text" autocomplete="off" class="form-control" name="jaddress[city]" placeholder="收件城市"  value="<?= $detail['jaddress']['city'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">门牌号</label>
                        <div class="col-sm-9">
                            <input id="jdoor"  type="text" autocomplete="off" class="form-control" name="jaddress[door]" placeholder="门牌号"  value="<?= $detail['jaddress']['door'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">详细地址</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" autocomplete="off"  id="jdetail" placeholder="填写详细地址" rows="2"><?= $detail['jaddress']['detail'] ?></textarea>
                        </div>
                    </div>
                    
            </div>
            <div class="col-sm-4">
                   
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">手机号</label>
                        <div class="col-sm-9">
                            <input id="jphone" type="text" class="form-control" name="jaddress[phone]" placeholder="手机号" value="<?= $detail['jaddress']['phone'] ?>" >
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件人区</label>
                        <div class="col-sm-9">
                            <input id="jregion"  type="text" autocomplete="off" class="form-control" name="jaddress[region]" placeholder="收件人区"  value="<?= $detail['jaddress']['region'] ?>">
                        </div>
                    </div>
					 <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">收件邮编</label>
                        <div class="col-sm-9">
                            <input id="jcode"  type="text" autocomplete="off" class="form-control" name="jaddress[code]" placeholder="收件邮编"  value="<?= $detail['jaddress']['code'] ?>">
                        </div>
                    </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-overflow">
            <table class="table table-xl border">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">中文品名</th>
                        <th scope="col">*英文品名</th>
                        <th scope="col">配货</th>
						<th scope="col">*单个商品重量(KG)</th>
						<th scope="col">*产品数量</th>
						<th scope="col">*总金额USD</th>
                        <th scope="col">海关编码</th>
                        <th width="70" class="text-center" scope="col">操作</th>
                        <th width="70" class="text-center" scope="col"></th>
                    </tr>
                </thead>
                <tbody class="step_mode">
                    <?php if (count($detail['item'])>0): foreach ($detail['item'] as $item): ?>
                    <?php if (count($item['packitem'])>0): foreach ($item['packitem'] as $it): ?>
                    <tr>
                        <td><div class="col-sm-12">
                            <input  type="text" class="form-control" name="goods[cnname][]" placeholder="选填" value="<?=$it['goods_name'] ?>" >
                            <input id="packitemid"  type="hidden" class="form-control" name="goods[id][]" placeholder="选填" value="<?=$it['id'] ?> " >
                        </div></td>
						<td>
						    <div class="col-sm-12"><input  type="text" class="form-control" name="goods[enname][]" placeholder="选填" value="<?= $it['class_name_en'] ?>" ></div>
						</td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[peihuo][]" placeholder="选填" value="<?= $it['distribution'] ?>" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[oneweight][]" placeholder="选填" value="<?= $it['unit_weight'] ?>" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[num][]" placeholder="选填" value="<?= $it['product_num'] ?>" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[price][]" placeholder="选填" value="<?= $it['all_price'] ?>" ></div>
                        </td>
						<td>
						    <div class="col-sm-12"><input  type="text" class="form-control" name="goods[haiguannum][]" placeholder="选填" value="<?= $it['customs_code'] ?>" ></div>
						</td>
                        <td class="text-center"><a href="javascript:;" onclick="addfreeRule(this)" class="text-gray">新增</a></td>
                        <td class="text-center" onclick="freeRuleDel(this)"><a href="javascript:;"  class="text-gray">删除</a></td>
                    </tr>
                     <?php endforeach;else: ?>
                      <tr>
                        <td><div class="col-sm-12">
                            <input  type="text" class="form-control" name="goods[cnname][]" placeholder="选填" value="" >
                            
                        </div></td>
						<td>
						    <div class="col-sm-12"><input  type="text" class="form-control" name="goods[enname][]" placeholder="选填" value="" ></div>
						</td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[peihuo][]" placeholder="选填" value="" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[oneweight][]" placeholder="选填" value="" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[num][]" placeholder="选填" value="" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="goods[price][]" placeholder="选填" value="" ></div>
                        </td>
						<td>
						    <div class="col-sm-12"><input  type="text" class="form-control" name="goods[haiguannum][]" placeholder="选填" value="" ></div>
						</td>
                        <td class="text-center"><a href="javascript:;" onclick="addfreeRule(this)" class="text-gray">新增</a></td>
                        <td class="text-center" onclick="freeRuleDel(this)"><a href="javascript:;"  class="text-gray">删除</a></td>
                    </tr>
                     
                    <?php endif;endforeach;endif; ?>
              </tbody>
            </table>
        </div>
    </div>
    <div class="card-body">
        <div class="row border bottom">
            <div class="col-sm-4">
                    <button type="button"  onclick="submitform()" value="<?= $detail['id'] ?>" id="yubao" class="btn btn-gradient-success yubao">保存修改</button>
                    <!--<button type="button" onclick="caogaosubmitform()" id="caogao" class="btn btn-gradient-success yubao">存入草稿</button>-->
            </div>
        </div>
    </div>

    </form>
</div>
<div class="modal fade" id="basic-modal" type="text/template">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>选择收件地址</h4>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <select class="form-control" id="address_id"  onchange="doSelect(this)">
                            <option value="">请选择收件地址</option>
                    <?php if (count($address)>0): foreach ($address as $item): ?>
                            <option  value="<?= $item['address_id'] ?>"><?= $item['address_id'] ?> - <?= $item['name'] ?> - <?= $item['phone'] ?></option>
                    <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer no-border">
                <div class="text-right">
                    <button class="btn btn-default"  data-dismiss="modal">取消</button>
                    <button class="btn btn-success" id="address" data-dismiss="modal">确认</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="address-modal" type="text/template">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>选择寄件地址</h4>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <select class="form-control" id="jaddress_id"  onchange="dojSelect(this)">
                            <option value="">请选择寄件地址</option>
                    <?php if (count($jaddress)>0): foreach ($jaddress as $item): ?>
                            <option  value="<?= $item['address_id'] ?>"><?= $item['address_id'] ?> - <?= $item['name'] ?> - <?= $item['phone'] ?></option>
                    <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer no-border">
                <div class="text-right">
                    <button class="btn btn-default"  data-dismiss="modal">取消</button>
                    <button class="btn btn-success" id="address" data-dismiss="modal">确认</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function addfreeRule(){
        var amformItem = document.getElementsByClassName('step_mode')[0];
        var Item = document.createElement('tr');
      
        var _html = '<td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[cnname][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[enname][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[peihuo][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[oneweight][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[num][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[price][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="goods[haiguannum][]" placeholder="选填" value="" ></div></td><td class="text-center"><a href="javascript:;" onclick="addfreeRule(this)" class="text-gray">新增</a></td><td onclick="freeRuleDel(this)"><a href="javascript:;"  class="text-gray">删除</a></td>';
        Item.innerHTML = _html;
        amformItem.appendChild(Item);
}

// 删除
function freeRuleDel(_this){
   var amformItem = document.getElementsByClassName('step_mode')[0];
   var id = $("#packitemid")[0].value;
   var parent = _this.parentNode;
   amformItem.removeChild(parent);
   if(id!=''){
    var url = "<?php echo(urlCreate('/web/package/deletepackitem')) ?>";
       $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:{id:id},
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                $('#addresss_id').val(res.data.address_id);
            }

        }
     })
   }
}
//触发选择地址事件
function doSelect(_this){
  var address_id = $("#address_id").val();
  console.log(address_id);
  var url = "<?php echo(urlCreate('/web/package/getuseraddress')) ?>";
  $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:{address_id:address_id},
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                $('#addresss_id').val(res.data.address_id);
                $('#country_id').val(res.data.country_id);
                $('#country').val(res.data.country);
                $('#name').val(res.data.name);
                $('#phone').val(res.data.phone);
                $('#city').val(res.data.city);
                $('#email').val(res.data.email);
                $('#street').val(res.data.street);
                $('#province').val(res.data.province);
                $('#door').val(res.data.door);
                $('#code').val(res.data.code);
                $('#region').val(res.data.region);
                $('#detail').val(res.data.detail);
                // $('#selectize-dropdown-selectized').val(res.data.country_id);
                // $('#selectize-dropdown-selectized').text(res.data.country);
                // $('#selectize-dropdown').val(res.data.country_id);
                // $('#selectize-dropdown').text(res.data.country);
                $('.single .item').val(res.data.country_id);
                $('.single .item').text(res.data.country);
                console.log(res.data);
                // window.location.href= url;
            }

        }
     })
     return; 
}

function dojSelect(_this){
  var address_id = $("#jaddress_id").val();
  console.log(address_id);
  var url = "<?php echo(urlCreate('/web/package/getuseraddress')) ?>";
  $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:{address_id:address_id},
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                $('#jaddresss_id').val(res.data.address_id);
                $('#jcountry_id').val(res.data.country_id);
                // $('#jcountry').val(res.data.country);
                $('#jname').val(res.data.name);
                $('#jphone').val(res.data.phone);
                $('#jcity').val(res.data.city);
                $('#jemail').val(res.data.email);
                $('#jstreet').val(res.data.street);
                $('#jprovince').val(res.data.province);
                $('#jdoor').val(res.data.door);
                $('#jcode').val(res.data.code);
                $('#jregion').val(res.data.region);
                $('#jdetail').val(res.data.detail);
                // $('#jselectize-dropdown-selectized').val(res.data.country_id);
                // $('#jselectize-dropdown-selectized').text(res.data.country);
                // $('#jselectize-dropdown').val(res.data.country_id);
                // $('#jselectize-dropdown').text(res.data.country);
                $('#jcountry').val(res.data.country_id);
                $('#jcountryy').text(res.data.country);
                console.log(res.data);
                // window.location.href= url;
            }

        }
     })
     return; 
}
  
function caogaosubmitform(){
    
     var formData = $('#ajaxForm').serializeArray(); 
     var url = "<?php echo(urlCreate('/web/package/createcgorder')) ?>";
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     });

     if(formJson['line_id']==''){
         layer.alert('请选择运输方式');
         return false;
     }
     if(formJson['address']['name']==''){
         layer.alert('请填写收件人名');
         return false;
     } 

     $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                 layer.alert(res.msg);
                 setTimeout(function(){ location.reload();},1000);
            }else{
                layer.alert(res.msg);
            }

        }
     })
    return false;   
    
    
}  

function submitform(){
    var formData = $('#ajaxForm').serializeArray();
    var id = $("#yubao")[0].value;
    console.log();
     var url = "<?php echo(urlCreate('/web/package/editorder')) ?>";
    var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     });

     if(formJson['line_id']==''){
         layer.alert('请选择运输方式');
         return false;
     }
     if(formJson['address[name]']==''){
         layer.alert('请填写收件人名');
         return false;
     } 

     $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                 layer.alert(res.msg);
                 setTimeout(function(){ location.reload();},1000);
            }else{
                layer.alert(res.msg);
            }

        }
     })
    return false; 
}


</script>
<style>
#jcountry{
    width: 100%;
    height: 46px;
    border-radius: 6px;
    border: 1px solid #e4e5e7;
    border-radius: 5px;
    box-shadow: none;
    line-height: 2.2;
    font-size: 14px;
    color: #8a8a8a;
}
#jcountry option{
    padding:5px 0px;
    line-height: 10px;
}
    
</style>