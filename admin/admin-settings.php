<?php
/**
 * Amazon Corner Buddy 管理画面設定ページ
 *
 * @package Amazon_Corner_Buddy
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// 設定の保存処理
if (isset($_POST['submit']) && check_admin_referer('acb_settings_nonce')) {
    $options = get_option('acb_options', array());
    
    $new_options = array(
        'enabled' => isset($_POST['acb_options']['enabled']) ? true : false,
        'animation_interval' => max(1, min(60, intval($_POST['acb_options']['animation_interval']))),
        'icon_size' => max(20, min(100, intval($_POST['acb_options']['icon_size']))),
        'icon_size_pc' => max(30, min(150, intval($_POST['acb_options']['icon_size_pc']))),
        'opacity' => max(0.1, min(1.0, floatval($_POST['acb_options']['opacity']))),
        'position_bottom' => max(0, min(500, intval($_POST['acb_options']['position_bottom']))),
        'position_left' => max(0, min(500, intval($_POST['acb_options']['position_left']))),
        'border_radius' => max(0, min(50, intval($_POST['acb_options']['border_radius']))),
        'link_url' => esc_url_raw($_POST['acb_options']['link_url']),
        'speech_bubble_enabled' => isset($_POST['acb_options']['speech_bubble_enabled']) ? true : false,
        'speech_bubble_frequency' => max(1, min(5, intval($_POST['acb_options']['speech_bubble_frequency']))),
        'swipe_hide_enabled' => isset($_POST['acb_options']['swipe_hide_enabled']) ? true : false,
        // カスタムメッセージ設定（25文字制限）
        'custom_messages' => array(
            'morning' => array(
                'greeting' => isset($_POST['acb_options']['custom_messages']['morning']['greeting']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '朝の挨拶メッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['morning']['greeting']))) : array(),
                'cta' => isset($_POST['acb_options']['custom_messages']['morning']['cta']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '朝のCTAメッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['morning']['cta']))) : array()
            ),
            'afternoon' => array(
                'greeting' => isset($_POST['acb_options']['custom_messages']['afternoon']['greeting']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '昼の挨拶メッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['afternoon']['greeting']))) : array(),
                'cta' => isset($_POST['acb_options']['custom_messages']['afternoon']['cta']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '昼のCTAメッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['afternoon']['cta']))) : array()
            ),
            'evening' => array(
                'greeting' => isset($_POST['acb_options']['custom_messages']['evening']['greeting']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '夕方の挨拶メッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['evening']['greeting']))) : array(),
                'cta' => isset($_POST['acb_options']['custom_messages']['evening']['cta']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '夕方のCTAメッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['evening']['cta']))) : array()
            ),
            'night' => array(
                'greeting' => isset($_POST['acb_options']['custom_messages']['night']['greeting']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '夜の挨拶メッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['night']['greeting']))) : array(),
                'cta' => isset($_POST['acb_options']['custom_messages']['night']['cta']) ? 
                    array_map(function($msg) { 
                        $sanitized = sanitize_text_field($msg);
                        if (mb_strlen($sanitized) > 25) {
                            add_settings_error('acb_options', 'message_too_long', '夜のCTAメッセージで25文字を超えるものがあります。', 'error');
                            return mb_substr($sanitized, 0, 25);
                        }
                        return $sanitized;
                    }, array_filter(explode("\n", $_POST['acb_options']['custom_messages']['night']['cta']))) : array()
            )
        )
    );
    
    update_option('acb_options', $new_options);
    
    // エラーがない場合のみ成功メッセージを表示
    $errors = get_settings_errors('acb_options');
    if (empty($errors)) {
        echo '<div class="notice notice-success"><p>設定を保存しました。</p></div>';
    } else {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error['message']) . ' 25文字以内に修正して再保存してください。</p></div>';
        }
    }
}

$options = get_option('acb_options', array());
$enabled = isset($options['enabled']) ? $options['enabled'] : true;
$animation_interval = isset($options['animation_interval']) ? $options['animation_interval'] : 10;
$icon_size = isset($options['icon_size']) ? $options['icon_size'] : 48;
$icon_size_pc = isset($options['icon_size_pc']) ? $options['icon_size_pc'] : 64;
$opacity = isset($options['opacity']) ? $options['opacity'] : 0.8;
$position_bottom = isset($options['position_bottom']) ? $options['position_bottom'] : 20;
$position_left = isset($options['position_left']) ? $options['position_left'] : 20;
$border_radius = isset($options['border_radius']) ? $options['border_radius'] : 12;
$link_url = isset($options['link_url']) ? $options['link_url'] : 'https://amzn.to/446mmWI';
$speech_bubble_enabled = isset($options['speech_bubble_enabled']) ? $options['speech_bubble_enabled'] : true;
$speech_bubble_frequency = isset($options['speech_bubble_frequency']) ? $options['speech_bubble_frequency'] : 3;
$swipe_hide_enabled = isset($options['swipe_hide_enabled']) ? $options['swipe_hide_enabled'] : true;

// カスタムメッセージのデフォルト値
$default_messages = array(
    'morning' => array(
        'greeting' => array('おはようございます☀️', '素敵な一日の始まりですね', '今日も頑張りましょう✨'),
        'cta' => array('お得な商品をチェック！', '新商品が入荷しています', '朝の特別セールあります')
    ),
    'afternoon' => array(
        'greeting' => array('こんにちは😊', 'お疲れ様です', '午後もお疲れ様'),
        'cta' => array('ランチタイムセール中！', 'お買い物はお済みですか？', '今だけ特別価格です')
    ),
    'evening' => array(
        'greeting' => array('お疲れ様です🌅', '夕方になりましたね', '今日もお疲れ様でした'),
        'cta' => array('帰宅前にチェック！', '夜のお得情報あります', '限定セール開催中')
    ),
    'night' => array(
        'greeting' => array('今日もお疲れ様でした🌙', 'おつかれさまです', 'ゆっくりお過ごしください'),
        'cta' => array('お買い物は済みましたか？', '夜のタイムセール中！', '明日の準備はいかがですか？')
    )
);

$custom_messages = isset($options['custom_messages']) ? $options['custom_messages'] : $default_messages;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div style="display: flex; gap: 20px; margin-top: 20px;">
        <!-- メイン設定エリア -->
        <div style="flex: 2;">
            <form method="post" action="">
                <?php wp_nonce_field('acb_settings_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <!-- プラグイン有効/無効 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_enabled">プラグインを有効にする</label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="acb_enabled" 
                                       name="acb_options[enabled]" 
                                       value="1" 
                                       <?php checked(1, $enabled); ?>>
                                <label for="acb_enabled">Amazon Corner Buddyを表示する</label>
                                <p class="description">チェックを外すとアイコンが非表示になります。</p>
                            </td>
                        </tr>
                        
                        <!-- アニメーション間隔 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_animation_interval">アニメーション間隔</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="acb_animation_interval" 
                                       name="acb_options[animation_interval]" 
                                       value="<?php echo esc_attr($animation_interval); ?>" 
                                       min="1" 
                                       max="60" 
                                       style="width: 80px;"> 秒
                                <p class="description">アニメーションを実行する間隔を設定します（1-60秒）。</p>
                            </td>
                        </tr>
                        
                        <!-- アイコンサイズ設定 -->
                        <tr>
                            <th scope="row" colspan="2">
                                <h3 style="margin: 30px 0 10px 0; color: #0073aa;">📱 アイコンサイズ設定</h3>
                                <p style="color: #666; margin-bottom: 20px;">PC表示とモバイル表示で異なるサイズを設定できます。</p>
                            </th>
                        </tr>
                        
                        <!-- モバイル表示サイズ -->
                        <tr>
                            <th scope="row">
                                <label for="acb_icon_size">📱 モバイル表示サイズ</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="acb_icon_size" 
                                       name="acb_options[icon_size]" 
                                       value="<?php echo esc_attr($icon_size); ?>" 
                                       min="20" 
                                       max="150" 
                                       style="width: 80px;"> px
                                <p class="description">モバイル端末（768px以下）でのアイコンサイズを設定します（20-150px）。</p>
                            </td>
                        </tr>
                        
                        <!-- PC表示サイズ -->
                        <tr>
                            <th scope="row">
                                <label for="acb_icon_size_pc">💻 PC表示サイズ</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="acb_icon_size_pc" 
                                       name="acb_options[icon_size_pc]" 
                                       value="<?php echo esc_attr($icon_size_pc); ?>" 
                                       min="30" 
                                       max="200" 
                                       style="width: 80px;"> px
                                <p class="description">PC画面（769px以上）でのアイコンサイズを設定します（30-200px）。</p>
                            </td>
                        </tr>
                        
                        <!-- 透明度 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_opacity">透明度</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="acb_opacity" 
                                       name="acb_options[opacity]" 
                                       value="<?php echo esc_attr($opacity); ?>" 
                                       min="0.1" 
                                       max="1.0" 
                                       step="0.1" 
                                       style="width: 80px;">
                                <p class="description">アイコンの透明度を設定します（0.1-1.0）。1.0で完全不透明。</p>
                            </td>
                        </tr>
                        
                        <!-- 角丸設定 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_border_radius">角丸の大きさ</label>
                            </th>
                            <td>
                                <input type="number" 
                                       id="acb_border_radius" 
                                       name="acb_options[border_radius]" 
                                       value="<?php echo esc_attr($border_radius); ?>" 
                                       min="0" 
                                       max="50" 
                                       style="width: 80px;"> px
                                <p class="description">アイコンの角丸の大きさを設定します（0-50px）。0で角丸なし。</p>
                            </td>
                        </tr>
                        
                        <!-- 位置設定 -->
                        <tr>
                            <th scope="row">位置設定</th>
                            <td>
                                <label for="acb_position_left">左端からの距離:</label>
                                <input type="number" 
                                       id="acb_position_left" 
                                       name="acb_options[position_left]" 
                                       value="<?php echo esc_attr($position_left); ?>" 
                                       min="0" 
                                       max="500" 
                                       style="width: 80px;"> px
                                <br><br>
                                <label for="acb_position_bottom">下端からの距離:</label>
                                <input type="number" 
                                       id="acb_position_bottom" 
                                       name="acb_options[position_bottom]" 
                                       value="<?php echo esc_attr($position_bottom); ?>" 
                                       min="0" 
                                       max="500" 
                                       style="width: 80px;"> px
                                <p class="description">画面の左下角からの距離を設定します。</p>
                            </td>
                        </tr>
                        
                        <!-- リンクURL -->
                        <tr>
                            <th scope="row">
                                <label for="acb_link_url">リンクURL</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="acb_link_url" 
                                       name="acb_options[link_url]" 
                                       value="<?php echo esc_attr($link_url); ?>" 
                                       style="width: 400px;">
                                <p class="description">アイコンクリック時のリンク先URLを設定します。</p>
                            </td>
                        </tr>
                        
                        <!-- スワイプ隠し機能設定 -->
                        <tr>
                            <th scope="row" colspan="2">
                                <h3 style="margin: 30px 0 10px 0; color: #d63384;">📱 モバイル機能設定</h3>
                            </th>
                        </tr>
                        
                        <!-- スワイプ隠し機能 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_swipe_hide_enabled">スワイプ隠し機能</label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="acb_swipe_hide_enabled" 
                                       name="acb_options[swipe_hide_enabled]" 
                                       value="1" 
                                       <?php checked(1, $swipe_hide_enabled); ?>>
                                <label for="acb_swipe_hide_enabled">左スワイプでバナーを隠す機能を有効にする</label>
                                <p class="description">
                                    <strong>📱 モバイル専用機能:</strong> 画面幅768px以下でのみ動作します。<br>
                                    <strong>使い方:</strong> バナーを左にスワイプすると画面外に隠れ、右向き矢印マークが表示されます。<br>
                                    <strong>復帰方法:</strong> 矢印マークをタップすると元の位置に戻ります。<br>
                                    <strong>状態保持:</strong> ページを移動しても隠し状態が維持されます（セッション中）。
                                </p>
                            </td>
                        </tr>
                        
                        <!-- 吹き出しメッセージ設定 -->
                        <tr>
                            <th scope="row" colspan="2">
                                <h3 style="margin: 30px 0 10px 0; color: #0073aa;">💬 吹き出しメッセージ設定</h3>
                            </th>
                        </tr>
                        
                        <!-- 吹き出し有効/無効 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_speech_bubble_enabled">吹き出しメッセージ機能</label>
                            </th>
                            <td>
                                <input type="checkbox" 
                                       id="acb_speech_bubble_enabled" 
                                       name="acb_options[speech_bubble_enabled]" 
                                       value="1" 
                                       <?php checked(1, $speech_bubble_enabled); ?>>
                                <label for="acb_speech_bubble_enabled">時間帯別の吹き出しメッセージを表示する</label>
                                <p class="description">アニメーション時に時間帯に応じた２段階メッセージを表示します。<br>
                                <strong>例:</strong> 「今日もお疲れ様でした🌙」→「お買い物は済みましたか？」</p>
                            </td>
                        </tr>
                        
                        <!-- 表示頻度 -->
                        <tr>
                            <th scope="row">
                                <label for="acb_speech_bubble_frequency">表示頻度</label>
                            </th>
                            <td>
                                <select id="acb_speech_bubble_frequency" 
                                        name="acb_options[speech_bubble_frequency]" 
                                        style="width: 200px;">
                                    <option value="1" <?php selected(1, $speech_bubble_frequency); ?>>とても頻繁（毎回表示）</option>
                                    <option value="2" <?php selected(2, $speech_bubble_frequency); ?>>頻繁（2回に1回）</option>
                                    <option value="3" <?php selected(3, $speech_bubble_frequency); ?>>標準（3回に1回）</option>
                                    <option value="4" <?php selected(4, $speech_bubble_frequency); ?>>控え目（4回に1回）</option>
                                    <option value="5" <?php selected(5, $speech_bubble_frequency); ?>>とても控え目（5回に1回）</option>
                                </select>
                                <p class="description">アニメーション何回に1回吹き出しを表示するかを設定します。</p>
                            </td>
                        </tr>
                        
                        <!-- カスタムメッセージ編集 -->
                        <tr>
                            <th scope="row" colspan="2">
                                <h3 style="margin: 30px 0 10px 0; color: #d63384;">✏️ カスタムメッセージ編集</h3>
                                <p style="color: #666; margin-bottom: 20px;">各時間帯の吹き出しメッセージをカスタマイズできます。メッセージは改行区切りで複数設定でき、ランダムに表示されます。</p>
                            </th>
                        </tr>
                        
                        <!-- 朝のメッセージ -->
                        <tr>
                            <th scope="row">
                                <label for="acb_morning_greeting">🌅 朝（5:00-9:59）</label>
                            </th>
                            <td>
                                <div style="margin-bottom: 15px;">
                                    <label for="acb_morning_greeting" style="font-weight: bold; display: block; margin-bottom: 5px;">挨拶メッセージ:</label>
                                    <textarea id="acb_morning_greeting" 
                                              name="acb_options[custom_messages][morning][greeting]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: おはようございます☀️&#10;素敵な一日の始まりですね&#10;今日も頑張りましょう✨"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['morning']['greeting']) ? $custom_messages['morning']['greeting'] : $default_messages['morning']['greeting']));
                                    ?></textarea>
                                </div>
                                <div>
                                    <label for="acb_morning_cta" style="font-weight: bold; display: block; margin-bottom: 5px;">CTAメッセージ:</label>
                                    <textarea id="acb_morning_cta" 
                                              name="acb_options[custom_messages][morning][cta]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: お得な商品をチェック！&#10;新商品が入荷しています&#10;朝の特別セールあります"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['morning']['cta']) ? $custom_messages['morning']['cta'] : $default_messages['morning']['cta']));
                                    ?></textarea>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 昼のメッセージ -->
                        <tr>
                            <th scope="row">
                                <label for="acb_afternoon_greeting">☀️ 昼（10:00-14:59）</label>
                            </th>
                            <td>
                                <div style="margin-bottom: 15px;">
                                    <label for="acb_afternoon_greeting" style="font-weight: bold; display: block; margin-bottom: 5px;">挨拶メッセージ:</label>
                                    <textarea id="acb_afternoon_greeting" 
                                              name="acb_options[custom_messages][afternoon][greeting]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: こんにちは😊&#10;お疲れ様です&#10;午後もお疲れ様"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['afternoon']['greeting']) ? $custom_messages['afternoon']['greeting'] : $default_messages['afternoon']['greeting']));
                                    ?></textarea>
                                </div>
                                <div>
                                    <label for="acb_afternoon_cta" style="font-weight: bold; display: block; margin-bottom: 5px;">CTAメッセージ:</label>
                                    <textarea id="acb_afternoon_cta" 
                                              name="acb_options[custom_messages][afternoon][cta]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: ランチタイムセール中！&#10;お買い物はお済みですか？&#10;今だけ特別価格です"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['afternoon']['cta']) ? $custom_messages['afternoon']['cta'] : $default_messages['afternoon']['cta']));
                                    ?></textarea>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 夕方のメッセージ -->
                        <tr>
                            <th scope="row">
                                <label for="acb_evening_greeting">🌅 夕方（15:00-18:59）</label>
                            </th>
                            <td>
                                <div style="margin-bottom: 15px;">
                                    <label for="acb_evening_greeting" style="font-weight: bold; display: block; margin-bottom: 5px;">挨拶メッセージ:</label>
                                    <textarea id="acb_evening_greeting" 
                                              name="acb_options[custom_messages][evening][greeting]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: お疲れ様です🌅&#10;夕方になりましたね&#10;今日もお疲れ様でした"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['evening']['greeting']) ? $custom_messages['evening']['greeting'] : $default_messages['evening']['greeting']));
                                    ?></textarea>
                                </div>
                                <div>
                                    <label for="acb_evening_cta" style="font-weight: bold; display: block; margin-bottom: 5px;">CTAメッセージ:</label>
                                    <textarea id="acb_evening_cta" 
                                              name="acb_options[custom_messages][evening][cta]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: 帰宅前にチェック！&#10;夜のお得情報あります&#10;限定セール開催中"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['evening']['cta']) ? $custom_messages['evening']['cta'] : $default_messages['evening']['cta']));
                                    ?></textarea>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 夜のメッセージ -->
                        <tr>
                            <th scope="row">
                                <label for="acb_night_greeting">🌙 夜（19:00-4:59）</label>
                            </th>
                            <td>
                                <div style="margin-bottom: 15px;">
                                    <label for="acb_night_greeting" style="font-weight: bold; display: block; margin-bottom: 5px;">挨拶メッセージ:</label>
                                    <textarea id="acb_night_greeting" 
                                              name="acb_options[custom_messages][night][greeting]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: 今日もお疲れ様でした🌙&#10;おつかれさまです&#10;ゆっくりお過ごしください"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['night']['greeting']) ? $custom_messages['night']['greeting'] : $default_messages['night']['greeting']));
                                    ?></textarea>
                                </div>
                                <div>
                                    <label for="acb_night_cta" style="font-weight: bold; display: block; margin-bottom: 5px;">CTAメッセージ:</label>
                                    <textarea id="acb_night_cta" 
                                              name="acb_options[custom_messages][night][cta]" 
                                              rows="3" 
                                              style="width: 100%; max-width: 500px;"
                                              placeholder="例: お買い物は済みましたか？&#10;夜のタイムセール中！&#10;明日の準備はいかがですか？"
                                              data-char-limit="25"><?php 
                                        echo esc_textarea(implode("\n", isset($custom_messages['night']['cta']) ? $custom_messages['night']['cta'] : $default_messages['night']['cta']));
                                    ?></textarea>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- メッセージ例 -->
                        <tr>
                            <th scope="row">設定のヒント</th>
                            <td>
                                <div style="background: #e7f3ff; padding: 15px; border-radius: 6px; border-left: 4px solid #0073aa;">
                                    <p style="margin: 0 0 10px 0;"><strong>📝 編集方法:</strong></p>
                                    <ul style="margin: 0; padding-left: 20px;">
                                        <li>各メッセージは改行区切りで複数設定できます</li>
                                        <li>設定したメッセージからランダムに選択されて表示されます</li>
                                        <li>絵文字や特殊文字も使用可能です</li>
                                        <li>空欄の場合はデフォルトメッセージが使用されます</li>
                                        <li><strong>1メッセージあたり25文字以内で入力してください</strong></li>
                                    </ul>
                                    <p style="margin: 10px 0 0 0;"><strong>⏰ 表示タイミング:</strong> 挨拶メッセージが5秒間表示され、その後CTAメッセージが4秒間表示されます。</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php submit_button('設定を保存'); ?>
            </form>
        </div>
        
        <!-- サイドバー -->
        <div style="flex: 1;">
            <!-- プレビューエリア -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">プレビュー</h3>
                
                <!-- PCプレビュー -->
                <div style="margin-bottom: 15px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #0073aa;">💻 PC表示 (769px以上)</h4>
                    <div style="position: relative; background: #fff; border: 2px dashed #ddd; height: 150px; border-radius: 4px;">
                        <div class="acb-admin-preview-pc" style="position: absolute; 
                                   bottom: <?php echo esc_attr($position_bottom); ?>px; 
                                   left: <?php echo esc_attr($position_left); ?>px; 
                                   width: <?php echo esc_attr($icon_size_pc); ?>px; 
                                   height: <?php echo esc_attr($icon_size_pc); ?>px; 
                                   opacity: <?php echo esc_attr($opacity); ?>; 
                                   background: #232f3e; 
                                   border-radius: <?php echo esc_attr($border_radius); ?>px; 
                                   display: flex; 
                                   align-items: center; 
                                   justify-content: center; 
                                   color: white; 
                                   font-weight: bold;">
                            a
                        </div>
                        <div style="position: absolute; bottom: 5px; right: 5px; font-size: 12px; color: #666;">
                            PCプレビュー
                        </div>
                    </div>
                </div>
                
                <!-- モバイルプレビュー -->
                <div>
                    <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #d63384;">📱 モバイル表示 (768px以下)</h4>
                    <div style="position: relative; background: #fff; border: 2px dashed #ddd; height: 120px; border-radius: 4px;">
                        <div class="acb-admin-preview-mobile" style="position: absolute; 
                                   bottom: <?php echo esc_attr($position_bottom); ?>px; 
                                   left: <?php echo esc_attr($position_left); ?>px; 
                                   width: <?php echo esc_attr($icon_size); ?>px; 
                                   height: <?php echo esc_attr($icon_size); ?>px; 
                                   opacity: <?php echo esc_attr($opacity); ?>; 
                                   background: #232f3e; 
                                   border-radius: <?php echo esc_attr($border_radius); ?>px; 
                                   display: flex; 
                                   align-items: center; 
                                   justify-content: center; 
                                   color: white; 
                                   font-weight: bold;">
                            a
                        </div>
                        <div style="position: absolute; bottom: 5px; right: 5px; font-size: 12px; color: #666;">
                            モバイルプレビュー
                        </div>
                    </div>
                </div>
                
                <p style="font-size: 12px; color: #666; margin: 10px 0 0 0;">
                    実際のサイトでのおおよその表示サイズです。
                </p>
            </div>
            
            <!-- アニメーション一覧 -->
            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">アニメーション一覧</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Shake</strong> - 軽い揺れ</li>
                    <li><strong>Bounce</strong> - 跳ねる動き</li>
                    <li><strong>Wiggle</strong> - くねくね動き</li>
                    <li><strong>Pulse</strong> - 拡大縮小</li>
                    <li><strong>Rotate</strong> - 回転</li>
                    <li><strong>Float</strong> - ふわふわ浮遊</li>
                </ul>
                <p style="font-size: 12px; color: #666; margin-bottom: 0;">
                    これらのアニメーションがランダムに実行されます。
                </p>
            </div>
            
            <!-- 情報ボックス -->
            <div style="background: #e7f3ff; padding: 20px; border-radius: 8px; border-left: 4px solid #0073aa;">
                <h3 style="margin-top: 0; color: #0073aa;">ℹ️ 使用について</h3>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px;">
                    <li>軽量で他のプラグインとの競合を最小限に抑制</li>
                    <li>モバイル端末でも適切に表示</li>
                    <li>アクセシビリティ設定に対応</li>
                    <li>タブが非アクティブ時はアニメーション停止</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* 管理画面専用スタイル */
