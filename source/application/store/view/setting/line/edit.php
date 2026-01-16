<style>
   .am-form-up { display: none;}
   .am-form-title { font-size: 13px; color:#666; cursor: pointer; }
   .am-form-up-item { width: 100%; height: auto;} 
   .am-form-item-del { font-size: 13px; color: #ff6666; cursor: pointer;}
   .support-title { font-size:13px; height:30px; line-height:30px; color:#ccc;}
   .support-list { display:block;}
</style> 
<?php $weiStatus=[10=>'g',20=>'kg',30=>'bl',40=>'CBM'] ?>
<link rel="stylesheet" href="assets/common/plugins/umeditor/themes/default/css/umeditor.css">
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
                                    <input type="text" class="tpl-form-input" name="line[name]" value="<?= $model['name'] ?>" required>
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
                                                <div class="file-item">
                                                    <a href="<?= $model['image']['file_path'] ?>"
                                                       title="点击查看大图" target="_blank">
                                                        <img src="<?= $model['image']['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="nav[image_id]"
                                                           value="<?= $model['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路模式 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_type]" onclick="switchLineMode(this)"  <?= $model['line_type'] == 0 ? 'checked' : '' ?> value="0" data-am-ucheck
                                               checked>
                                        按重量
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[line_type]" onclick="switchLineMode(this)"  <?= $model['line_type'] == 1 ? 'checked' : '' ?> value="1" data-am-ucheck>
                                         按体积
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 线路重量单位 </label>
                                <div class="am-u-sm-9 am-u-end">
                            
                                    <label class="am-radio-inline line_type_weight" <?= $model['line_type'] == 0 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 10 ? 'checked' : '' ?> value="10" data-am-ucheck>
                                        g
                                    </label>
                                    <label class="am-radio-inline line_type_weight" <?= $model['line_type'] == 0 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 20 ? 'checked' : '' ?> value="20" data-am-ucheck>
                                         kg
                                    </label>
                                    <label class="am-radio-inline line_type_weight" <?= $model['line_type'] == 0 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 30 ? 'checked' : '' ?> value="30" data-am-ucheck>
                                         lb
                                    </label>
                                 
                                    <label class="am-radio-inline line_type_vol" <?= $model['line_type'] == 1 ? 'style="display:inline-block;"' : 'style="display:none;"' ?>>
                                        <input type="radio" name="line[line_type_unit]"  <?= $model['line_type_unit'] == 40 ? 'checked' : '' ?> value="40" data-am-ucheck>
                                         CBM
                                    </label>
                            
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重泡货比 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 5000 ? 'checked' : '' ?> value="5000" data-am-ucheck
                                               checked>
                                        5000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 6000 ? 'checked' : '' ?> value="6000" data-am-ucheck>
                                         6000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 7000 ? 'checked' : '' ?> value="7000" data-am-ucheck>
                                         7000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 8000 ? 'checked' : '' ?> value="8000" data-am-ucheck>
                                         8000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 9000 ? 'checked' : '' ?> value="9000" data-am-ucheck>
                                         9000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 10000 ? 'checked' : '' ?> value="10000" data-am-ucheck>
                                        10000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 27000 ? 'checked' : '' ?> value="27000" data-am-ucheck>
                                        27000计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 28316 ? 'checked' : '' ?> value="28316" data-am-ucheck>
                                        28316计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 1000000 ? 'checked' : '' ?> value="1000000" data-am-ucheck>
                                        1000000(百万)
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 166 ? 'checked' : '' ?> value="166" data-am-ucheck>
                                         166计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight]"  <?= $model['volumeweight'] == 139 ? 'checked' : '' ?> value="139" data-am-ucheck>
                                         139计费
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 体积重计算模式 </label>
                                <div class="am-u-sm-9 am-u-end" >
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight_type]"  <?= $model['volumeweight_type'] == 10 ? 'checked' : '' ?> value="10" data-am-ucheck
                                               checked>
                                        长*宽*高/体积重泡货比
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[volumeweight_type]"   <?= $model['volumeweight_type'] == 20 ? 'checked' : '' ?> value="20" data-am-ucheck>
                                        实重+（长*宽*高/6000-实重）*70%(选择此模式请设置下方的百分比)
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 泡货比 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" class="tpl-form-input" name="line[bubble_weight]" value="<?= $model['bubble_weight'] ?>" required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 计费模式 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]"  value="1"  onclick="switchMode(this)" <?= $model['free_mode'] == 1 ? 'checked' : '' ?> data-am-ucheck
                                               checked>
                                        阶梯计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[free_mode]" onclick="switchMode(this)" <?= $model['free_mode'] == 2 ? 'checked' : '' ?> value="2" data-am-ucheck>
                                        首/续重模式
                                    </label>
                                    <label class="am-radio-inline">
                                        <input value="3" type="radio" name="line[free_mode]" onclick="switchMode(this)" <?= $model['free_mode'] == 3 ? 'checked' : '' ?> data-am-ucheck>
                                        范围区间计费
                                    </label>
                                    <label class="am-radio-inline">
                                        <input value="4" type="radio" name="line[free_mode]" onclick="switchMode(this)" <?= $model['free_mode'] == 4 ? 'checked' : '' ?> data-am-ucheck>
                                        重量区间计费
                                    </label>
                                    <div class="help-block"><small>范围区间计费,举例说明:1-10kg,价格20元,是指不管是1kg还是10kg,总价格就是20元.重量区间计费,举例说明:1-10kg,价格20元,是指在1-10kg之间时,每kg费用20元,当重量为5kg时,总价格为5 * 20 = 100元</small></div>
                                </div>
                            </div>

                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 计费规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <!--阶梯计费模式--->
                                     <div class="am-form-up" id="step_mode"  <?= $model['free_mode'] == 1 ? 'style="display: block;"' : '' ?>>
                                             <div class="am-form-title" style="height:40px; line-height:35px;">阶梯计费规则 
                                                  <span style="color:#ff6600;" onclick="addfreeRule(this)">新增计费规则</span>
                                             </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>最大限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="step_mode">
                                                    <tr>
                                                       <?php if (isset($model['free_rule']) && $model['free_mode'] == 1): ?> 
                                                        <?php foreach($model['free_rule'] as $item2): ?>
                                                        <tr>
                                                        <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-email-1" placeholder="输入起始重量" value="<?= $item2['weight'][0]; ?>"></td>
                                                        <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-email-1" placeholder="输入结束重量" value="<?= $item2['weight'][1]??''; ?>"></td>
                                                        <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-email-1" placeholder="输入所需价格" value="<?= $item2['weight_price'] ; ?>"></td>
                                                        <td onclick="freeRuleDel(this)">删除</td>
                                                    </tr>
                                                         <?php endforeach;?>
                                                       <?php endif; ?>
                                                    </tr>
                                                </tbody>
                                               </table>
                                     </div>
                                      <!--首/续重计费模式--->
                                     <div class="am-form-up" id="format_mode" <?= $model['free_mode'] == 2 ? 'style="display: block;"' : '' ?>>
                                          <div class="am-form-title" style="height:40px; line-height:35px;"> 首/续重模式</div>
                                          
                                          <?php if (isset($model['free_rule'][0]['first_weight'])): ?> 
                                          <?php foreach($model['free_rule'] as $item3): ?>
                                          <div class="am-form-group" >
                                                <label class="am-u-lg-2 am-form-label"> 首重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_weight]" value="<?= isset($item3['first_weight'])?$item3['first_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 首重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_price]" value="<?= isset($item3['first_price'])?$item3['first_price']:'' ; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_weight]" value="<?= isset($item3['first_price'])?$item3['next_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_price]" value="<?= isset($item3['first_price'])?$item3['next_price']:''; ?>"
                                                           required>
                                                </div>
                                            </div>
                                          <?php endforeach;?>
                                          <?php else : ?>
                                           <div class="am-form-group" >
                                                <label class="am-u-lg-2 am-form-label"> 首重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_weight]" value="<?= isset($item3['first_weight'])?$item3['first_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 首重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[first_price]" value="<?= isset($item3['first_price'])?$item3['first_price']:'' ; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_weight]" value="<?= isset($item3['first_price'])?$item3['next_weight']:''; ?>"
                                                           required>
                                                </div>
                                                <label class="am-u-lg-2 am-form-label"> 续重费用 (<?= $set['price_mode']['unit'] ?>) </label>
                                                <div class="am-u-sm-9 am-u-end">
                                                     <input type="number" min="0" class="tpl-form-input" name="line[next_price]" value="<?= isset($item3['first_price'])?$item3['next_price']:''; ?>"
                                                           required>
                                                </div>
                                                
                                            </div>
                                        <?php endif; ?>
                                          
                                     </div>
                                     
                                     <!--区间计费规则--->
                                     <div class="am-form-up" id="area_mode"  <?= in_array($model['free_mode'],[3]) ? 'style="display: block;"' : '' ?>>
                                             <div class="am-form-title" style="height:40px; line-height:35px;">范围区间计费 
                                                  <span style="color:#ff6600;" onclick="addfreeRulearea(this)">新增计费规则</span>
                                             </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>最大限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="area_mode">
                                                    <tr>
                                                       <?php if (isset($model['free_rule']) && in_array($model['free_mode'],[3])): ?> 
                                                        <?php foreach($model['free_rule'] as $item4): ?>
                                                        <tr>
                                                        <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item4['weight'][0]; ?>"></td>
                                                        <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item4['weight'][1]??''; ?>"></td>
                                                        <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需价格" value="<?= $item4['weight_price'] ; ?>"></td>
                                                        <td><input type="hidden" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="1"></td>
                                                        <td onclick="freeRuleDelarea(this)">删除</td>
                                                    </tr>
                                                         <?php endforeach;?>
                                                       <?php endif; ?>
                                                    </tr>
                                                </tbody>
                                               </table>
                                     </div>
                                     
                                       <!--区间计费规则--->
                                     <div class="am-form-up" id="area_mode_unit"  <?= in_array($model['free_mode'],[4]) ? 'style="display: block;"' : '' ?>>
                                             <div class="am-form-title" style="height:40px; line-height:35px;">重量区间计费 
                                                  <span style="color:#ff6600;" onclick="addfreeRuleareaunit(this)">新增计费规则</span>
                                             </div>
                                              <table class="am-table">
                                                <thead>
                                                    <tr>
                                                        <th>最低限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>最大限重(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                        <th>价格(<?= $set['price_mode']['unit'] ?>)</th>
                                                        <th>计费单位重量(<?= $weiStatus[$model['line_type_unit']] ?>)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="area_mode_unit">
                                                    <tr>
                                                       <?php if (isset($model['free_rule']) && in_array($model['free_mode'],[4])): ?> 
                                                        <?php foreach($model['free_rule'] as $item4): ?>
                                                        <tr>
                                                        <td><input type="text" name="line[weight_start][]" class="" id="doc-ipt-start-1" placeholder="输入起始重量" value="<?= $item4['weight'][0]; ?>"></td>
                                                        <td><input type="text" name="line[weight_max][]" class="" id="doc-ipt-end-1" placeholder="输入结束重量" value="<?= $item4['weight'][1]??''; ?>"></td>
                                                        <td><input type="text" name="line[weight_price][]" class="" id="doc-ipt-price-1" placeholder="输入所需价格" value="<?= $item4['weight_price'] ; ?>"></td>
                                                        <td><input type="text" name="line[weight_unit][]" class="" id="doc-ipt-unit-1" placeholder="输入计费单位重量" value="<?= $item4['weight_unit'] ; ?>"></td>
                                                        <td onclick="freeRuleDelareaunit(this)">删除</td>
                                                    </tr>
                                                         <?php endforeach;?>
                                                       <?php endif; ?>
                                                    </tr>
                                                </tbody>
                                               </table>
                                     </div>
                                    
                                </div>
                            </div>
                            
                           
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 限制最少重量 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[weight_min]" value="<?= $model['weight_min']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">限制最大重量 (<?= $weiStatus[$model['line_type_unit']] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="number" min="0" class="tpl-form-input" name="line[max_weight]" value="<?= $model['max_weight']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 重量限制说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <textarea name="line[weight_limit]" id="" class="tpl_form_input" cols="30" rows="5"><?= $model['weight_limit'];?></textarea>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 运送时效 (<?= $set['price_mode']['unit'] ?>) </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text" min="0" class="tpl-form-input" name="line[limitationofdelivery]" value="<?= $model['limitationofdelivery']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 关税说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text"  class="tpl-form-input" name="line[tariff]" value="<?= $model['tariff']; ?>"
                                           required>
                                </div>
                            </div>
                           
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">
                                    增值服务
                                </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <select id="selectize-tags-1" onclick="changeorder()" onchange="changeorder()" name="line[services_require]" multiple="" class="tag-gradient-success">
                                        <?php if (count($lineservice)>0): foreach ($lineservice as $key =>$item): ?>
                                            <option value="<?= $item['service_id'] ?>" <?= in_array($item['service_id'],explode(',',$model['services_require']))?"selected":'' ?>><?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <input id="orderno" autocomplete="off" type="hidden" name="line[services_require]"  value="<?= $model['services_require']?>">
                                    <small>注：不需要增值服务可不选；</small>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 增值服务说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <input type="text"  class="tpl-form-input" name="line[service_route]" value="<?= $model['service_route']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 线路特点 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="text" min="0" class="tpl-form-input" name="line[line_special]" value="<?= $model['line_special'];?>"
                                           >
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 物品限制 </label>
                                <div class="am-u-sm-9 am-u-end">
                                      <textarea name="line[goods_limit]" id="" class="tpl_form_input" cols="30" rows="5"><?= $model['goods_limit'];?></textarea>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 体积限制说明 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <textarea name="line[length_limit]" id="" class="tpl_form_input" cols="30" rows="5"><?= $model['length_limit'];?></textarea>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 排序 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <input type="number" min="0" class="tpl-form-input" name="line[sort]" value="<?= $model['sort'];?>"
                                           required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 国家支持 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="am-u-sm-9">
                                          <div class="support-title">共支持 <span><?= count($model['country']) ;?></span> 个</div>
                                          <input name="line[countrys]" id="countrys_id" type="hidden" value="<?= $model['countrys'] ;?>">
                                          <div class="support-list" id="support-list">
                                              <?php foreach($model['country'] as $v):?>
                                                  <button type="button" data-id='<?= $v['id']; ?>' class="am-btn am-btn-default am-btn-xs country"><?= $v['title']; ?></button>
                                              <?php endforeach; ?>
                                          </div>
                                     </div>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm support-add">重新选择</button>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm support-app">追加国家</button>
                                </div>
                            </div>
                            
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label"> 类目支持 </label>
                                <div class="am-u-sm-9 am-u-end">
                                     <div class="am-u-sm-9">
                                          <div class="category-title">共支持 <span><?= count($model['category']) ;?></span> 个</div>
                                          <input name="line[categorys]" id="category_id" type="hidden" value="<?= $model['categorys'] ;?>">
                                          <div class="category-list" id="category-list">
                                              <?php foreach($model['category'] as $v):?>
                                                  <button type="button" data-id='<?= $v['category_id']; ?>' class="am-btn am-btn-default am-btn-xs category"><?= $v['name']; ?></button>
                                              <?php endforeach; ?>
                                          </div>
                                     </div>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm category-add">重新类目</button>
                                     <button type="button" class="am-btn am-btn-primary am-btn-sm category-app">追加类目</button>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 状态 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="1" data-am-ucheck
                                                <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[status]" value="0" data-am-ucheck
                                         <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require"> 推荐至首页 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[is_recommend]" value="1" data-am-ucheck
                                                <?= $model['is_recommend'] == 1 ? 'checked' : '' ?>>
                                        是
                                    </label>
                                    <label class="am-radio-inline">
                                        <input type="radio" name="line[is_recommend]" value="2" data-am-ucheck
                                         <?= $model['is_recommend'] == 2 ? 'checked' : '' ?>>
                                        否
                                    </label>
                                </div>
                            </div>
                            <div class="am-form-group am-padding-top">
                                <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">更多规则 </label>
                                <div class="am-u-sm-9 am-u-end">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="line[line_content]"
                                              type="text/plain"><?= $model['line_content'] ?></textarea>
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

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}
<link href="/web/static/css/selectize.default.css" rel="stylesheet">
<script src="/web/static/js/summernote-bs4.min.js"></script>
<script src="/web/static/js/selectize.min.js"></script>
<script src="assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="assets/common/plugins/umeditor/umeditor.min.js"></script>
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
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    function changeorder(){
        console.log($('#selectize-tags-1')[0]);
        $('#orderno').val($('#selectize-tags-1')[0].selectize.items);
    }
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
    $('.category-add').click(function () {
        var $countryList = $('.category-list');
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
                $('#category_id').val(select_ids.join(','));
                $countryList.html(template('tpl-category-item', data));
            }
        });
    });
    
     // 追加国家
    $('.support-app').click(function () {
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
                if($('#countrys_id').val()){
                $('#countrys_id').val($('#countrys_id').val()+","+select_ids.join(','));}else{
                     $('#countrys_id').val(select_ids.join(','));
                }
                $countryList.append(template('tpl-country-item', data));
            }
        });
    });
    
     // 追加类目
    $('.category-app').click(function () {
        var $categoryList = $('.category-list');
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
                if($('#category_id').val()){
                $('#category_id').val($('#category_id').val()+","+select_ids.join(','));}else{
                     $('#category_id').val(select_ids.join(','));
                }
                $categoryList.append(template('tpl-category-item', data));
            }
        });
    });

    // 富文本编辑器
    UM.getEditor('container', {
        initialFrameWidth: 375 + 15,
        initialFrameHeight: 600
    });
    // 切换计费模式
    function switchMode(_this){
        var _mode = _this.value;
        $('.am-form-up').css('display','none');
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
      
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-email-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-email-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-email-1"placeholder="输入所需价格"></td><td class="" onclick="freeRuleDel(this)">删除</td>';
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
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input value="1" type="hidden"name="line[weight_unit][]" class=""id="doc-ipt-unit-1" placeholder="输入单位重量(如：0.5或1)"></td><td class="" onclick="freeRuleDelarea(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }
    
    function addfreeRuleareaunit(){
        var amformItem = document.getElementsByClassName('area_mode_unit')[0];
            console.log(amformItem)
        var Item1 = document.createElement('tr');
        
        var _html = '<td><input type="text"name="line[weight_start][]"class=""id="doc-ipt-start-1"placeholder="输入起始重量"></td><td><input type="text"name="line[weight_max][]"class=""id="doc-ipt-end-1"placeholder="输入结束重量"></td><td><input type="text"name="line[weight_price][]"class=""id="doc-ipt-price-1"placeholder="输入所需价格"></td><td><input type="text"name="line[weight_unit][]"class=""id="doc-ipt-unit-1"placeholder="输入单位重量(如：0.5或1)"></td><td class="" onclick="freeRuleDelareaunit(this)">删除</td>';
        Item1.innerHTML = _html;
    
        amformItem.appendChild(Item1);
    }

    // 删除
    function freeRuleDelarea(_this){
       var amformItem = document.getElementsByClassName('area_mode')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }
    
    // 删除
    function freeRuleDelareaunit(_this){
       var amformItem = document.getElementsByClassName('area_mode_unit')[0];
       var parent = _this.parentNode;
       amformItem.removeChild(parent);
    }

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
    })
