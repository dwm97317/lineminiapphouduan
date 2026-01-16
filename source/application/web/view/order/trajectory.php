<div class="card">
    <div class="card-header border bottom">
        <h4 class="card-title">轨迹查询</h4>
    </div>
    <div class="card-body">
        <div class="form-group col-md-3">
            <form id="ajaxForm">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="请输入包裹单号" name="express_name" id="express_num">
                        <span class="input-group-append">
                            <button class="btn btn-default btn-icon" type="button">
                                <i class="mdi mdi-magnify"></i>
                            </button>
                        </span>
                </div>
            </form>
        </div>
				
        <div class="row m-v-15">
            <div class="col-md-3">
                <ul class="list">
                </ul>
            </div>
        </div>       
    </div>
</div>
<script id="tpl-inpack" type="text/template">
{{ if data.length>0 }}
    {{each data value}}
        <li>{{ value.created_time}} - {{value.logistics_describe}}</li>
    {{/each}}
{{ else }}
    <li>暂无轨迹</li>
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