.acb-admin-preview {
    transition: all 0.3s ease;
}

#acb_icon_size, #acb_opacity, #acb_position_left, #acb_position_bottom {
    transition: all 0.3s ease;
}

/* 設定変更時のライブプレビュー用スタイル */
.acb-preview-container {
    transition: all 0.3s ease;
}
</style>

<script>
jQuery(document).ready(function($) {
    // ライブプレビュー機能
    function updatePreview() {
        var iconSize = $('#acb_icon_size').val();
        var iconSizePc = $('#acb_icon_size_pc').val();
        var opacity = $('#acb_opacity').val();
        var positionLeft = $('#acb_position_left').val();
        var positionBottom = $('#acb_position_bottom').val();
        var borderRadius = $('#acb_border_radius').val();
        
        // PCプレビュー更新
        $('.acb-admin-preview-pc').css({
            'width': iconSizePc + 'px',
            'height': iconSizePc + 'px',
            'opacity': opacity,
            'left': positionLeft + 'px',
            'bottom': positionBottom + 'px',
            'border-radius': borderRadius + 'px'
        });
        
        // モバイルプレビュー更新
        $('.acb-admin-preview-mobile').css({
            'width': iconSize + 'px',
            'height': iconSize + 'px',
            'opacity': opacity,
            'left': positionLeft + 'px',
            'bottom': positionBottom + 'px',
            'border-radius': borderRadius + 'px'
        });
    }
    
    // 文字数カウンター機能
    function setupCharCounters() {
        $('textarea[data-char-limit]').each(function() {
            var $textarea = $(this);
            var limit = parseInt($textarea.data('char-limit'));
            var counterId = $textarea.attr('id') + '_counter';
            
            // カウンター要素を作成
            var $counter = $('<div>', {
                id: counterId,
                style: 'font-size: 12px; color: #666; margin-top: 5px; text-align: right;'
            });
            
            // textareaの後に挿入
            $textarea.after($counter);
            
            // カウント更新関数
            function updateCounter() {
                var currentLength = $textarea.val().split('\n').map(function(line) {
                    return line.trim();
                }).filter(function(line) {
                    return line.length > 0;
                }).map(function(line) {
                    return line.length;
                });
                
                var maxLength = Math.max.apply(Math, currentLength.concat([0]));
                var color = maxLength > limit ? '#d63384' : maxLength > limit * 0.8 ? '#ff9500' : '#666';
                
                if (currentLength.length === 0) {
                    $counter.html('<span style="color: ' + color + ';">文字数: 0/' + limit + '</span>');
                } else {
                    var longestLine = currentLength.indexOf(maxLength) + 1;
                    $counter.html('<span style="color: ' + color + ';">最長行 ' + longestLine + ': ' + maxLength + '/' + limit + '文字</span>');
                }
            }
            
            // 入力制限機能
            $textarea.on('input', function() {
                var lines = $textarea.val().split('\n');
                var modifiedLines = lines.map(function(line) {
                    if (line.length > limit) {
                        return line.substring(0, limit);
                    }
                    return line;
                });
                
                if (modifiedLines.join('\n') !== $textarea.val()) {
                    $textarea.val(modifiedLines.join('\n'));
                }
                
                updateCounter();
            });
            
            // 初期カウント表示
            updateCounter();
        });
    }
    
    // 設定値変更時にリアルタイムでプレビュー更新
    $('#acb_icon_size, #acb_icon_size_pc, #acb_opacity, #acb_position_left, #acb_position_bottom, #acb_border_radius').on('input', updatePreview);
    
    // 文字数カウンター初期化
    setupCharCounters();
});
</script>