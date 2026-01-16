<style>
    .wd {
        width: 200px;
    }
    .country-search-panle {
        width: 100%; height: auto; max-height: 300px;
        background: #fff;
        border: 1px solid #eee;
        position: absolute;
        top:25px; left: 0; z-index: 999;
    }
    .country-search-title { height: 25px; line-height: 25px; font-size: 14px; padding-left: 10px;}
    .country-search-content { width: 100%; height: auto; }
    .country-search-content p { padding-left: 10px; height: 25px; cursor: pointer; line-height: 25px; font-size: 14px;}
    .country-search-content p:hover { background: #0b6fa2; color: #fff;}
    .hidden { display: none}
    .show { display: block;}
    
    .tip-layer { width: 250px; border-radius:10px; height:100px; background:#fff; box-shadow:1px 1px 1px 1px #ccc; position:absolute;top:20px; right:-250px; transition: .4s;
    -webkit-transition: .4s; /* Safari */}
    .tip-layer-content { font-size:13px; padding:10px; padding-top:5px; color:#666;}
    .tip-layer-title { width:96%; padding-left:4%; font-size:14px; height:40px; line-height:40px;}
    .layer-tips-area { position:absolute; top:10%; width:300px; right:0;}
    .err { color:red;}
    .success { color:green;}
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form  id="form" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">扫码操作</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">加入批次 </label>
                                <div class="am-u-sm-9 am-u-end wd">
                                     <select id="batch_id" name="batch_id"
                                            data-am-selected="{searchBox: 1, btnSize: 'sm', placeholder:'请选择', maxHeight: 400}">
                                         <option value="0">不加入批次</option>
                                        <?php if (isset($batchlist) && !$batchlist->isEmpty()):
                                            foreach ($batchlist as $item): ?>
                                                 <?php if(isset($data['storage_id'])): ?>
                                                      <option value="<?= $item['batch_id'] ?>"  <?= $data['batch_id'] == $item['batch_id'] ? 'selected' : '' ?>><?= $item['batch_name'] ?></option>
                                                <?php else: ?>  
                                                     <option value="<?= $item['batch_id'] ?>"><?= $item['batch_name'] ?></option>
                                                <?php endif; ?>
                                             
                                            <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">输入单号 </label>
                                <div class="am-u-sm-9 am-u-end wd">
                                      <input autocomplete="off"  readonly="readonly"   type="text" class="tpl-form-input" id="keys" placeholder="请输入快递单号" value="">
                                      <div class="help-block">
                                        <small style="color:#ff6666;">如使用扫码枪，请将焦点置于框内</small>
                                </div>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
             <div class="am-scrollable-horizontal am-u-sm-12" style="background:#ffffff">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>包裹单号</th>
                                <th>仓库</th>
                                <th>操作时间</th>
                                <th>操作反馈</th>
                            </tr>
                            </thead>
                            <tbody id="body">
                           
                            </tbody>
                        </table>
                    </div>
        </div>            
    </div>
</div>
<div class="layer-tips-area"></div>
<script>
    function renderTable(datas){
        var tr = document.createElement('tr');
        var body = document.getElementById('body');
        var _td = '';
        var data = datas.data;
        tr.id = 'data_'+data.express_num;

        _td+="<td class='am-text-middle'>"+data.express_num+"</td>";

        if (!data.shop_name){
            data.shop_name = '';
        }
        _td+="<td class='am-text-middle'>"+data.shop_name+"</td>";
      
        _td+="<td class='am-text-middle'>"+data.opTime+"</td>";
        var opClass = 'err';
        if (data['success']){
            opClass = 'success';
        }
        _td+="<td class='am-text-middle "+opClass+"'>"+data.err+"</td>";
        tr.innerHTML = _td;
        body.appendChild(tr);
    }
    
    // 渲染 提示窗口
    function renderTips(content){
        var layerTipsArea = document.getElementsByClassName('layer-tips-area')[0];
        var tipLayers = document.getElementsByClassName('tip-layer');
        var top = tipLayers.length * 100 +30;
        var tipLayer = document.createElement('div');
        tipLayer.className = 'tip-layer';
        tipLayer.style.top = top+'px';
        var opTitle = '包裹出库提示';
        tipLayer.innerHTML = '<div class="tip-layer-title">'+opTitle+'</div><div class="tip-layer-content"><p>'+content+'</p></div>';
        layerTipsArea.appendChild(tipLayer);
        setTimeout(function(){
             tipLayer.style.right = 10+'px';
        },10);
        setTimeout(function(){
            tipLayer.style.opacity = 0;
            tipLayer.style.top = tipLayer.offsetTop - 50+'px'; 
            setTimeout(function(){
                layerTipsArea.removeChild(tipLayer);
            },400);
        },3000);
    }
    
    
    var code = "";
    var lastTime, nextTime;
    var lastCode, nextCode;
    document.onkeypress = function (e) {
        if (window.event) { // IE
            nextCode = e.keyCode;
        } else if (e.which) { // Netscape/Firefox/Opera
            nextCode = e.which;
        }
        if (nextCode === 13) {
            if (code.length < 3) return; // 手动输入的时间不会让code的长度大于2，所以这里只会对扫码枪有
            // 判断 是否 已经 操作过改包裹
            if (document.getElementById('data_'+code)){
                renderTips('本次操作为重复操作');
                return;
            }
            // 给搜索框赋值并搜索
            $("#keys").attr("value", code);
            var batch_id = $("#batch_id").val(); // 获取到扫码枪输入的内容，做别的操作
            // 得到扫码枪的值,请求数据库,返回结果
            $.ajax({
                type: "POST",
                url: "<?= url('store/package.index/scanoutshop')?>",
                data: {barcode: code,batch_id:batch_id,op:$("input[name='op']:checked").val()},
                dataType: "json",
                success: function (res) {
                    renderTips(res.msg);
                    if (res.code == 1){
                        var data = res.data;
                        renderTable(data);
                    }else {
                        console.log('111')
                    }
                },error: function (error) {
                    console.log(11)
                }
            });

            code = '';
            lastCode = '';
            lastTime = '';
            return;
        }
        nextTime = new Date().getTime();
        if (!lastTime && !lastCode) {
            code += e.key;
        }

        if (lastCode && lastTime && nextTime - lastTime > 30) { // 当扫码前有keypress事件时,防止首字缺失
            code = e.key;
        } else if (lastCode && lastTime) {
            code += e.key;
        }
        lastCode = nextCode;
        lastTime = nextTime;
    }
</script>
