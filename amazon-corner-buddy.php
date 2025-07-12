<?php
/**
 * Plugin Name: Amazon Corner Buddy
 * Plugin URI: https://github.com/amazon-corner-buddy
 * Description: ページの左下角でAmazonアイコンが密かにアニメーションする可愛いプラグイン
 * Version: 2.2.0
 * Author: buchi
 * Author URI: https://github.com/amazon-corner-buddy
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: amazon-corner-buddy
 * Domain Path: /languages
 */

// このファイルが直接アクセスされることを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// プラグイン定数を定義
define('ACB_VERSION', '2.2.0');
define('ACB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACB_PLUGIN_FILE', __FILE__);

// メインクラスの読み込み
require_once ACB_PLUGIN_DIR . 'includes/class-amazon-corner-buddy.php';

// プラグインの初期化
function acb_init() {
    // プラグインクラスのインスタンスを作成
    Amazon_Corner_Buddy::get_instance();
}
add_action('plugins_loaded', 'acb_init');

// プラグインアクティベーション時の処理
function acb_activation() {
    // デフォルト設定を保存
    $default_options = array(
        'enabled' => true,
        'animation_interval' => 10,
        'icon_size_pc' => 120,
        'icon_size_mobile' => 80,
        'icon_size_mobile_small' => 60,
        'opacity' => 0.8,
        'position_bottom' => 20,
        'position_left' => 20,
        'border_radius' => 12,
        'link_url' => 'https://amzn.to/446mmWI',
        'speech_bubble_enabled' => true,
        'speech_bubble_frequency' => 3,
        'swipe_hide_enabled' => true
    );
    
    $existing_options = get_option('acb_options', array());
    
    if (empty($existing_options)) {
        // 新規インストール：デフォルト設定を保存
        add_option('acb_options', $default_options);
    } else {
        // 既存インストール：設定移行処理
        $updated = false;
        
        // 古いicon_sizeフィールドがあり、新しいフィールドがない場合は移行
        if (isset($existing_options['icon_size']) && !isset($existing_options['icon_size_mobile'])) {
            $existing_options['icon_size_mobile'] = $existing_options['icon_size'];
            $updated = true;
        }
        
        // 新しいフィールドにデフォルト値を設定（存在しない場合）
        foreach ($default_options as $key => $value) {
            if (!isset($existing_options[$key])) {
                $existing_options[$key] = $value;
                $updated = true;
            }
        }
        
        // 古いフィールドを削除
        if (isset($existing_options['icon_size'])) {
            unset($existing_options['icon_size']);
            $updated = true;
        }
        
        // 更新が必要な場合のみ保存
        if ($updated) {
            update_option('acb_options', $existing_options);
        }
    }
}
register_activation_hook(__FILE__, 'acb_activation');

// プラグイン無効化時の処理
function acb_deactivation() {
    // 特に処理なし（設定は保持）
}
register_deactivation_hook(__FILE__, 'acb_deactivation');

// プラグインアンインストール時の処理
function acb_uninstall() {
    // 設定を削除
    delete_option('acb_options');
}
register_uninstall_hook(__FILE__, 'acb_uninstall');