</script>
<script>
    $(function(){
       checker = {
          num:0, 
          check:[],
          init:function(){
              this.check = document.getElementById('body').getElementsByTagName('input');
              this.num = this.check.length;
              this.bindEvent();
          },
          bindEvent:function(){
              var that = this;
              for(var i=0; i< this.check.length; i++){
                  this.check[i].onclick = function(){
                       var _check = that.isFullCheck();
                       if (_check){
                           document.getElementById('checkAll').checked = 'checked';
                       }else{
                           document.getElementById('checkAll').checked = '';
                       }
                  }
              }
              var  allCheck = document.getElementById('checkAll');
              allCheck.onclick = function(){
                  if (this.checked){
                      that.setFullCheck();
                  }else{
                      that.setFullCheck('');
                  }
              }
              
          },
          setFullCheck:function(checked='checked'){
             for (var ik =0; ik<this.num; ik++){
                  this.check[ik].checked = checked; 
              } 
          },
          isFullCheck:function(){
              var hasCheck = 0;
              for (var k =0; k<this.num; k++){
                   if (this.check[k].checked){
                       hasCheck++;
                   }
              }
              return hasCheck==this.num?true:false;
          },
          getCheckSelect:function(){
              var selectIds = [];
              for (var i=0;i<this.check.length;i++){
                    if (this.check[i].checked){
                       selectIds.push(this.check[i].value);
                    }
              }
              return selectIds;
          }
       }
    });
</script>
