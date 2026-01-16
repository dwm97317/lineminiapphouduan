 <div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">申请预报</h4>
    </div>    
    <div class="card-body">
        <form role="form" id="ajaxForm">
        <div class="table-overflow">
            <table class="table table-xl border">
                <thead class="thead-light">
                    <tr>
                        <th scope="col">快递公司</th>
                        <th scope="col">*快递单号</th>
                        <th scope="col">货物品名</th>
						<th scope="col">*货物类型</th>
						<th scope="col">*货物数量</th>
						<th scope="col">*货物单价</th>
                        <th scope="col">货物总价</th>
                        <th scope="col">备注说明</th>
                        <th width="70" class="text-center" scope="col">操作</th>
                        <th width="70" class="text-center" scope="col"></th>
                    </tr>
                </thead>
                <tbody class="step_mode">
                    <tr>
                        <td>
                            <div class="col-sm-12">
                                <select class="form-control" name="yubao[express_id][]">
                                    <?php if (isset($express) && !$express->isEmpty()):
                                    foreach ($express as $itemex): ?>
                                             <option value="<?= $itemex['express_id'] ?>"><?= $itemex['express_name'] ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </td>
						<td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[express_num][]" placeholder="选填" value="" ></div></td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="yubao[goods_name][]" placeholder="选填" value="" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12">
                                <select class="form-control" name="yubao[class_id][]">
                                    <?php if (isset($category) && !$category->isEmpty()):
                                    foreach ($category as $itemexs): ?>
                                             <option value="<?= $itemexs['category_id'] ?>"><?= $itemexs['name'] ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="yubao[product_num][]" placeholder="选填" value="" ></div>
                        </td>
                        <td>
                            <div class="col-sm-12"><input  type="text" class="form-control" name="yubao[one_price][]" placeholder="选填" value="" ></div>
                        </td>
						<td>
						    <div class="col-sm-12"><input  type="text" class="form-control" name="yubao[all_price][]" placeholder="选填" value="" ></div>
						</td>
						<td>
						    <div class="col-sm-12"><input  type="text" class="form-control" name="yubao[remark][]" placeholder="选填" value="" ></div>
						</td>
                        <td class="text-center"><a href="javascript:;" onclick="addfreeRule(this)" class="text-gray">新增</a></td>
                    </tr>
              </tbody>
            </table>
        </div>
        </form>
    </div>
    <div class="card-body">
        <div class="row border bottom">
            <div class="col-sm-4">
                    <button type="button" onclick="submitform()" id="yubao" class="btn btn-gradient-success yubao">保存</button>
                    <button type="button" onclick="caogaosubmitform()" id="caogao" class="btn btn-gradient-success yubao">存入草稿</button>
            </div>
        </div>
    </div>
</div>
<style>
    .step_mode td{
        padding:10px 0px !important;
    }    

</style>
<script>
function submitform(){
    var formData = $('#ajaxForm').serializeArray(); 
     var url = "<?php echo(urlCreate('/web/package/packreport')) ?>";
    //  var formJson = {};
    //  formData.forEach((val)=>{
    //      console.log(val['name']);
    //     formJson[val['name']] = val['value']; 
       
    //  });
    var dataNew=[];
    for(let i=0;i<formData.length;i++){
        formData[i].value= formData[i]['value'];
        dataNew.push(formData[i])
    }
    
    
        // console.log(dataNew);
        // return;
     if(dataNew['line_id']==''){
         layer.alert('请选择运输方式');
         return false;
     }
     if(dataNew['address[name]']==''){
         layer.alert('请填写收件人名');
         return false;
     } 

     $.ajax({
        url:url, 
        type:'POST',
        dataType:"json",
        data:dataNew,
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

function addfreeRule(){
        var amformItem = document.getElementsByClassName('step_mode')[0];
        var Item = document.createElement('tr');
      
        var _html = '<td><div class="col-sm-12"><select class="form-control" name="yubao[express_id][]"><?php if (isset($express) && !$express->isEmpty()):foreach ($express as $itemex): ?><option value="<?= $itemex['express_id'] ?>"><?= $itemex['express_name'] ?></option><?php endforeach; endif; ?></select></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[express_num][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[goods_name][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><select class="form-control" name="yubao[class_id][]"><?php if (isset($category) && !$category->isEmpty()):foreach ($category as $itemexs): ?><option value="<?= $itemexs['category_id'] ?>"><?= $itemexs['name'] ?></option><?php endforeach; endif; ?></select></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[product_num][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[one_price][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[all_price][]" placeholder="选填" value="" ></div></td><td><div class="col-sm-12"><input  type="text" class="form-control" name="yubao[remark][]" placeholder="选填" value="" ></div></td><td class="text-center" onclick="freeRuleDel(this)"><a href="javascript:;"  class="text-gray">删除</a></td>';
        Item.innerHTML = _html;
        amformItem.appendChild(Item);
}

// 删除
function freeRuleDel(_this){
   var amformItem = document.getElementsByClassName('step_mode')[0];
   console.log(amformItem);
   var parent = _this.parentNode;
   amformItem.removeChild(parent);
}
</script>