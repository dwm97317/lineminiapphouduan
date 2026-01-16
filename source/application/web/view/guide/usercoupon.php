<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">优惠券</h4>
    </div>
    <div class="card-body">
        <div class="pill-info">
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item">
                    <a href="#pills-info-1" onclick="changese(1)" class="nav-link active" role="tab" data-toggle="tab">未领取</a>
                </li>
                <li class="nav-item">
                    <a href="#pills-info-2" onclick="changese(2)" class="nav-link" role="tab" data-toggle="tab">我的优惠券</a>
                </li>
                <li class="nav-item">
                    <a href="#pills-info-3" onclick="changese(3)" class="nav-link" role="tab" data-toggle="tab">已失效</a>
                </li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane fade in active" id="pills-info-1">
                    <div class="p-h-15 p-v-20">
                        <!--搜索框-->
                        <div class="table-overflow">
                            <table id="dt-opt" class="table table-hover table-xl">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>优惠券名称</th>
                						<th>优惠券类型</th>
                                        <th>最低消费金额</th> 
                                        <th>优惠方式</th>
                                        <th>有效期</th>
                                        <th>创建时间</th>
                                        <th class="text-center">操作</th>
                                    </tr>
                                </thead>
                                <tbody class="gradfk">
                                    <?php if (!$couponlist->isEmpty()): foreach ($couponlist as $key => $item): ?>
                                    <?php if ($item['is_receive'] == false) : ?>
                                    <tr>
                                        <td><?= $item['coupon_id'] ?></td>
                                        <td><a class="j-search"  href="javascript:void(0)"><?= $item['name'] ?></a></td>
                                        <td><?= $item['coupon_type']['text'] ?></td>
                                        <td><?= $item['min_price'] ?></td>
                                        <td>
                                            <?php if ($item['coupon_type']['value'] == 10) : ?>
                                                <span>立减 <strong><?= $item['reduce_price'] ?></strong> 元</span>
                                            <?php elseif ($item['coupon_type']['value'] == 20) : ?>
                                                <span>打 <strong><?= $item['discount'] ?></strong> 折</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['expire_type'] == 10) : ?>
                                                <span>领取 <strong><?= $item['expire_day'] ?></strong> 天内有效</span>
                                            <?php elseif ($item['expire_type'] == 20) : ?>
                                                <span><?= $item['start_time']['text'] ?>
                                                    ~ <?= $item['end_time']['text'] ?></span>
                                            <?php endif; ?>
                                        </td>
                						<td><?= $item['create_time'] ?></td>
                                        <td class="text-center font-size-18">
                                            <?php if ($item['is_receive'] == false) : ?>
                                            <button  id="buzaidian" class="m-t-20 btn-xs btn btn-primary btn-rounded btn-float" id="resetbag">领取</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="11" class="am-text-center">暂无记录</td>
                                        </tr>
                                    <?php endif; ?>
                			   </tbody>
                            </table>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
        
    </div>       
</div>


