<?php
/**
 * Amazon Corner Buddy メインクラス
 *
 * @package Amazon_Corner_Buddy
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Amazon_Corner_Buddy {
    
    /**
     * シングルトンインスタンス
     */
    private static $instance = null;
    
    /**
     * プラグイン設定
     */
    private $options;
    
    /**
     * シングルトンインスタンスを取得
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        $this->options = get_option('acb_options', array());
        $this->init_hooks();
    }
    
    /**
     * フックの初期化
     */
    private function init_hooks() {
        // フロントエンドでのスクリプト・スタイル読み込み
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        
        // フロントエンドでのアイコン表示
        add_action('wp_footer', array($this, 'render_corner_buddy'));
        
        // 管理画面のメニュー追加
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // 管理画面でのスクリプト・スタイル読み込み
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // 設定の保存処理
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX処理（将来の拡張用）
        add_action('wp_ajax_acb_save_settings', array($this, 'ajax_save_settings'));
    }
    
    /**
     * フロントエンド用のアセットを読み込み
     */
    public function enqueue_frontend_assets() {
        // プラグインが有効かチェック
        if (!$this->is_enabled()) {
            return;
        }
        
        // CSS読み込み
        wp_enqueue_style(
            'acb-frontend-style',
            ACB_PLUGIN_URL . 'assets/css/corner-buddy.css',
            array(),
            ACB_VERSION
        );
        
        // JavaScript読み込み
        wp_enqueue_script(
            'acb-frontend-script',
            ACB_PLUGIN_URL . 'assets/js/corner-buddy.js',
            array('jquery'),
            ACB_VERSION,
            true
        );
        
        // デフォルトのカスタムメッセージ
        $default_custom_messages = array(
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
        
        // カスタムメッセージ設定を取得
        $custom_messages = $this->get_option('custom_messages', $default_custom_messages);
        
        // 空の配列の場合はデフォルトを使用
        foreach ($default_custom_messages as $time => $messages) {
            foreach ($messages as $type => $default_array) {
                if (empty($custom_messages[$time][$type])) {
                    $custom_messages[$time][$type] = $default_array;
                }
            }
        }

        // JavaScriptに設定値を渡す
        wp_localize_script('acb-frontend-script', 'acb_vars', array(
            'animation_interval' => $this->get_option('animation_interval', 10) * 1000, // ミリ秒に変換
            'icon_size' => $this->get_option('icon_size', 48),
            'icon_size_pc' => $this->get_option('icon_size_pc', 64),
            'opacity' => $this->get_option('opacity', 0.8),
            'position_bottom' => $this->get_option('position_bottom', 20),
            'position_left' => $this->get_option('position_left', 20),
            'border_radius' => $this->get_option('border_radius', 12),
            'icon_url' => ACB_PLUGIN_URL . 'assets/images/prime-day.png',
            'link_url' => $this->get_option('link_url', 'https://amzn.to/446mmWI'),
            'speech_bubble_enabled' => $this->get_option('speech_bubble_enabled', true),
            'speech_bubble_frequency' => $this->get_option('speech_bubble_frequency', 3),
            'custom_messages' => $custom_messages
        ));
    }
    
    /**
     * 管理画面用のアセットを読み込み
     */
    public function enqueue_admin_assets($hook) {
        // 設定ページでのみ読み込み
        if ('settings_page_amazon-corner-buddy' !== $hook) {
            return;
        }
        
        wp_enqueue_style(
            'acb-admin-style',
            ACB_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            ACB_VERSION
        );
    }
    
    /**
     * Corner Buddyアイコンをレンダリング
     */
    public function render_corner_buddy() {
        if (!$this->is_enabled()) {
            return;
        }
        
        $icon_size = $this->get_option('icon_size', 48);
        $opacity = $this->get_option('opacity', 0.8);
        $bottom = $this->get_option('position_bottom', 20);
        $left = $this->get_option('position_left', 20);
        $border_radius = $this->get_option('border_radius', 12);
        
        $link_url = $this->get_option('link_url', 'https://amzn.to/446mmWI');
        
        echo '<a id="acb-corner-buddy" href="' . esc_url($link_url) . '" target="_blank" rel="noopener noreferrer" style="
            position: fixed;
            bottom: ' . intval($bottom) . 'px;
            left: ' . intval($left) . 'px;
            width: ' . intval($icon_size) . 'px;
            height: ' . intval($icon_size) . 'px;
            opacity: ' . floatval($opacity) . ';
            border-radius: ' . intval($border_radius) . 'px;
            z-index: 9999;
            display: block;
            text-decoration: none;
            border: none;
            outline: none;
            transition: all 0.3s ease;
            cursor: pointer;
        ">
            <img src="' . esc_url(ACB_PLUGIN_URL . 'assets/images/prime-day.png') . '" 
                 alt="Amazon Corner Buddy" 
                 style="width: 100%; height: 100%; object-fit: contain; border: none; display: block;">
        </a>';
    }
    
    /**
     * 管理画面メニューを追加
     */
    public function add_admin_menu() {
        add_options_page(
            'Amazon Corner Buddy 設定',
            'Amazon Corner Buddy',
            'manage_options',
            'amazon-corner-buddy',
            array($this, 'admin_page')
        );
    }
    
    /**
     * 管理画面ページの表示
     */
    public function admin_page() {
        require_once ACB_PLUGIN_DIR . 'admin/admin-settings.php';
    }
    
    /**
     * 設定の登録
     */
    public function register_settings() {
        register_setting('acb_settings', 'acb_options', array($this, 'sanitize_options'));
        
        add_settings_section(
            'acb_general_section',
            '基本設定',
            array($this, 'general_section_callback'),
            'acb_settings'
        );
        
        // 有効/無効
        add_settings_field(
            'enabled',
            'プラグインを有効にする',
            array($this, 'enabled_field_callback'),
            'acb_settings',
            'acb_general_section'
        );
        
        // アニメーション間隔
        add_settings_field(
            'animation_interval',
            'アニメーション間隔（秒）',
            array($this, 'animation_interval_field_callback'),
            'acb_settings',
            'acb_general_section'
        );
        
        // アイコンサイズ
        add_settings_field(
            'icon_size',
            'アイコンサイズ（px）',
            array($this, 'icon_size_field_callback'),
            'acb_settings',
            'acb_general_section'
        );
        
        // 透明度
        add_settings_field(
            'opacity',
            '透明度',
            array($this, 'opacity_field_callback'),
            'acb_settings',
            'acb_general_section'
        );
        
        // 角丸設定
        add_settings_field(
            'border_radius',
            '角丸の大きさ（px）',
            array($this, 'border_radius_field_callback'),
            'acb_settings',
            'acb_general_section'
        );
        
        // リンクURL
        add_settings_field(
            'link_url',
            'リンクURL',
            array($this, 'link_url_field_callback'),
            'acb_settings',
            'acb_general_section'
        );
    }
    
    /**
     * 設定値のサニタイズ
     */
    public function sanitize_options($input) {
        $sanitized = array();
        
        $sanitized['enabled'] = isset($input['enabled']) ? (bool) $input['enabled'] : false;
        $sanitized['animation_interval'] = max(1, min(60, intval($input['animation_interval'])));
        $sanitized['icon_size'] = max(20, min(100, intval($input['icon_size'])));
        $sanitized['icon_size_pc'] = max(30, min(150, intval($input['icon_size_pc'])));
        $sanitized['opacity'] = max(0.1, min(1.0, floatval($input['opacity'])));
        $sanitized['position_bottom'] = max(0, min(500, intval($input['position_bottom'])));
        $sanitized['position_left'] = max(0, min(500, intval($input['position_left'])));
        $sanitized['border_radius'] = max(0, min(50, intval($input['border_radius'])));
        $sanitized['link_url'] = esc_url_raw($input['link_url']);
        $sanitized['speech_bubble_enabled'] = isset($input['speech_bubble_enabled']) ? (bool) $input['speech_bubble_enabled'] : false;
        $sanitized['speech_bubble_frequency'] = max(1, min(5, intval($input['speech_bubble_frequency'])));
        
        // カスタムメッセージのサニタイズ
        if (isset($input['custom_messages']) && is_array($input['custom_messages'])) {
            $sanitized['custom_messages'] = array();
            $time_periods = array('morning', 'afternoon', 'evening', 'night');
            $message_types = array('greeting', 'cta');
            
            foreach ($time_periods as $time) {
                if (isset($input['custom_messages'][$time]) && is_array($input['custom_messages'][$time])) {
                    foreach ($message_types as $type) {
                        if (isset($input['custom_messages'][$time][$type]) && is_array($input['custom_messages'][$time][$type])) {
                            $sanitized['custom_messages'][$time][$type] = array_map('sanitize_text_field', $input['custom_messages'][$time][$type]);
                        }
                    }
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * プラグインが有効かどうかをチェック
     */
    private function is_enabled() {
        return $this->get_option('enabled', true);
    }
    
    /**
     * 設定値を取得
     */
    private function get_option($key, $default = null) {
        return isset($this->options[$key]) ? $this->options[$key] : $default;
    }
    
    // 設定フィールドのコールバック関数
    public function general_section_callback() {
        echo '<p>Amazon Corner Buddyの動作を設定できます。</p>';
    }
    
    public function enabled_field_callback() {
        $enabled = $this->get_option('enabled', true);
        echo '<input type="checkbox" name="acb_options[enabled]" value="1" ' . checked(1, $enabled, false) . '>';
        echo '<label> アイコンを表示する</label>';
    }
    
    public function animation_interval_field_callback() {
        $interval = $this->get_option('animation_interval', 10);
        echo '<input type="number" name="acb_options[animation_interval]" value="' . esc_attr($interval) . '" min="1" max="60">';
        echo '<p class="description">1-60秒の間で設定してください。</p>';
    }
    
    public function icon_size_field_callback() {
        $size = $this->get_option('icon_size', 48);
        echo '<input type="number" name="acb_options[icon_size]" value="' . esc_attr($size) . '" min="20" max="100">';
        echo '<p class="description">20-100pxの間で設定してください。</p>';
    }
    
    public function opacity_field_callback() {
        $opacity = $this->get_option('opacity', 0.8);
        echo '<input type="number" name="acb_options[opacity]" value="' . esc_attr($opacity) . '" min="0.1" max="1.0" step="0.1">';
        echo '<p class="description">0.1-1.0の間で設定してください。</p>';
    }
    
    public function border_radius_field_callback() {
        $border_radius = $this->get_option('border_radius', 12);
        echo '<input type="number" name="acb_options[border_radius]" value="' . esc_attr($border_radius) . '" min="0" max="50">';
        echo '<p class="description">アイコンの角丸の大きさを設定します（0-50px）。0で角丸なし。</p>';
    }
    
    public function link_url_field_callback() {
        $link_url = $this->get_option('link_url', 'https://amzn.to/446mmWI');
        echo '<input type="url" name="acb_options[link_url]" value="' . esc_attr($link_url) . '" style="width: 400px;">';
        echo '<p class="description">アイコンクリック時のリンク先URLを設定してください。</p>';
    }
    
    /**
     * AJAX設定保存処理
     */
    public function ajax_save_settings() {
        check_ajax_referer('acb_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('権限がありません。'));
        }
        
        // 今後の拡張用
        wp_die();
    }
}