/**
 * Amazon Corner Buddy JavaScript
 * 10秒間隔でランダムアニメーションを実行
 */

(function($) {
    'use strict';

    // アニメーションクラスの配列
    const animations = [
        'acb-animation-shake',
        'acb-animation-bounce', 
        'acb-animation-wiggle',
        'acb-animation-pulse',
        'acb-animation-rotate',
        'acb-animation-float'
    ];

    // アニメーション実行中フラグ
    let isAnimating = false;

    // Corner Buddyクラス
    class AmazonCornerBuddy {
        constructor() {
            this.element = null;
            this.animationTimer = null;
            this.settings = {};
            
            this.init();
        }

        init() {
            // DOMが読み込まれたら開始
            $(document).ready(() => {
                this.setupElement();
                this.startAnimationTimer();
            });
        }

        setupElement() {
            this.element = $('#acb-corner-buddy');
            
            if (this.element.length === 0) {
                console.log('Amazon Corner Buddy: Element not found');
                return;
            }

            // 設定値を取得（WordPressから渡される）
            if (typeof acb_vars !== 'undefined') {
                this.settings = acb_vars;
                this.applySettings();
            }

            console.log('Amazon Corner Buddy: Initialized successfully');
        }

        applySettings() {
            if (!this.element || this.element.length === 0) return;

            // 設定を適用
            this.element.css({
                'width': this.settings.icon_size + 'px',
                'height': this.settings.icon_size + 'px',
                'opacity': this.settings.opacity,
                'bottom': this.settings.position_bottom + 'px',
                'left': this.settings.position_left + 'px'
            });
        }

        startAnimationTimer() {
            if (!this.element || this.element.length === 0) return;

            // 設定から間隔を取得（デフォルト10秒）
            const interval = this.settings.animation_interval || 10000;

            // 最初のアニメーションは開始から少し遅らせる（3-7秒後）
            const initialDelay = 3000 + Math.random() * 4000;

            setTimeout(() => {
                this.performRandomAnimation();
                
                // 定期的なアニメーション実行
                this.animationTimer = setInterval(() => {
                    this.performRandomAnimation();
                }, interval);
            }, initialDelay);
        }

        performRandomAnimation() {
            if (isAnimating || !this.element || this.element.length === 0) {
                return;
            }

            // アクセシビリティ設定チェック
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                console.log('Amazon Corner Buddy: Animations disabled due to accessibility settings');
                return;
            }

            isAnimating = true;

            // ランダムアニメーション選択
            const randomAnimation = animations[Math.floor(Math.random() * animations.length)];
            
            console.log('Amazon Corner Buddy: Playing animation -', randomAnimation);

            // 既存のアニメーションクラスを削除
            this.removeAllAnimationClasses();

            // 新しいアニメーションクラスを追加
            this.element.addClass(randomAnimation);

            // アニメーション完了後にクラスを削除
            this.element.one('animationend', () => {
                this.element.removeClass(randomAnimation);
                isAnimating = false;
            });

            // セーフティタイマー（アニメーションイベントが発火しない場合）
            setTimeout(() => {
                if (isAnimating) {
                    this.element.removeClass(randomAnimation);
                    isAnimating = false;
                }
            }, 3000);
        }

        removeAllAnimationClasses() {
            animations.forEach(animationClass => {
                this.element.removeClass(animationClass);
            });
        }

        // 手動でアニメーション実行（デバッグ用）
        triggerRandomAnimation() {
            this.performRandomAnimation();
        }

        // アニメーション停止
        stopAnimations() {
            if (this.animationTimer) {
                clearInterval(this.animationTimer);
                this.animationTimer = null;
            }
            this.removeAllAnimationClasses();
            isAnimating = false;
        }

        // アニメーション再開
        resumeAnimations() {
            this.stopAnimations();
            this.startAnimationTimer();
        }

        // 設定更新
        updateSettings(newSettings) {
            this.settings = { ...this.settings, ...newSettings };
            this.applySettings();
            
            // タイマーを再設定
            if (newSettings.animation_interval) {
                this.resumeAnimations();
            }
        }
    }

    // インスタンス作成
    const cornerBuddy = new AmazonCornerBuddy();

    // グローバルオブジェクトとして公開（デバッグ用）
    window.AmazonCornerBuddy = cornerBuddy;

    // ページの可視性API対応（タブが非アクティブになったらアニメーション停止）
    if (typeof document.hidden !== 'undefined') {
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // ページが隠れた時はアニメーション一時停止
                if (cornerBuddy.animationTimer) {
                    clearInterval(cornerBuddy.animationTimer);
                }
            } else {
                // ページが表示された時はアニメーション再開
                if (!cornerBuddy.animationTimer) {
                    cornerBuddy.startAnimationTimer();
                }
            }
        });
    }

    // ウィンドウフォーカス/ブラーイベント対応
    $(window).on('focus', function() {
        if (!cornerBuddy.animationTimer) {
            cornerBuddy.startAnimationTimer();
        }
    });

    $(window).on('blur', function() {
        // フォーカス失った時は一時停止
        if (cornerBuddy.animationTimer) {
            clearInterval(cornerBuddy.animationTimer);
            cornerBuddy.animationTimer = null;
        }
    });

    // デバッグ用コンソールコマンド
    if (typeof console !== 'undefined') {
        console.log('Amazon Corner Buddy loaded! Use AmazonCornerBuddy.triggerRandomAnimation() to test animations.');
    }

})(jQuery);