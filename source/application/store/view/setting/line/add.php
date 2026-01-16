<style>
   .am-form-up { display: none;}
   .am-form-title { font-size: 13px; color:#666; cursor: pointer; }
   .am-form-up-item { width: 100%; height: auto;} 
   .am-form-item-del { font-size: 13px; color: #ff6666; cursor: pointer;}
</style>
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
<?php $weiStatus=[10=>'g',20=>'kg',30=>'bl',40=>'CBM'] ?>
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl"><?= isset($model) ? '编辑' : '新增' ?>线路地址</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[name]" value="" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">线路图片 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-file">
                                        <button type="button"
                                                class="upload-file am-btn am-btn-secondary am-radius">
                                            <i class="am-icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list am-cf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_type]" onclick="switchLineMode(this)"   value="0" data-am-ucheck
                                               checked>
                                        按重量
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_type]" onclick="switchLineMode(this)"   value="1" data-am-ucheck>
                                         按体积
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路重量单位 </label>
                                <div class="am-u-sm-9 am-u-end">
                            
                                    <label class="am-radio-inline line_type_weight">
                                        <input type="radio" name="line[line_type_unit]"  value="10" data-am-ucheck <?= $set['weight_mode']['mode']==10?'checked':'' ?>>
                                        g
                                    </label>
                                    <label class="am-radio-inline line_type_weight">
                                        <input type="radio" name="line[line_type_unit]"  value="20" data-am-ucheck <?= $set['weight_mode']['mode']==20?'checked':'' ?>>
                                         kg
                                    </label>
                                    <label class="am-radio-inline line_type_weight">
                                        <input type="radio" name="line[line_type_unit]"  value="30" data-am-ucheck <?= $set['weight_mode']['mode']==30?'checked':'' ?>>
                                         lb
                                    </label>
                                 
                                    <label class="am-radio-inline line_type_vol" style="display:none;">
                                        <input type="radio" name="line[line_type_unit]"  value="40" data-am-ucheck>
                                         CBM
                                    </label>
                            
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重计算模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  value="5000" data-am-ucheck
                                               checked>
                                        5000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"   value="6000" data-am-ucheck>
                                         6000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"   value="7000" data-am-ucheck>
                                         7000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"   value="8000" data-am-ucheck>
                                         8000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"   value="9000" data-am-ucheck>
                                         9000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  value="10000" data-am-ucheck>
                                        10000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  value="27000" data-am-ucheck>
                                        27000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  value="28316" data-am-ucheck>
                                        28316计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  value="1000000" data-am-ucheck>
                                        1000000(百万)
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"   value="166" data-am-ucheck>
                                         166计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"   value="139" data-am-ucheck>
                                         139计费
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 计费模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" value="1" data-am-ucheck
                                               checked>
                                        阶梯计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" value="2" data-am-ucheck>
                                        首/续重模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" value="3" data-am-ucheck>
                                        范围区间计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" value="4" data-am-ucheck>
                                        重量区间计费
                                    </label>
                                    <div class="help-block"><small>范围区间计费,举例说明:1-10kg,价格20元,是指不管是1kg还是10kg,总价格就是20元.重量区间计费,举例说明:1-10kg,价格20元,是指在1-10kg之间时,每kg费用20元,当重量为5kg时,总价格为5 * 20 = 100元</small></div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 计费规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <div class="am-form-up" id="step_mode" style='display: block'>
                                          <div class="am-form-title" style="height:40px; line-height:35px;">阶梯计费规则 <span style="color:#ff6600;" onclick="addfreeRule(this)">新增计费规则</span></div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>最大限重(<?= $set['weight_mode']['unit'] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="step_mode">
                                                    <tr>
                                                        <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-email-1" placeholder="输入起始重量"></td>
                                                        <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-email-1" placeholder="输入结束重量"></td>
                                                        <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-email-1" placeholder="输入所需价格"></td>
                                                        <td><input type="hidden" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量"></td>
                                                        <td onclick="freeRuleDel(this)">删除</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                         </div>
                                
                                 <!--续重计费模式--->
                                     <div class="am-form-up" id="format_mode">
                                          <div class="am-form-title" style="height:40px; line-height:35px;"> 首/续重模式</div>
                                          <div class="am-form-group" >
                                                <label class="am-u-lg-2 am-form-label"> 首重 (<?= $set['weight_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_weight]" value=""
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 首重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_price]" value=""
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重 (<?= $set['weight_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_weight]" value=""
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_price]" value=""
                                                           required>
                                                </div>
                                            </div>
                                     </div>
                                     
                                      <!--区间计费规则--->
                                      <div class="am-form-up" id="area_mode">
                                                  <div class="am-form-title" style="height:40px; line-height:35px;">区间计费规则 <span style="color:#ff6600;" onclick="addfreeRulearea(this)">新增计费规则</span></div>
                                                      <table class="am-table">
                                                        <thead>
                                                            <tr>
                                                                <th>最低限重(<?= $set['weight_mode']['unit'] ?>)</th>
                                                                <th>最大限重(<?= $set['weight_mode']['unit'] ?>)</th>
                                                                <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="area_mode">
                                                            <tr>
                                                                <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-email-1" placeholder="输入起始重量"></td>
                                                                <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-email-1" placeholder="输入结束重量"></td>
                                                                <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-email-1" placeholder="输入所需价格"></td>
                                                                <td><input type="hidden" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="1"></td>
                                                                <td onclick="freeRuleDelarea(this)">删除</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                 </div>
                                                 
                      
                                             
                                                <div class="am-form-up" id="area_mode_unit">
                                                  <div class="am-form-title" style="height:40px; line-height:35px;">重量计费规则 <span style="color:#ff6600;" onclick="addfreeRuleareaweightunit(this)">新增计费规则</span></div>
                                                      <table class="am-table">
                                                        <thead>
                                                            <tr>
                                                                <th>最低限重(<?= $set['weight_mode']['unit'] ?>)</th>
                                                                <th>最大限重(<?= $set['weight_mode']['unit'] ?>)</th>
                                                                <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                                 <th>计费单位重量(<?= $set['weight_mode']['unit'] ?>)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="area_mode_unit">
                                                            <tr>
                                                                <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-email-1" placeholder="输入起始重量"></td>
                                                                <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-email-1" placeholder="输入结束重量"></td>
                                                                <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-email-1" placeholder="输入所需价格"></td>
                                                                <td><input type="text" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量"></td>
                                                                <td onclick="freeRuleDelareaweightunit(this)">删除</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                 </div>
                                             
                                         </div>
                                    </div>
                            
                            
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 限制最少重量 (<?= $set['weight_mode']['unit'] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[weight_min]" value="10"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 限制最大重量 (<?= $set['weight_mode']['unit'] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[max_weight]" value="10"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 重量限制说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <textarea name="line[weight_limit]" id="" class="tpl_form_input" cols="30" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 运送时效 (天) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text" class="tpl-form-input" placeholder="请输入文字说明" name="line[limitationofdelivery]" value=""
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require "> 关税说明</label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text" min="0" class="tpl-form-input" name="line[tariff]" value=""
                                           required>
                                     <small>关税指:引进出口商品经过一国关境时，由政府所设置的海关向其引进出口商所征收的税收</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">
                                    增值服务
                                </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="selectize-tags-1" onclick="changeorder()" onchange="changeorder()" name="line[services_require]" multiple="" class="tag-gradient-success">
                                        <?php if (count($lineservice)>0): foreach ($lineservice as $key =>$item): ?>
                                            <option value="<?= $item['service_id'] ?>"><?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    
                                    <small>注：不需要增值服务可不选；</small>
                                </div>
                            </div>
                           <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 增值服务说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text"  class="tpl-form-input" name="line[service_route]" value=""
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 线路特点 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[special]" value="请输入线路特点描述：如速度快"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 物品限制 </label>
                                <div class="am-u-sm-9 am-u-end">
                                      <textarea name="line[goods_limit]" id="" class="tpl_form_input" cols="30" rows="5"></textarea>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 体积限制说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <textarea name="line[length_limit]" id="" class="tpl_form_input" cols="30" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="line[sort]" value="100"
                                           required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 国家支持 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="am-u-sm-9">
                                          <input name="line[countrys]" id="countrys_id" type="hidden" value="">
                                           <div class="support-list" id="support-list"></div>
                                     </div>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm support-add">编辑</button>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 类目支持 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="am-u-sm-9">
                                          <input name="line[categorys]" id="categorys_id" type="hidden" value="">
                                           <div class="categorys-list" id="categorys-list"></div>
                                     </div>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm categorys-add">编辑</button>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="1" data-am-ucheck
                                               checked>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="0" data-am-ucheck>
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">更多规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="line[line_content]"
                                              type="text/plain"></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                    <button type="submit" class="j-submit am-btn am-btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}
<link href="/web/static/css/selectize.default.css" rel="stylesheet">
<script src="/web/static/js/summernote-bs4.min.js"></script>
<script src="/web/static/js/selectize.min.js"></script>
<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script id="tpl-country-item" type="text/template">
    {{ each data as value }}
      <button type="button" data-id='{{value.id}}' class="am-btn am-btn-default am-btn-xs country">{{value.title}}</button>
    {{ /each }}
</script>
<script id="tpl-category-item" type="text/template">
    {{ each data as value }}
      <button type="button" data-id='{{value.id}}' class="am-btn am-btn-default am-btn-xs category">{{value.name}}</button>
    {{ /each }}
</script>
<script>
    // 富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: 375 + 15,
            initialFrameHeight: 600
        });
    // 选择国家
    $('.support-add').click(function () {
        var $countryList = $('.support-list');
        $.selectData({
            title: '选择国家',
            uri: 'Country/countryList',
            dataIndex: 'id',
            done: function (list) {
                var data = {};
                var select_ids = [];
                data['data'] = list;
                console.log(data['data']);
                for (var i=0; i<data['data'].length; i++){
                      select_ids.push(data['data'][i].id);
                    }
                $('#countrys_id').val(select_ids.join(','));
                $countryList.html(template('tpl-country-item', data));
            }
        });
    });
    
    // 选择类目
    $('.categorys-add').click(function () {
        var $countryList = $('.categorys-list');
        $.selectData({
            title: '选择类目',
            uri: 'Category/categoryList',
            dataIndex: 'id',
            done: function (list) {
                var data = {};
                var select_ids = [];
                data['data'] = list;
                console.log(data['data']);
                for (var i=0; i<data['data'].length; i++){
                      select_ids.push(data['data'][i].id);
                    }
                $('#categorys_id').val(select_ids.join(','));
                $countryList.html(template('tpl-category-item', data));
            }
        });
    });
    
    $(function () {
        // 选择图片
        $('.upload-file').selectImages({
            name: 'line[image_id]'
        });
        
        $('#selectize-tags-1').selectize({
    	    delimiter: ',',
    	    persist: false,
    	    create: function(input) {
    	        return {
    	            value: input,
    	            text: input
    	        }
    	    }
	    });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();
    });
    
    function changeorder(){
        console.log($('#selectize-tags-1')[0]);
        $('#orderno').val($('#selectize-tags-1')[0].selectize.items);
    }

    // 切换计费模式
    function switchMode(_this){
        var _mode = _this.value;
         $('.am-form-up').css('display','none');
         console.log(_mode)
        if(_mode==1){
            var freeMode = '#step_mode';
        }
        if(_mode==2){
            var freeMode = '#format_mode';
        }
        if(_mode==3){
            var freeMode = '#area_mode';
        }
        if(_mode==4){
            var freeMode = '#area_mode_unit';
        }
        $(freeMode).css('display','block');
    }
    
    


    
    function switchLineMode(_this){
        var _mode = _this.value;
        $('.line_type_weight').css('display','none');
        $('.line_type_vol').css('display','none');
        if(_mode==0){
            var freeMode = '.line_type_weight';
        }
        if(_mode==1){
            var freeMode = '.line_type_vol';
        }
        $(freeMode).css('display','inline-block');
    }

    function addfreeRule(){
        var amformItem = document.getElementsByClassName('step_mode')[0];
        var Item = document.createElement('tr');
      
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-email-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-email-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-email-1"placeholder="输入所需价格"></td></td><td class="" onclick="freeRuleDel(this)">删除</td>';
        Item.innerHTML = _html;
        amformItem.appendChild(Item);
    }

    // 删除
    function freeRuleDel(_this){
       var amformItem = document.getElementsByClassName('step_mode')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
     function addfreeRulearea(){
        var amformItem = document.getElementsByClassName('area_mode')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input type="hidden" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="1"><td class="" onclick="freeRuleDelarea(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }

    // 删除
    function freeRuleDelarea(_this){
      var amformItem = document.getElementsByClassName('area_mode')[0];
      var parent = _this.parentNode;
      amformItem.removeChild(parent);
    }
    
    function addfreeRuleareaweight(){
        var amformItem = document.getElementsByClassName('areaweight_mode')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td class="" onclick="freeRuleDelareaweight(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }

    // 删除
    function freeRuleDelareaweight(_this){
       var amformItem = document.getElementsByClassName('areaweight_mode')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
    function addfreeRuleareaweightunit(){
        var amformItem = document.getElementsByClassName('area_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input type="text" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量"></td><td class="" onclick="freeRuleDelareaweightunit(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    // 删除
    function freeRuleDelareaweightunit(_this){
       var amformItem = document.getElementsByClassName('area_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
   
    // $(function () {
        
    //     /**
    //      * 表单验证提交
    //      * @type {*}
    //      */
    //     $('#my-form').superForm();

    // });
</script>
