<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">价格查询</h4>
    </div>
    <div class="card-body">
        <div class="form-group col-md-12">
            <form id="ajaxForm">
                <div class="input-group">
                    <div class="form-group row col-md-2" style="height:25px;">
                        <label class="col-sm-5 col-form-label control-label dropdown-active">选择国家</label>
    	                <div class="col-md-7">
    	                        <select id="selectize-dropdown" name="country">
                                    <?php if (count($countryList)>0): foreach ($countryList as $item): ?>
                                            <option value="<?= $item['id'] ?>"><?= $item['title'] ?></option>
                                    <?php endforeach;endif; ?>
                                </select>
                        </div> 
                    </div>
                    <div class="form-group row col-md-1" style="height:25px;margin-right:5px;">
                        <input type="text" class="form-control" placeholder="重量" name="weight" id="weight" autocomplete="off">
                    </div>
                    <div class="form-group row col-md-1" style="height:25px;margin-right:5px;">
                        <input type="text" class="form-control" placeholder="体积" name="weigthV" id="weigthV" autocomplete="off">
                    </div>
                    <div class="form-group row col-md-1" style="height:25px;margin-right:5px;">
                        <input type="text" class="form-control" placeholder="长度" name="length" id="length" autocomplete="off">
                    </div>
                    <div class="form-group row col-md-1" style="height:25px;margin-right:5px;">
                        <input type="text" class="form-control" placeholder="宽度" name="width" id="width" autocomplete="off">
                    </div>
                    <div class="form-group row col-md-2">
                        <input type="text" class="form-control" placeholder="高度" name="height" id="height" autocomplete="off">
                    </div>
                    <span class="input-group-append">
                            <button class="btn btn-default btn-icon" type="button" style="height:45px;">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </span> 
                </div>
            </form>
        </div>
				
        <div class="card">
            <div class="card-header border bottom">
                <h4 class="card-title">路线列表</h4>
            </div>
            <div class="card-body">
                <div class="table-overflow">
                    <table class="table table-xl border">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">路线封面</th>
                                <th scope="col">计费模式</th>
                                <th scope="col">路线规则</th>
                                <th scope="col">计费重量(kg)</th>
                                <th scope="col">时效(天)</th>
								<th scope="col">路线价格(元)</th>
								<th scope="col">最小重量</th>
                                <th class="text-center" scope="col">最大重量</th>
                                <th scope="col">操作</th>
                            </tr>
                        </thead>
                        <tbody class="list">
     
                      </tbody>
                    </table>
                </div>
            </div>
        </div>       
    </div>
</div>
<script id="tpl-inpack" type="text/template">
{{ if data.length>0 }}
    {{each data value}}
        <tr>
            <td>{{ value.id}}</td>
            <td>
                <div class="list-media">
                    <div class="list-item">
                        <div class="media-img">
                            <img class="rounded" src="{{ value.image?value.image.file_path:'/assets/api/images/dzx_img179.png'}}" alt="">
                        </div>
                    </div>
                </div>
            </td>
            <td>
            <a target="_blank" class="j-search"  href="<?php echo(urlcreate('/web/package/pricedetail&id={{value.id}}')) ?>">
              {{ value.name}}
            </a>
            </td>
			<td>{{ value.free_mode}}</td>
			<td>{{ value.predict.weight}}</td>
            <td>{{ value.limitationofdelivery}}</td>
            <td>{{ value.predict.price}}</td>
            <td>{{ value.weight_min}}</td>
            <td class="text-center">{{ value.max_weight}}</td>
            <td><a target="_blank" class="j-search"  href="<?php echo(urlcreate('/web/package/pricedetail&id={{value.id}}')) ?>">详情</a></td>
        </tr>
    {{/each}}
{{ else }}
   <tr>
        <td colspan="11" class="am-text-center">暂无记录</td>
    </tr>
{{/if}}
</script>
<script>
window.onload = function(){
     var express_num = (location.search).substring(((location.search).indexOf("num") + 4), (location.search).length);
     var sss = GetRequest();
     console.log(sss,777);
     
     if(sss.express_num){
         document.getElementById('express_num').value = express_num;
         var express_name = express_num;
         $.ajax({
            url:'', 
            type:'POST',
            dataType:"json",
            data:{express_name:express_name},
            success:function(res){
                if (res['code']==1){
                    var list = template('tpl-inpack',res)
                    $('.list').html(list);
                }
    
            }
         })
     }
     
    function GetRequest() {
        var url = location.search; //获取url中"?"符后的字串
        var theRequest = new Object();
        if (url.indexOf("?") != -1) {
            var str = url.substr(1);
            strs = str.split("&");
            for (var i = 0; i < strs.length; i++) {
                theRequest[strs[i].split("=")[0]] = decodeURIComponent(strs[i].split("=")[1]);
            }
        }
        return theRequest;
    }
     
     
 $(".btn").click(res=>{
     var formData = $('#ajaxForm').serializeArray(); 
     var formJson = {};
     formData.forEach((val)=>{
        formJson[val['name']] = val['value']; 
     })
     console.log(formJson,567);
     $.ajax({
        url:'', 
        type:'POST',
        dataType:"json",
        data:formJson,
        success:function(res){
            if (res['code']==1){
                var list = template('tpl-inpack',res)
                $('.list').html(list);
            }

        }
     })
    return false;
 })
}
</script>