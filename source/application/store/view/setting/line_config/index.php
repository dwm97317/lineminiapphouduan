<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-body">
                    <div class="am-tabs" data-am-tabs>
                        <ul class="am-tabs-nav am-nav am-nav-tabs">
                            <li class="am-active"><a href="#tab1">基础配置 (LIFF)</a></li>
                            <li><a href="#tab2">消息通知 (Messaging API)</a></li>
                            <li><a href="#tab3">支付设置 (LINE Pay)</a></li>
                            <li><a href="#tab4">客户联系 (Customer Contact)</a></li>
                        </ul>

                        <div class="am-tabs-bd">
                            <!-- 基础配置 -->
                            <div class="am-tab-panel am-fade am-in am-active" id="tab1">
                                <form action="<?= url('setting.line_config/index') ?>" class="am-form tpl-form-line-form line-config-form" method="post">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">LINE Mini App (LIFF) 配置</div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">是否启用 LINE 登录</label>
                                            <div class="am-u-sm-9">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="line_config[is_enable]" value="1" data-am-ucheck <?= isset($line_config['is_enable']) && $line_config['is_enable'] == '1' ? 'checked' : '' ?> required> 启用
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="line_config[is_enable]" value="0" data-am-ucheck <?= !isset($line_config['is_enable']) || $line_config['is_enable'] == '0' ? 'checked' : '' ?>> 禁用
                                                </label>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">LINE Channel ID</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="line_config[channel_id]" value="<?= $line_config['channel_id'] ?? '' ?>" required>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">LINE Channel Secret</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="line_config[channel_secret]" value="<?= $line_config['channel_secret'] ?? '' ?>" required>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">LIFF ID</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="line_config[liff_id]" value="<?= $line_config['liff_id'] ?? '' ?>" required>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">Google Maps API Key</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="line_config[google_maps_key]" value="<?= $line_config['google_maps_key'] ?? '' ?>">
                                                <small>用于地址定位和搜索辅助功能</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">Bot Link (提示关注官方账号)</label>

                                            <div class="am-u-sm-9">
                                                <select class="am-form-field tpl-form-input" name="line_config[bot_link]">
                                                    <option value="Off" <?= isset($line_config['bot_link']) && $line_config['bot_link'] == 'Off' ? 'selected' : '' ?>>关闭 (Off)</option>
                                                    <option value="Normal" <?= isset($line_config['bot_link']) && $line_config['bot_link'] == 'Normal' ? 'selected' : '' ?>>正常 (Normal)</option>
                                                    <option value="Aggressive" <?= isset($line_config['bot_link']) && $line_config['bot_link'] == 'Aggressive' ? 'selected' : '' ?>>激进 (Aggressive)</option>
                                                </select>
                                                <small>用户登录时是否提示关注 LINE 官方账号</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">保存 LIFF 配置</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <!-- 消息通知 -->
                            <div class="am-tab-panel am-fade" id="tab2">
                                <form action="<?= url('setting.line_config/index') ?>" class="am-form tpl-form-line-form line-messaging-form" method="post">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">LINE Messaging API 配置</div>
                                        </div>

                                        <!-- Channel 配置 -->
                                        <div class="widget-body am-fr">
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-form-label form-require">启用消息通知</label>
                                                <div class="am-u-sm-9">
                                                    <label class="am-radio-inline">
                                                        <input type="radio" name="line_messaging[is_enable]" value="1" <?= isset($line_messaging['is_enable']) && $line_messaging['is_enable'] == '1' ? 'checked' : '' ?>> 启用
                                                    </label>
                                                    <label class="am-radio-inline">
                                                        <input type="radio" name="line_messaging[is_enable]" value="0" <?= !isset($line_messaging['is_enable']) || $line_messaging['is_enable'] == '0' ? 'checked' : '' ?>> 禁用
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-form-label form-require">Channel ID</label>
                                                <div class="am-u-sm-9">
                                                    <input type="text" class="tpl-form-input" 
                                                        name="line_messaging[channel_id]" 
                                                        value="<?= $line_messaging['channel_id'] ?? '' ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-form-label">Channel Secret</label>
                                                <div class="am-u-sm-9">
                                                    <input type="text" class="tpl-form-input" 
                                                        name="line_messaging[channel_secret]" 
                                                        value="<?= $line_messaging['channel_secret'] ?? '' ?>">
                                                </div>
                                            </div>
                                            
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-form-label form-require">Access Token</label>
                                                <div class="am-u-sm-9">
                                                    <textarea rows="3" class="tpl-form-input" 
                                                        name="line_messaging[access_token]" 
                                                        placeholder="Channel Access Token (长期有效)"><?= $line_messaging['access_token'] ?? '' ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="am-form-group">
                                                <label class="am-u-sm-3 am-form-label">LIFF URL</label>
                                                <div class="am-u-sm-9">
                                                    <input type="text" class="tpl-form-input" 
                                                        name="line_messaging[liff_url]" 
                                                        value="<?= $line_messaging['liff_url'] ?? '' ?>"
                                                        placeholder="https://liff.line.me/1234567890-abcdefgh">
                                                    <small>用于消息中的跳转链接</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- 消息模板配置 -->
                                        <div class="widget-body am-fr" style="margin-top: 20px;">
                                            <div class="widget-head am-cf">
                                                <div class="widget-title am-fl">消息模板配置</div>
                                            </div>
                                            
                                            <?php 
                                            $templates = [
                                                'inwarehouse' => ['name' => '📦 包裹入库通知', 'color' => '#1DB446'],
                                                'sendpack' => ['name' => '🚚 发货通知', 'color' => '#0066CC'],
                                                'payment' => ['name' => '✅ 支付成功通知', 'color' => '#FF6B00'],
                                                'dabaosuccess' => ['name' => '📋 打包完成通知', 'color' => '#9933FF'],
                                                'payorder' => ['name' => '💰 付款单生成通知', 'color' => '#FF3366'],
                                                'toshop' => ['name' => '🏪 到仓通知', 'color' => '#00CC99'],
                                                'outapply' => ['name' => '📤 出库申请通知', 'color' => '#FF9900'],
                                            ];
                                            
                                            foreach ($templates as $type => $info): 
                                                $template = $line_messaging['templates'][$type] ?? [];
                                            ?>
                                            
                                            <div class="am-panel am-panel-default" style="margin-bottom: 15px; border-left: 3px solid <?= $info['color'] ?>;">
                                                <div class="am-panel-hd" style="background-color: #f5f5f5; padding: 10px;">
                                                    <h4 class="am-panel-title" style="margin: 0;">
                                                        <?= $info['name'] ?>
                                                        <label class="am-checkbox-inline" style="float: right; margin: 0;">
                                                            <input type="checkbox" 
                                                                name="line_messaging[templates][<?= $type ?>][is_enable]" 
                                                                value="1" 
                                                                <?= isset($template['is_enable']) && $template['is_enable'] == '1' ? 'checked' : '' ?>>
                                                            启用
                                                        </label>
                                                    </h4>
                                                </div>
                                                <div class="am-panel-bd">
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">替代文本</label>
                                                        <div class="am-u-sm-9">
                                                            <input type="text" class="tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][alt_text]" 
                                                                value="<?= $template['alt_text'] ?? $info['name'] ?>">
                                                            <small>当用户无法查看 Flex Message 时显示的文本</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">消息标题</label>
                                                        <div class="am-u-sm-9">
                                                            <input type="text" class="tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][title]" 
                                                                value="<?= $template['title'] ?? $info['name'] ?>">
                                                            <small>消息卡片顶部显示的标题</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">优先级</label>
                                                        <div class="am-u-sm-9">
                                                            <select class="am-form-field tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][priority]">
                                                                <option value="high" <?= isset($template['priority']) && $template['priority'] == 'high' ? 'selected' : '' ?>>高 - 重要通知</option>
                                                                <option value="normal" <?= !isset($template['priority']) || $template['priority'] == 'normal' ? 'selected' : '' ?>>普通 - 常规通知</option>
                                                                <option value="low" <?= isset($template['priority']) && $template['priority'] == 'low' ? 'selected' : '' ?>>低 - 次要通知</option>
                                                            </select>
                                                            <small>影响消息发送的优先级队列</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">发送延迟</label>
                                                        <div class="am-u-sm-9">
                                                            <div class="am-input-group">
                                                                <input type="number" class="tpl-form-input" 
                                                                    name="line_messaging[templates][<?= $type ?>][send_delay]" 
                                                                    value="<?= $template['send_delay'] ?? 0 ?>" 
                                                                    min="0" 
                                                                    max="3600">
                                                                <span class="am-input-group-label">秒</span>
                                                            </div>
                                                            <small>触发事件后延迟多少秒发送（0 = 立即发送）</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">主题颜色</label>
                                                        <div class="am-u-sm-9">
                                                            <input type="color" class="tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][theme_color]" 
                                                                value="<?= $template['theme_color'] ?? $info['color'] ?>" 
                                                                style="width: 100px; height: 40px;">
                                                            <small>消息卡片的主题颜色</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">按钮文本</label>
                                                        <div class="am-u-sm-9">
                                                            <input type="text" class="tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][button_text]" 
                                                                value="<?= $template['button_text'] ?? '查看详情' ?>" 
                                                                placeholder="例如：查看详情、立即支付">
                                                            <small>消息底部按钮显示的文字</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">备注信息</label>
                                                        <div class="am-u-sm-9">
                                                            <textarea rows="2" class="tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][remark]" 
                                                                placeholder="可选的额外说明信息"><?= $template['remark'] ?? '' ?></textarea>
                                                            <small>显示在消息底部的备注文字（可选）</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">发送关联图片</label>
                                                        <div class="am-u-sm-9">
                                                            <label class="am-checkbox-inline">
                                                                <input type="checkbox" 
                                                                    name="line_messaging[templates][<?= $type ?>][send_images]" 
                                                                    value="1" 
                                                                    <?= isset($template['send_images']) && $template['send_images'] == '1' ? 'checked' : '' ?>>
                                                                启用图片发送
                                                            </label>
                                                            <small style="display: block; margin-top: 5px;">
                                                                启用后，将在消息后附带发送关联的包裹图片（如入库照片等）
                                                            </small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">最大图片数量</label>
                                                        <div class="am-u-sm-9">
                                                            <select class="am-form-field tpl-form-input" 
                                                                name="line_messaging[templates][<?= $type ?>][max_images]">
                                                                <option value="1" <?= isset($template['max_images']) && $template['max_images'] == '1' ? 'selected' : '' ?>>1张</option>
                                                                <option value="2" <?= isset($template['max_images']) && $template['max_images'] == '2' ? 'selected' : '' ?>>2张</option>
                                                                <option value="3" <?= !isset($template['max_images']) || $template['max_images'] == '3' ? 'selected' : '' ?>>3张</option>
                                                                <option value="4" <?= isset($template['max_images']) && $template['max_images'] == '4' ? 'selected' : '' ?>>4张</option>
                                                            </select>
                                                            <small>每条消息最多发送的图片数量（LINE限制每次最多5条消息，包含1条文字消息）</small>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">模板变量</label>
                                                        <div class="am-u-sm-9">
                                                            <?php 
                                                            // 获取当前选中的变量
                                                            $currentVariables = $template['variables'] ?? [];
                                                            if (is_string($currentVariables)) {
                                                                $decoded = $currentVariables;
                                                                for ($i = 0; $i < 5; $i++) {
                                                                    $temp = html_entity_decode($decoded);
                                                                    if ($temp === $decoded) break;
                                                                    $decoded = $temp;
                                                                }
                                                                $currentVariables = json_decode(trim($decoded, '"\''), true) ?: [];
                                                            }
                                                            
                                                            // 获取该消息类型的可用变量
                                                            $availableVars = $availableVariables[$type] ?? [];
                                                            ?>
                                                            
                                                            <div class="variable-selector" style="border: 1px solid #ddd; border-radius: 4px; padding: 10px; background: #fafafa;">
                                                                <div style="margin-bottom: 10px;">
                                                                    <strong>选择要在消息中显示的变量：</strong>
                                                                    <small style="color: #666; display: block; margin-top: 5px;">
                                                                        勾选的变量将在消息模板中可用，使用 {{变量名}} 格式引用
                                                                    </small>
                                                                </div>
                                                                
                                                                <!-- 横向滚动容器 -->
                                                                <div style="overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding-bottom: 10px;">
                                                                    <div style="display: inline-flex; gap: 10px; min-width: 100%;">
                                                                        <?php foreach ($availableVars as $varName => $varInfo): ?>
                                                                        <label class="am-checkbox" style="display: inline-block; white-space: normal; width: 220px; flex-shrink: 0; margin: 0; padding: 12px; background: white; border-radius: 4px; border: 1px solid #e0e0e0; vertical-align: top;">
                                                                            <div style="margin-bottom: 8px;">
                                                                                <input type="checkbox" 
                                                                                    name="line_messaging[templates][<?= $type ?>][selected_variables][]" 
                                                                                    value="<?= $varName ?>"
                                                                                    <?= in_array($varName, $currentVariables) ? 'checked' : '' ?>
                                                                                    <?= $varInfo['required'] ? 'disabled checked' : '' ?>>
                                                                                <span style="font-weight: 500; color: #333; font-size: 13px;">
                                                                                    <?= $varInfo['label'] ?>
                                                                                    <?= $varInfo['required'] ? '<span style="color: red; font-size: 14px;">*</span>' : '' ?>
                                                                                </span>
                                                                            </div>
                                                                            <div style="margin-left: 20px;">
                                                                                <code style="font-size: 11px; color: #666; background: #f5f5f5; padding: 2px 6px; border-radius: 3px; display: inline-block; margin-bottom: 5px;">
                                                                                    {{<?= $varName ?>}}
                                                                                </code>
                                                                                <br>
                                                                                <small style="color: #999; font-size: 11px; line-height: 1.4; display: block; margin-top: 3px;">
                                                                                    示例: <?= $varInfo['example'] ?>
                                                                                </small>
                                                                            </div>
                                                                        </label>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <!-- 滚动提示 -->
                                                                <div style="text-align: center; margin-top: 5px;">
                                                                    <small style="color: #999;">
                                                                        <i class="am-icon-arrows-h"></i> 
                                                                        左右滑动查看更多变量
                                                                    </small>
                                                                </div>
                                                                
                                                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                                                    <small style="color: #666;">
                                                                        <i class="am-icon-info-circle"></i> 
                                                                        标记 <span style="color: red;">*</span> 的变量为必需变量，无法取消
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- 当前已选变量预览 -->
                                                            <div style="margin-top: 10px;">
                                                                <strong style="font-size: 12px;">当前已选变量：</strong>
                                                                <code class="selected-vars-preview-<?= $type ?>" style="background: #f5f5f5; padding: 8px; display: block; font-size: 12px; border-radius: 4px; line-height: 1.6; margin-top: 5px;">
                                                                    <?php 
                                                                    if (is_array($currentVariables) && !empty($currentVariables)) {
                                                                        echo implode(', ', $currentVariables);
                                                                    } else {
                                                                        echo '<span style="color: #999;">暂无选择</span>';
                                                                    }
                                                                    ?>
                                                                </code>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="am-form-group">
                                                        <label class="am-u-sm-3 am-form-label">操作</label>
                                                        <div class="am-u-sm-9">
                                                            <button type="button" class="am-btn am-btn-primary am-btn-xs" 
                                                                onclick="testMessage('<?= $type ?>')">
                                                                <i class="am-icon-send"></i> 发送测试消息
                                                            </button>
                                                            <button type="button" class="am-btn am-btn-default am-btn-xs" 
                                                                onclick="previewTemplate('<?= $type ?>')">
                                                                <i class="am-icon-eye"></i> 预览模板
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- 隐藏字段：保存完整的配置 -->
                                                    <input type="hidden" 
                                                        name="line_messaging[templates][<?= $type ?>][name]" 
                                                        value="<?= $template['name'] ?? $info['name'] ?>">
                                                    <input type="hidden" 
                                                        name="line_messaging[templates][<?= $type ?>][send_delay]" 
                                                        value="<?= $template['send_delay'] ?? 0 ?>">
                                                    <input type="hidden" 
                                                        name="line_messaging[templates][<?= $type ?>][flex_template]" 
                                                        value='<?= isset($template['flex_template']) ? (is_string($template['flex_template']) ? $template['flex_template'] : json_encode($template['flex_template'])) : '' ?>'>
                                                    <input type="hidden" 
                                                        name="line_messaging[templates][<?= $type ?>][variables]" 
                                                        value='<?= json_encode($template['variables'] ?? []) ?>'>
                                                </div>
                                            </div>
                                            
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">提交保存</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <!-- 支付设置 -->
                            <div class="am-tab-panel am-fade" id="tab3">
                                <form action="<?= url('setting.line_config/index') ?>" class="am-form tpl-form-line-form line-pay-form" method="post">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">LINE Pay 支付配置</div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">是否启用 LINE Pay</label>
                                            <div class="am-u-sm-9">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="line_pay[is_enable]" value="1" data-am-ucheck <?= isset($line_pay['is_enable']) && $line_pay['is_enable'] == '1' ? 'checked' : '' ?> required> 启用
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="line_pay[is_enable]" value="0" data-am-ucheck <?= !isset($line_pay['is_enable']) || $line_pay['is_enable'] == '0' ? 'checked' : '' ?>> 禁用
                                                </label>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">Channel ID</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="line_pay[channel_id]" value="<?= $line_pay['channel_id'] ?? '' ?>">
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">Channel Secret</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="tpl-form-input" name="line_pay[channel_secret]" value="<?= $line_pay['channel_secret'] ?? '' ?>">
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">测试模式</label>
                                            <div class="am-u-sm-9">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="line_pay[is_test]" value="1" data-am-ucheck <?= !isset($line_pay['is_test']) || $line_pay['is_test'] == '1' ? 'checked' : '' ?>> 开启 (Sandbox)
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="line_pay[is_test]" value="0" data-am-ucheck <?= isset($line_pay['is_test']) && $line_pay['is_test'] == '0' ? 'checked' : '' ?>> 关闭 (Production)
                                                </label>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">保存支付配置</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                            
                            <!-- 客户联系配置 -->
                            <div class="am-tab-panel am-fade" id="tab4">
                                <form action="<?= url('setting.line_config/index') ?>" class="am-form tpl-form-line-form customer-contact-form" method="post">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">ฝ่ายบริการลูกค้า (客户联系配置)</div>
                                        </div>
                                        
                                        <div class="am-alert am-alert-secondary" style="margin-bottom: 20px;">
                                            <p><i class="am-icon-info-circle"></i> 配置客服联系方式后，将在前端主页展示，方便用户快速联系客服。</p>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">Hotline (TH)</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" 
                                                       class="tpl-form-input" 
                                                       name="customer_contact[hotline_th]" 
                                                       value="<?= isset($customer_contact['hotline_th']) ? htmlspecialchars($customer_contact['hotline_th']) : '' ?>"
                                                       placeholder="例如: +66 2 123 4567">
                                                <small class="am-form-help">泰国客服热线电话，用户点击后可直接拨打</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">LINE Support</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" 
                                                       class="tpl-form-input" 
                                                       name="customer_contact[line_support]" 
                                                       value="<?= isset($customer_contact['line_support']) ? htmlspecialchars($customer_contact['line_support']) : '' ?>"
                                                       placeholder="例如: yourlineid">
                                                <small class="am-form-help">LINE 官方账号 ID（不含 @），用户点击后可打开 LINE 聊天</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">WeChat</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" 
                                                       class="tpl-form-input" 
                                                       name="customer_contact[wechat]" 
                                                       value="<?= isset($customer_contact['wechat']) ? htmlspecialchars($customer_contact['wechat']) : '' ?>"
                                                       placeholder="例如: yourwechatid">
                                                <small class="am-form-help">微信客服账号，用户点击后可复制微信号</small>
                                            </div>
                                        </div>
                                        
                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <div class="am-alert am-alert-warning">
                                                    <p><strong>格式要求：</strong></p>
                                                    <ul style="margin: 5px 0; padding-left: 20px;">
                                                        <li>Hotline: 只能包含数字、+、-、空格和括号</li>
                                                        <li>LINE Support: 只能包含字母、数字、下划线和点</li>
                                                        <li>WeChat: 只能包含字母、数字、下划线和连字符</li>
                                                    </ul>
                                                    <p style="margin-top: 10px;"><strong>注意：</strong>所有字段均为可选，未填写的联系方式不会在前端显示。</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">保存客户联系配置</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        /**
         * 表单验证提交 - 为每个表单单独绑定
         */
        $('.line-config-form').superForm();
        $('.line-messaging-form').superForm();
        $('.line-pay-form').superForm();
        $('.customer-contact-form').superForm();
        
        /**
         * 监听变量选择变化，实时更新预览
         */
        $('input[name*="selected_variables"]').on('change', function() {
            var container = $(this).closest('.variable-selector');
            var type = $(this).attr('name').match(/\[(\w+)\]/)[1];
            var previewEl = $('.selected-vars-preview-' + type);
            
            // 收集所有选中的变量
            var selectedVars = [];
            container.find('input[type="checkbox"]:checked').each(function() {
                selectedVars.push($(this).val());
            });
            
            // 更新预览
            if (selectedVars.length > 0) {
                previewEl.html(selectedVars.join(', '));
            } else {
                previewEl.html('<span style="color: #999;">暂无选择</span>');
            }
        });
    });
    
    /**
     * 发送测试消息
     * @param {string} type 消息类型
     */
    function testMessage(type) {
        // 提示用户输入 LINE User ID
        layer.prompt({
            title: '请输入测试用户的 LINE User ID',
            formType: 0,
            value: '',
            btn: ['发送', '取消']
        }, function(lineUserId, index) {
            if (!lineUserId || lineUserId.trim() === '') {
                layer.msg('请输入有效的 LINE User ID');
                return;
            }
            
            layer.close(index);
            
            // 显示加载提示
            var loadingIndex = layer.load(1, {shade: [0.3, '#fff']});
            
            // 发送 AJAX 请求
            $.ajax({
                url: "<?= url('setting.line_config/testMessage') ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    message_type: type,
                    line_user_id: lineUserId.trim()
                },
                success: function(result) {
                    layer.close(loadingIndex);
                    
                    if (result.code === 1) {
                        layer.msg(result.msg || '测试消息发送成功', {
                            icon: 1,
                            time: 2000
                        });
                    } else {
                        layer.msg(result.msg || '测试消息发送失败', {
                            icon: 2,
                            time: 3000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    layer.close(loadingIndex);
                    layer.msg('请求失败：' + error, {
                        icon: 2,
                        time: 3000
                    });
                }
            });
        });
    }
    
    /**
     * 预览模板（可选功能）
     * @param {string} type 消息类型
     */
    function previewTemplate(type) {
        var loadingIndex = layer.load(1, {shade: [0.3, '#fff']});
        
        $.ajax({
            url: "<?= url('setting.line_config/previewTemplate') ?>",
            type: 'GET',
            dataType: 'json',
            data: {
                type: type
            },
            success: function(result) {
                layer.close(loadingIndex);
                
                if (result.code === 1) {
                    var template = result.data.template;
                    var flexSimulatorUrl = result.data.flex_simulator_url;
                    
                    // 显示模板信息
                    var content = '<div style="max-height: 400px; overflow-y: auto;">';
                    content += '<p><strong>模板名称：</strong>' + template.name + '</p>';
                    content += '<p><strong>替代文本：</strong>' + template.alt_text + '</p>';
                    content += '<p><strong>优先级：</strong>' + template.priority + '</p>';
                    content += '<p><strong>发送延迟：</strong>' + template.send_delay + ' 秒</p>';
                    content += '<p><strong>变量列表：</strong>' + (template.variables ? template.variables.join(', ') : '无') + '</p>';
                    content += '<hr>';
                    content += '<p><strong>Flex Message JSON：</strong></p>';
                    content += '<pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px;">';
                    content += JSON.stringify(template.flex_template, null, 2);
                    content += '</pre>';
                    content += '<p style="margin-top: 10px;"><a href="' + flexSimulatorUrl + '" target="_blank">在 LINE Flex Simulator 中测试</a></p>';
                    content += '</div>';
                    
                    layer.open({
                        type: 1,
                        title: '模板预览 - ' + template.name,
                        area: ['600px', '500px'],
                        content: content
                    });
                } else {
                    layer.msg(result.msg || '获取模板失败', {
                        icon: 2,
                        time: 2000
                    });
                }
            },
            error: function(xhr, status, error) {
                layer.close(loadingIndex);
                layer.msg('请求失败：' + error, {
                    icon: 2,
                    time: 3000
                });
            }
        });
    }
</script>

