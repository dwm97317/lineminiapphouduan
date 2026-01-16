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
                                <select class="form-control" name="package[country_id]">
                                <?php if (isset($country) && !$country->isEmpty()):
                                foreach ($country as $countryitem): ?>
                                         <option value="<?= $countryitem['id'] ?>" name="package[country_id]"
                                                <?= $countryitem['id'] == $detail['country_id'] ? 'selected' : '' ?>><?= $countryitem['title'] ?>
                                            </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">集运仓库 *</label>
                        <div class="col-sm-3">
                            <select class="form-control" name="package[shop_id]">
                                <?php if (isset($shopList) && !$shopList->isEmpty()):
                                foreach ($shopList as $item): ?>
                                         <!--<option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>-->
                                         <option value="<?= $item['shop_id'] ?>" name="package[shop_id]"
                                                <?= $item['shop_id'] == $detail['storage_id'] ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                            </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
					
					
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">快递单号</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="package[express_num]" placeholder="快递单号" required="" value="<?= $detail['express_num'] ?>">
                        </div>
                    </div>
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">快递名称 *</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="package[express_id]">
                                <?php if (isset($express) && !$express->isEmpty()):
                                foreach ($express as $itemex): ?>
                                         <option value="<?= $itemex['express_id'] ?>" name="package[express_id]"
                                                <?= $itemex['express_id'] == $detail['express_id'] ? 'selected' : '' ?>><?= $itemex['express_name'] ?>
                                            </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>
					
					<div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">物品分类</label>
                        <div class="col-sm-10">
                            <div class="d-flex align-items-center h-100">
                                <div class="d-block mr-4" id="category">
                                     <?php if (isset($detail['category_attr']) && !$detail['category_attr']->isEmpty()):
                                        foreach ($detail['category_attr'] as $itemex): ?>   
                                             <span><?= $itemex['class_name'] ?></span>
                                     <?php endforeach; endif; ?>
                                </div>
								<div class="d-block">
									<a style="color:#fff" data-toggle="modal" data-target="#basic-modals" class="btn btn-info">重选物品类目</a>
								</div>
							</div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">物品价值</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="package[price]" placeholder="物品价值" value="<?= $detail['price'] ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label control-label">备注</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" name="package[remark]" placeholder="备注"  value="<?= $detail['remark'] ?>">
                        </div>
                    </div>

                    <button type="button" id="yubao" class="btn btn-gradient-success yubao">提交预报</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!--modal---->
<div class="modal fade" id="basic-modals">
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

<style>
.selected { background:#339999; color:#fff;}
</style>

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
         _this.className = 'badge badge-pill badge-default category selected';
     }
 
     var _text = '';
     for (k in categoryId){
         _text+= "<span>"+categoryId[k]['text']+"</span> ";
     }
     $('#category').html(_text);
     _this.setAttribute('data-select',_s);
  }
  
 
window.onload = function(){
  
 $("#yubao").click(res=>{
     var formData = $('#ajaxForm').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     });
     var _ids = '';
     for (k in categoryId){
         _ids+= categoryId[k]['id']+',';
     }
     formJson['package[categoryIds]'] = _ids;
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
						