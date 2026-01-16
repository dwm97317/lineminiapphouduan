<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form id="my-form" class="am-form tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head am-cf">
                                <div class="widget-title am-fl">编辑服务项目</div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">增值服务名称 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[name]"
                                           value="<?= $model['name']?>" required>
                                </div>
                            </div>
                            
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">增值服务说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[desc]"
                                           value="<?= $model['desc']?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">增值服务类型</label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[type]" value="10" data-am-ucheck <?= $model['type'] == 10 ? 'checked' : '' ?>>
                                        重量模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[type]"  value="20" <?= $model['type'] == 20 ? 'checked' : '' ?> data-am-ucheck>
                                        长度模式
                                    </label>
                                </div>
                            </div>
                             <!--区间计费规则--->
                             <div class="am-form-up" id="area_mode">
                                     <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">区间计费规则</label>
                                    <div class="am-u-sm-9 am-u-end">
                                      <table class="am-table">
                                        <thead>
                                            <tr>
                                                <th>开始</th>
                                                <th>结束</th>
                                                <th>费用</th>
                                            </tr>
                                        </thead>
                                        <tbody class="area_mode">
                                            <tr>
                                                <?php if(isset($model['rule'])) : foreach($model['rule'] as $item4) : ?>
                                                <tr>
                                                    <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始值" value="<?= $item4['weight'][0]; ?>"></td>
                                                    <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束值" value="<?= $item4['weight'][1]??''; ?>"></td>
                                                    <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需费用" value="<?= $item4['weight_price'] ; ?>"></td>
                                                    <td onclick="addfreeRulearea(this)">新增</td>
                                                    <td onclick="freeRuleDelarea(this)">删除</td>
                                                </tr>
                                                 <?php endforeach; endif; ?>
                                            </tr>
                                        </tbody>
                                       </table>
                             </div></div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">是否启用</label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="0" data-am-ucheck <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]"  value="1" <?= $model['status'] == 1 ? 'checked' : '' ?> data-am-ucheck>
                                        禁用
                                    </label>
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
<script>
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
    
    $(function () {
        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
