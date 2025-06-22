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
            this.speechBubble = null;
            this.animationCount = 0;
            this.speechBubbleTimer = null;
            
            this.init();
        }

        init() {
            // DOMが読み込まれたら開始
            $(document).ready(() => {
                this.setupElement();
                this.setupSpeechBubble();
                this.startAnimationTimer();
            });
        }

        // 時間帯別挨拶メッセージ定義
        getGreetingMessages() {
            const hour = new Date().getHours();
            
            // デフォルトメッセージ（設定が取得できない場合のフォールバック）
            const defaultMessages = {
                morning: {
                    greeting: ["おはようございます☀️", "素敵な一日の始まりですね", "今日も頑張りましょう✨"],
                    cta: ["お得な商品をチェック！", "新商品が入荷しています", "朝の特別セールあります"]
                },
                afternoon: {
                    greeting: ["こんにちは😊", "お疲れ様です", "午後もお疲れ様"],
                    cta: ["ランチタイムセール中！", "お買い物はお済みですか？", "今だけ特別価格です"]
                },
                evening: {
                    greeting: ["お疲れ様です🌅", "夕方になりましたね", "今日もお疲れ様でした"],
                    cta: ["帰宅前にチェック！", "夜のお得情報あります", "限定セール開催中"]
                },
                night: {
                    greeting: ["今日もお疲れ様でした🌙", "おつかれさまです", "ゆっくりお過ごしください"],
                    cta: ["お買い物は済みましたか？", "夜のタイムセール中！", "明日の準備はいかがですか？"]
                }
            };
            
            // 設定からカスタムメッセージを取得
            const customMessages = this.settings.custom_messages || defaultMessages;
            
            let timeOfDay;
            if (hour >= 5 && hour < 10) {
                timeOfDay = 'morning';
            } else if (hour >= 10 && hour < 15) {
                timeOfDay = 'afternoon';
            } else if (hour >= 15 && hour < 19) {
                timeOfDay = 'evening';
            } else {
                timeOfDay = 'night';
            }
            
            // カスタムメッセージが存在し、配列が空でない場合はそれを使用、そうでなければデフォルトを使用
            const messages = customMessages[timeOfDay] || defaultMessages[timeOfDay];
            const greeting = (messages.greeting && messages.greeting.length > 0) ? messages.greeting : defaultMessages[timeOfDay].greeting;
            const cta = (messages.cta && messages.cta.length > 0) ? messages.cta : defaultMessages[timeOfDay].cta;
            
            return {
                greeting: greeting,
                cta: cta
            };
        }

        // 吹き出しの位置を動的計算
        calculateSpeechBubblePosition() {
            if (!this.speechBubble || !this.speechBubble.length || !this.element || !this.element.length) {
                return;
            }

            // アイコンサイズを取得（設定値またはCSS値）
            const iconSize = this.settings.icon_size || parseInt(this.element.width()) || 50;
            
            // 吹き出しの幅を取得（まだ表示されていない場合は仮表示して取得）
            const wasVisible = this.speechBubble.is(':visible');
            if (!wasVisible) {
                this.speechBubble.css({
                    'visibility': 'hidden',
                    'display': 'block'
                });
            }
            
            const bubbleWidth = this.speechBubble.outerWidth() || 180; // フォールバック値
            
            if (!wasVisible) {
                this.speechBubble.css({
                    'visibility': 'visible',
                    'display': 'none'
                });
            }

            // アイコン左端から吹き出しが始まるように計算
            // 吹き出し幅 - アイコン幅 = 左にずらす距離
            let leftPosition = -(bubbleWidth - iconSize * 0.1); // アイコン左端から少し内側
            
            // 画面端からのはみ出しを防ぐ
            const iconLeft = parseInt(this.element.css('left')) || 20;
            const minLeft = -iconLeft + 10; // 画面左端から10px余裕
            leftPosition = Math.max(leftPosition, minLeft);
            
            // 吹き出しに位置を適用
            this.speechBubble.css('left', leftPosition + 'px');
            
            console.log(`Amazon Corner Buddy: Speech bubble position calculated - Icon: ${iconSize}px, Bubble: ${bubbleWidth}px, Left: ${leftPosition}px`);
        }

        // 吹き出し要素のセットアップ
        setupSpeechBubble() {
            if (!this.element || this.element.length === 0) return;

            // 吹き出し要素を作成
            this.speechBubble = $('<div id="acb-speech-bubble"></div>');
            this.element.append(this.speechBubble);
            
            // 位置を計算
            setTimeout(() => {
                this.calculateSpeechBubblePosition();
            }, 100); // DOM更新後に実行
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
            
            // 吹き出し位置を再計算（アイコンサイズ変更時）
            setTimeout(() => {
                this.calculateSpeechBubblePosition();
            }, 50); // CSS適用後に実行
        }

        startAnimationTimer() {
            if (!this.element || this.element.length === 0) return;

            // 既存のタイマーをクリア（重複防止）
            if (this.animationTimer) {
                clearInterval(this.animationTimer);
                this.animationTimer = null;
                console.log('Amazon Corner Buddy: Cleared existing timer before starting new one');
            }

            // 設定から間隔を取得（デフォルト10秒）
            const interval = this.settings.animation_interval || 10000;

            // 最初のアニメーションは開始から少し遅らせる（3-7秒後）
            const initialDelay = 3000 + Math.random() * 4000;

            console.log(`Amazon Corner Buddy: Starting animation timer with ${interval}ms interval`);

            setTimeout(() => {
                this.performRandomAnimation();
                
                // 定期的なアニメーション実行
                this.animationTimer = setInterval(() => {
                    this.performRandomAnimation();
                }, interval);
                console.log('Amazon Corner Buddy: Animation timer started successfully');
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
            this.animationCount++;

            // ランダムアニメーション選択
            const randomAnimation = animations[Math.floor(Math.random() * animations.length)];
            
            console.log(`Amazon Corner Buddy: Playing animation ${this.animationCount} - ${randomAnimation}`);

            // 既存のアニメーションクラスを削除
            this.removeAllAnimationClasses();

            // 新しいアニメーションクラスを追加
            this.element.addClass(randomAnimation);

            // 設定された頻度で吹き出しを表示（設定で無効の場合は表示しない）
            const speechBubbleEnabled = this.settings.speech_bubble_enabled !== false;
            const frequency = this.settings.speech_bubble_frequency || 3;
            const shouldShowBubble = speechBubbleEnabled && (this.animationCount % frequency === 0);
            
            console.log(`Amazon Corner Buddy: Speech bubble check - Count: ${this.animationCount}, Frequency: ${frequency}, Enabled: ${speechBubbleEnabled}, Should show: ${shouldShowBubble}`);
            
            if (shouldShowBubble) {
                // アニメーション開始から少し遅れて吹き出し表示
                setTimeout(() => {
                    this.showSpeechBubble();
                }, 500);
                console.log('Amazon Corner Buddy: Speech bubble will be displayed');
            }

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

        // ２段階吹き出し表示
        showSpeechBubble() {
            if (!this.speechBubble || !this.speechBubble.length) return;

            // アクセシビリティ設定チェック
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const messages = this.getGreetingMessages();
            const greetingMsg = messages.greeting[Math.floor(Math.random() * messages.greeting.length)];
            const ctaMsg = messages.cta[Math.floor(Math.random() * messages.cta.length)];

            // 第1段階：挨拶表示
            this.speechBubble.text(greetingMsg);
            this.speechBubble.removeClass('acb-second-phase acb-fade-out acb-pulse');
            this.speechBubble.addClass('acb-show');

            // 第2段階：CTA表示（3秒後）
            setTimeout(() => {
                if (this.speechBubble && this.speechBubble.hasClass('acb-show')) {
                    this.speechBubble.text(ctaMsg);
                    this.speechBubble.addClass('acb-second-phase acb-pulse');
                }
            }, 3000);

            // フェードアウト（5秒後）
            setTimeout(() => {
                if (this.speechBubble && this.speechBubble.hasClass('acb-show')) {
                    this.speechBubble.addClass('acb-fade-out');
                    
                    // 完全に消去（1.5秒後）
                    setTimeout(() => {
                        this.speechBubble.removeClass('acb-show acb-second-phase acb-fade-out acb-pulse');
                    }, 1500);
                }
            }, 5000);
        }

        // 吹き出しを手動で非表示
        hideSpeechBubble() {
            if (this.speechBubble && this.speechBubble.length) {
                this.speechBubble.removeClass('acb-show acb-second-phase acb-fade-out acb-pulse');
            }
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

        // 手動で吹き出し表示（デバッグ用）
        triggerSpeechBubble() {
            this.showSpeechBubble();
        }

        // アニメーション停止
        stopAnimations() {
            if (this.animationTimer) {
                clearInterval(this.animationTimer);
                this.animationTimer = null;
                console.log('Amazon Corner Buddy: All animations stopped');
            }
            this.removeAllAnimationClasses();
            this.hideSpeechBubble();
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
            
            // 吹き出し位置を再計算（設定変更時）
            setTimeout(() => {
                this.calculateSpeechBubblePosition();
            }, 100);
            
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
                    cornerBuddy.animationTimer = null; // ← 重要：nullを設定
                    console.log('Amazon Corner Buddy: Animation paused (visibility hidden)');
                }
            } else {
                // ページが表示された時はアニメーション再開
                if (!cornerBuddy.animationTimer) {
                    cornerBuddy.startAnimationTimer();
                    console.log('Amazon Corner Buddy: Animation resumed (visibility visible)');
                }
            }
        });
    }

    // ウィンドウフォーカス/ブラーイベント対応
    $(window).on('focus', function() {
        if (!cornerBuddy.animationTimer) {
            cornerBuddy.startAnimationTimer();
            console.log('Amazon Corner Buddy: Animation resumed (window focus)');
        }
    });

    $(window).on('blur', function() {
        // フォーカス失った時は一時停止
        if (cornerBuddy.animationTimer) {
            clearInterval(cornerBuddy.animationTimer);
            cornerBuddy.animationTimer = null; // ← 重要：nullを設定
            console.log('Amazon Corner Buddy: Animation paused (window blur)');
        }
    });

    // デバッグ用コンソールコマンド
    if (typeof console !== 'undefined') {
        console.log('Amazon Corner Buddy loaded! Use AmazonCornerBuddy.triggerRandomAnimation() to test animations.');
    }

})(jQuery);