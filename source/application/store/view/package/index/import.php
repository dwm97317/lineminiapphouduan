<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<style>
    .wd {
        width: 200px;
    }
    .data-file { width: 120px; height:60px;}
    .data-file-select { display:inline-block; position:absolute; }
    .process-bar { width: 300px; height:40px; background:#eee;  position:relative;}
    .process-bar p { width:10%; height:40px;  transition: width 0.5s; }
    .process-bar span { position:absolute; left:50%; margin-left:-18%; top:18%; width:100%; height:40px; font-size:14px;}
</style>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form action="<?= url('store/package.index/save')?>" class="am-form tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">导入文件</div>&nbsp;&nbsp; 
                                <a href="/assets/store/小思集运批量导入模板.xlsx">批量导入模板（点击下载）</a>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">选择文件</label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="widget-become-goods am-form-file am-margin-top-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择文件
                                            <input type="file" class="data-file-select"/> 
                                        </button>
                                        <div class="user-list uploader-list am-cf">
                                        </div>
                                        <div class="am-block">
                                            <small>请上传excel文件！格式支持 <span style="color:red;">xls,xlsx</span></small>
                                        </div>
                                        <div class="process-bar" style="display:none;">
                                             <p class="am-btn-secondary" id="bar" style="width:0%"></p>
                                             <span id="bar_text">正在处理中 <b>0%</b></span>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </fieldset>
                    </div>
                </form>
                 <div class="am-u-sm-12" style="display:none;">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr id="tableTr">
                             
                            </tr>
                            </thead>
                            <tbody id="tbody">
                                    
                            </tbody>
                        </table>
                    </div>
            </div>
            
        </div>
    </div>
</div>
<script src="https://lf6-cdn-tos.bytecdntp.com/cdn/expire-1-M/xlsx/0.16.9/xlsx.core.min.js"></script>
<script src="https://lib.baomitu.com/xlsx/latest/xlsx.core.min.js"></script>
<script>

function randomNum(minNum,maxNum){ 
    switch(arguments.length){ 
        case 1: 
            return parseInt(Math.random()*minNum+1,10); 
        break; 
        case 2: 
            return parseInt(Math.random()*(maxNum-minNum+1)+minNum,10); 
        break; 
            default: 
                return 0; 
            break; 
    } 
} 
var importExcel = {
    _map:{
          
          '快递单号':'express_num',
          '物流名称':'express_name',
          '用户ID':'member_id',
          '唛头':'usermark',
          '仓库名称':'storage_name',
          '包裹重量':'weight',
          '长':'length',
          '宽':'width',
          '高':'height',
          '体积':'volume',
          '物品名称':'class_name',
          '物品数量':'product_num',
    },
    num:0,
    pro_num:0,
    onSelect:function(){
      var fileSelect = document.getElementsByClassName('data-file-select')[0];
        
      fileSelect.onchange = function(e){
          var file = this.value;
          var files = e.target.files;
          var ext = importExcel.getFileExt(file);
          if (!importExcel.isAllow(ext)){
              alert('请上传excel文件');
              return;
          }
          var tbody = document.getElementById('tbody');
          tbody.innerHTML = '';
          importExcel.readFile(files);
      }
    },
    readFile:function(_file){
        var fileReader = new FileReader();
        fileReader.onload = function(ev) {
            try {
                var data = ev.target.result,
           
                    workbook = XLSX.read(data, {
                        type: 'binary'
                    }), // 以二进制流方式读取得到整份excel表格对象
                    persons = []; // 存储获取到的数据
            } catch (e) {
                console.log('文件类型不正确');
                return;
            }
            // 表格的表格范围，可用于判断表头是否数量是否正确
            var fromTo = '';
            // 遍历每张表读取
            for (var sheet in workbook.Sheets) {
                if (workbook.Sheets.hasOwnProperty(sheet)) {
                    fromTo = workbook.Sheets[sheet]['!ref'];
                    console.log(fromTo,'77777777');
                    persons = persons.concat(XLSX.utils.sheet_to_json(workbook.Sheets[sheet]));
                    // break; // 如果只取第一张表，就取消注释这行
                }
            }
            $(".process-bar").show();
               
            importExcel.num = importExcel.getJsonLength(persons);
            importExcel.uploadPre(persons);
        };
        // 以二进制方式打开文件
        fileReader.readAsBinaryString(_file[0]); 
    },
    appendTableTr:function(){
        var _map = this._map;
        var tr_temp = '';
        for (var _k in _map){
            tr_temp+='<th>'+_k+'</th>';
        }
        tr_temp+='<th>上传结果</th>';
        $('#tableTr').html(tr_temp);
    },
    getJsonLength:function(data){
       $l = 0;
       for (var _k in data){
           $l++;
       }
       return $l;
    },
    getMapData:function(column){
       var _map = this._map;
       var data = {};
       for (var _l in column){
           data[_map[_l]] = column[_l];
       }
       data['id'] = column['id'];
       return data;
    },
    tableTbodyAppend:function(_temp){
        var _temp_tr = document.createElement('tr');
        var tbody = document.getElementById('tbody');
        _temp_tr.id = _temp['id'];
        var map = this._map;
        var _temp_td = '';
        for (var _k in map){
            var c = _temp[_k]!=undefined?_temp[_k]:"空的";
            _temp_td += "<td>"+c+"</td>";
        }
        _temp_td += "<td style='color:green;'>正在导入^~^</td>"
        _temp_tr.innerHTML = _temp_td;
        tbody.appendChild(_temp_tr);
    },
    uploadPre:function(excelData){
        if (!excelData[0]){
             console.log(excelData + 'excel 为空的');
             return;
        }
        $('.am-u-sm-12').show();
        this.appendTableTr();
        for (var ik in excelData){
            excelData[ik]['id'] = new Date().getTime()+randomNum(000,999); 
            var _temp = this.getMapData(excelData[ik]);
            this.tableTbodyAppend(excelData[ik]);
            this.upload(_temp);
        }
    },
    upload:function(temp){
        $.ajax({
          type:"POST",
          url:"<?= url('store/package.index/importDo')?>",
          dataType:"JSON",
          data:temp,
          success:function(res){
             importExcel.pro_num+=1;
             importExcel.setProcess();
             if (res['code']==0){
                 importExcel.outPuterr(res.data);
             }else{
                importExcel.outPutSuccess(res.data);
             }
          }
        }); 
    },
    setProcess:function(){
       var scale = importExcel.pro_num/importExcel.num; 
       var processbar = document.getElementById('bar');
       var processbarText = document.getElementById('bar_text');
       processbar.style.width = scale *100 +"%";
       if (scale>0.5){
           processbarText.style.color = '#fff';
       }
       processbarText.innerHTML = "正在处理中:<b>"+scale *100 +"%</b>";
       if (scale==1){
           processbarText.innerHTML = '处理完成';
           setTimeout(()=>{
               $('.process-bar').hide();
           },3000);
       }
    },
    outPuterr:function(res){
       var tr = document.getElementById(res.id);
       td = tr.getElementsByTagName('td');
       td[td.length-1].style.color = 'red';
       td[td.length-1].innerHTML = res.error;
    },
    outPutSuccess:function(res){
       var tr = document.getElementById(res.id);
       td = tr.getElementsByTagName('td');
       td[td.length-1].style.color = 'green';
       td[td.length-1].innerHTML = res.success;
    },
    isAllow:function(_ext){
      var reg = /xls|xlsx/;
      return reg.test(_ext);
    },
    getFileExt:function(file){
        return file.substring(file.lastIndexOf('.')+1); 
    },
    envInit:function(){
                      
    },
    init:function(){
        this.envInit(); // 环境检测
        this.onSelect();
    }
}
importExcel.init();
</script>