<div class="modal fade" id="basic-modal" type="text/template">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>疑難件處理</h4>
            </div>
            <div class="modal-body">
                <form role="form" id="ajaxForm">
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">處理方式</label>
                        <div class="col-sm-9">
                           <select id="problem_type" name="problem_type" class="form-control">
                                <option value="1">原路恢復</option>
                                <option value="2">駁回申請</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label control-label">結果描述</label>
                        <div class="col-sm-9">
                            <input id="problem_result" type="text" class="form-control" name="problem_result" placeholder="請輸入处理結果的描述" value="">
                            <input id="dataidss" type="hidden" class="form-control"  value="">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer no-border">
                <div class="text-right">
                    <button class="btn btn-default"  data-dismiss="modal">取消</button>
                    <button class="btn btn-success" onclick="changestatek()" data-dismiss="modal">确认</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script id="tpl-inpack" type="text/template">
    {{each data value}}
        <tr>
            <td>
                <div class="checkbox">
                    <input id="selectable{{value.solicitation_Id}}" type="checkbox">
                    <label for="selectable{{value.solicitation_Id}}">{{ $index+1 }}</label>
                </div> 
            </td>
            <td><a class="j-searchf">{{value.customer}}</a></td>
            <td>{{value.telphone}}</td>
    		<td><span class="badge badge-pill badge-gradient-success">{{value.number}}</span></td>
            <td>{{value.weight}}</td>
    		<td>{{value.advance?value.advance:0}}</td>
    		<td>{{value.topay}}</td>
    		<td>{{value.create_time}}</td>
			<td>{{value.problem_type?value.problem_type:''}}</td>
			<td>{{value.problem_time}}</td>
            <td class="text-center font-size-18">
               <button  data-target="#basic-modal" data-toggle="modal" data-id="{{value.solicitation_Id}}" data-number="{{value.number}}" data-type="out" onclick="changestates(this)" class="btn btn-success yinan">疑難件</button>
            </td>
        </tr>
    {{/each}}
</script>
<script id="tpl-inpackrr" type="text/template">
    {{each data value}}
        <tr>
            <td>
                <div class="checkbox">
                    <input id="selectable{{value.solicitation_Id}}" type="checkbox">
                    <label for="selectable{{value.solicitation_Id}}">{{ $index+1 }}</label>
                </div> 
            </td>
            <td><a class="j-searchf">{{value.customer}}</a></td>
            <td>{{value.telphone}}</td>
    		<td><span class="badge badge-pill badge-gradient-success">{{value.number}}</span></td>
            <td>{{value.weight}}</td>
    		<td>{{value.advance?value.advance:0}}</td>
    		<td>{{value.topay}}</td>
    		<td>{{value.create_time}}</td>
			<td>{{value.problem_type?value.problem_type:''}}</td>
			<td>{{value.problem_time}}</td>
        </tr>
    {{/each}}
</script>
<script>
function changestatek(){
    var dataid =   $('#dataidss')[0].value;
    var remark =  $('#problem_result')[0].value;
    var type =  $('#problem_type')[0].value;
    // console.log(type);return;
    var url = "index.php?s=/web/Package/doproblem"; 
      $.ajax({
        url:url,
        type:'POST',
        dataType:"json",
        data:{id:dataid,type:type,remark:remark},
        success:function(res){
            if (res['code']==1){
               location.reload();
            }
        }
     }) 
}

function changese(e){
    console.log(e,567);
    var type = e;
    var formData =$("#numberid")[0].value;
    console.log(formData);
     $.ajax({
        url:'index.php?s=/web/Package/problempack', 
        type:'POST',
        dataType:"json",
        data:{number:formData,type:e},
        success:function(res){
            if (res['code']==1){
                if(e==3){
                    var list = template('tpl-inpackrr',res.data)
                    $('.gradfk').html(list);
                }else{
                     var list = template('tpl-inpack',res.data)
                    $('.gradfk').html(list);
                }
                
            }
        }
     })
}

function changestates(_this){
   var dataid =  _this.getAttribute("data-id");
   console.log(dataid);
   var datatype =  _this.getAttribute("data-type");
   var number =  _this.getAttribute("data-number");
   $('#dataidss').val(dataid);
   $('#datatypess').val(datatype);
   $('#numberss').val(number);
}

window.onload = function(){

//搜索功能
 $(".search").click(res=>{
     var formData =$("#numberid")[0].value;
     var type =  $('#problem_type')[0].value;
     console.log(formData);
     $.ajax({
        url:'index.php?s=/web/Package/problempack', 
        type:'POST',
        dataType:"json",
        data:{number:formData,type:type},
        success:function(res){
            if (res['code']==1){
                var list = template('tpl-inpack',res.data)
                $('.gradfk').html(list);
            }

        }
     })
    return false;
 })
}
</script>