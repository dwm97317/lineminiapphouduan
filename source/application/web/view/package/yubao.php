 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">包裹预报</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-8">
                <form role="form" id="ajaxForm">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">寄送国家 *</label>
                        <div class="col-sm-3">
                                <select class="form-control" name="yubao[country_id]">
                                <?php if (isset($country) && !$country->isEmpty()):
                                foreach ($country as $countryitem): ?>
                                         <option value="<?= $countryitem['id'] ?>"><?= $countryitem['title'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">集运仓库 *</label>
                        <div class="col-sm-3">
                            <select class="form-control" name="yubao[shop_id]">
                                <?php if (isset($shopList) && !$shopList->isEmpty()):
                                foreach ($shopList as $item): ?>
                                         <option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
					
					
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">快递单号</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="yubao[express_num]" placeholder="快递单号" required="">
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">快递名称 *</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="yubao[express_id]">
                                <?php if (isset($express) && !$express->isEmpty()):
                                foreach ($express as $itemex): ?>
                                         <option value="<?= $itemex['express_id'] ?>"><?= $itemex['express_name'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
					
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">物品分类</label>
                        <div class="col-sm-10">
                            <div class="d-flex align-items-center h-100">
                                <div class="d-block" id="category">
								</div>
								<div class="d-block" >
									<button data-toggle="modal" data-target="#basic-modal" class="btn btn-info">选择物品类目</button>
								</div>
							</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">物品价值</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="yubao[price]" placeholder="物品价值">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">备注</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="yubao[remark]" placeholder="备注" required="">
                        </div>
                    </div>

                    <button class="btn btn-gradient-success yubao">提交预报</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!--modal---->
<div class="modal fade" id="basic-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>选择物品类目</h4>
            </div>
            <div class="modal-body">
				<?php if (isset($category)):
                 foreach ($category as $item): ?>
				<div class="m-t-20">
					<h5><?= $item['name'] ?></h5>
					<?php if (isset($item['child'])):
					     foreach ($item['child'] as $items): ?>
                             <span class="badge badge-pill badge-default category" data-select=false data-id="<?= $items['category_id'] ?>" onclick="doSelect(this)"><?= $items['name'] ?></span>
                        <?php endforeach; endif; ?>
                </div>
				<?php endforeach; endif; ?>
            </div>
            <div class="modal-footer no-border">
                <div class="text-right">
                    <button class="btn btn-default" data-dismiss="modal">取消</button>
                    <button class="btn btn-success" data-dismiss="modal">确认</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
  var categoryId = [];
  
  function doSelect(_this){
     var id = _this.getAttribute('data-id');
     var select = _this.getAttribute('data-select');
     var _s = false;
     console.log(select,'5555');
     if (select=='true'){
         _s = false;
         for (k in categoryId){
            if (id==categoryId[k]['id']){
                categoryId.splice(k,1);
            }
         }
         _this.className = 'badge badge-pill badge-default category';
     }else{
         categoryId.push({
             id:id,
             text:_this.innerHTML,
         });
         _s = true;
         _this.className = 'badge badge-pill category badge-primary';
     }
 
     var _text = '';
     for (k in categoryId){
         _text+= "<span>"+categoryId[k]['text']+"</span> ";
     }
     $('#category').html(_text);
     _this.setAttribute('data-select',_s);
  }
  


window.onload = function(){
 $(".yubao").click(res=>{
     var formData = $('#ajaxForm').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     })
     var _ids = '';
     for (k in categoryId){
         _ids+= categoryId[k]['id']+',';
     }
     formJson['yubao[categoryIds]'] = _ids;
     console.log(formJson,567);
     $.ajax({
        url:'', 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            console.log(res,456789);
            if (res['code']==1){
                console.log(res.msg);
                 var url = "<?php echo(urlCreate('/web/package/index')) ?>";
                window.location.href= url;
            }

        }
     })
    return false;
 })
}
</script>						
						