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
            
            // スワイプ機能関連
            this.swipeIndicator = null;
            this.hideIndicator = null;
            this.isSwipeHidden = false;
            this.isTransitioning = false;
            this.touchStartX = 0;
            this.touchStartY = 0;
            this.touchStartTime = 0;
            
            this.init();
        }

        init() {
            // DOMが読み込まれたら開始
            $(document).ready(() => {
                this.setupElement();
                this.setupSpeechBubble();
                this.setupHideFeature();
                this.startAnimationTimer();
                this.setupResizeListener();
            });
        }
        
        // リサイズイベントリスナーを設定
        setupResizeListener() {
            let resizeTimeout;
            
            $(window).on('resize', () => {
                // デバウンシングでパフォーマンスを最適化
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(() => {
                    this.applySettings();
                    this.updateHideFeatureOnResize();
                    console.log('Amazon Corner Buddy: Window resized, updated layout');
                }, 150);
            });
        }

        // リサイズ時の隠し機能更新
        updateHideFeatureOnResize() {
            const isMobile = window.innerWidth <= 768;
            const hideEnabled = this.settings.swipe_hide_enabled !== false;

            if (!hideEnabled) {
                // 隠し機能が無効の場合、隠し状態を解除
                if (this.isSwipeHidden) {
                    this.isSwipeHidden = false;
                    this.element.removeClass('acb-swipe-hidden acb-swipe-transitioning');
                    
                    if (this.swipeIndicator) {
                        this.swipeIndicator.removeClass('acb-show acb-pulse');
                    }
                    
                    if (this.hideIndicator) {
                        this.hideIndicator.removeClass('acb-show acb-pulse');
                    }
                    
                    this.saveHideState();
                    console.log('Amazon Corner Buddy: Hide feature disabled, state reset');
                }
            } else {
                // 隠し機能が有効な場合、再初期化
                this.setupHideFeature();
            }
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

            // アイコンサイズを取得（レスポンシブ対応）
            const iconSizeObj = this.getCurrentIconSize();
            const iconSize = iconSizeObj.width; // 幅を基準にする
            
            // 吹き出しの幅を取得（まだ表示されていない場合は仮表示して取得）
            const wasVisible = this.speechBubble.is(':visible');
            if (!wasVisible) {
                this.speechBubble.css({
                    'visibility': 'hidden',
                    'display': 'block',
                    'opacity': '0'
                });
            }
            
            const bubbleWidth = this.speechBubble.outerWidth() || 200; // フォールバック値を200pxに調整
            const bubbleHeight = this.speechBubble.outerHeight() || 40;
            
            if (!wasVisible) {
                this.speechBubble.css({
                    'visibility': 'visible',
                    'display': 'none',
                    'opacity': ''
                });
            }

            // アイコン左端から吹き出しが始まるように計算
            let leftPosition = -(bubbleWidth - iconSize * 0.1); // アイコン左端から少し内側
            
            // 画面端からのはみ出しを防ぐ
            const iconLeft = parseInt(this.element.css('left')) || 20;
            const minLeft = -iconLeft + 10; // 画面左端から10px余裕
            leftPosition = Math.max(leftPosition, minLeft);
            
            // 複数行対応：吹き出しの高さに応じてtop位置を調整
            let topPosition = -45; // デフォルト位置
            if (bubbleHeight > 30) {
                topPosition = -(bubbleHeight + 15); // 高さ + 余白
            }
            
            // 吹き出しに位置を適用
            this.speechBubble.css({
                'left': leftPosition + 'px',
                'top': topPosition + 'px'
            });
            
            console.log(`Amazon Corner Buddy: Speech bubble position calculated - Icon: ${iconSize}px, Bubble: ${bubbleWidth}x${bubbleHeight}px, Position: left=${leftPosition}px, top=${topPosition}px`);
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

        // バナー隠し機能のセットアップ（PC/モバイル共通）
        setupHideFeature() {
            if (!this.element || this.element.length === 0) return;

            // バナー隠し機能が有効か確認（デフォルトは有効）
            const hideEnabled = this.settings.swipe_hide_enabled !== false;
            if (!hideEnabled) {
                console.log('Amazon Corner Buddy: Hide feature is disabled');
                return;
            }

            // インジケーター作成（PC/モバイル共通）
            this.createSwipeIndicator();
            this.createHideIndicator();
            
            // スワイプジェスチャーはモバイルのみ
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                this.setupSwipeGestures();
                console.log('Amazon Corner Buddy: Mobile swipe gestures enabled');
            } else {
                console.log('Amazon Corner Buddy: PC click controls enabled');
            }
            
            this.loadHideState();
            
            console.log('Amazon Corner Buddy: Hide feature initialized for ' + (isMobile ? 'mobile' : 'PC'));
        }

        // 復帰インジケーター（右矢印）を作成
        createSwipeIndicator() {
            if (this.swipeIndicator && this.swipeIndicator.length > 0) {
                return; // 既に存在する場合は作成しない
            }

            this.swipeIndicator = $('<div id="acb-swipe-indicator"></div>');
            
            // PC版用のツールチップ追加
            const isMobile = window.innerWidth <= 768;
            if (!isMobile) {
                this.swipeIndicator.attr('title', 'クリックでバナーを表示');
                // キーボードアクセシビリティ
                this.swipeIndicator.attr('tabindex', '0');
                this.swipeIndicator.attr('role', 'button');
                this.swipeIndicator.attr('aria-label', 'バナーを表示');
            }
            
            $('body').append(this.swipeIndicator);

            // クリック・タッチイベントを設定
            this.swipeIndicator.on('click touchstart', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showBanner();
            });
            
            // PC版用キーボードイベント
            if (!isMobile) {
                this.swipeIndicator.on('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        e.stopPropagation();
                        this.showBanner();
                    }
                });
            }

            console.log('Amazon Corner Buddy: Restore indicator created for ' + (isMobile ? 'mobile' : 'PC'));
        }

        // 隠すインジケーター（左矢印）を作成
        createHideIndicator() {
            if (this.hideIndicator && this.hideIndicator.length > 0) {
                return; // 既に存在する場合は作成しない
            }

            this.hideIndicator = $('<div id="acb-hide-indicator"></div>');
            
            // PC版用のツールチップ追加
            const isMobile = window.innerWidth <= 768;
            if (!isMobile) {
                this.hideIndicator.attr('title', 'クリックでバナーを隠す');
                // キーボードアクセシビリティ
                this.hideIndicator.attr('tabindex', '0');
                this.hideIndicator.attr('role', 'button');
                this.hideIndicator.attr('aria-label', 'バナーを隠す');
            }
            
            $('body').append(this.hideIndicator);

            // クリック・タッチイベントを設定
            this.hideIndicator.on('click touchstart', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.hideBanner();
            });
            
            // PC版用キーボードイベント
            if (!isMobile) {
                this.hideIndicator.on('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        e.stopPropagation();
                        this.hideBanner();
                    }
                });
            }

            console.log('Amazon Corner Buddy: Hide indicator created for ' + (isMobile ? 'mobile' : 'PC'));
        }

        // スワイプジェスチャーのセットアップ
        setupSwipeGestures() {
            if (!this.element || this.element.length === 0) return;

            // Touch Events
            this.element.on('touchstart', (e) => this.handleTouchStart(e));
            this.element.on('touchmove', (e) => this.handleTouchMove(e));
            this.element.on('touchend', (e) => this.handleTouchEnd(e));

            console.log('Amazon Corner Buddy: Swipe gestures setup completed');
        }

        // タッチ開始処理
        handleTouchStart(e) {
            if (this.isTransitioning || this.isSwipeHidden) return;

            const touch = e.originalEvent.touches[0];
            this.touchStartX = touch.clientX;
            this.touchStartY = touch.clientY;
            this.touchStartTime = Date.now();
        }

        // タッチ移動処理
        handleTouchMove(e) {
            if (this.isTransitioning || this.isSwipeHidden) return;
            
            // デフォルトのスクロール動作を一時的に無効化
            e.preventDefault();
        }

        // タッチ終了処理
        handleTouchEnd(e) {
            if (this.isTransitioning || this.isSwipeHidden) return;

            const touch = e.originalEvent.changedTouches[0];
            const touchEndX = touch.clientX;
            const touchEndY = touch.clientY;
            const touchEndTime = Date.now();

            const deltaX = touchEndX - this.touchStartX;
            const deltaY = touchEndY - this.touchStartY;
            const deltaTime = touchEndTime - this.touchStartTime;

            // スワイプ判定条件
            const minDistance = 50; // 最小スワイプ距離
            const maxTime = 1000; // 最大スワイプ時間（1秒）
            const maxVerticalRatio = 0.5; // 垂直移動の最大比率

            // 左スワイプ判定
            if (deltaX < -minDistance &&
                deltaTime < maxTime &&
                Math.abs(deltaY) < Math.abs(deltaX) * maxVerticalRatio) {
                
                console.log('Amazon Corner Buddy: Left swipe detected');
                this.hideBanner();
            }
        }

        // バナーを隠す
        hideBanner() {
            if (this.isTransitioning || this.isSwipeHidden) return;

            this.isTransitioning = true;
            this.element.addClass('acb-swipe-transitioning');

            // アニメーション停止
            this.removeAllAnimationClasses();
            this.hideSpeechBubble();

            // 左矢印インジケーターを隠す
            if (this.hideIndicator) {
                this.hideIndicator.removeClass('acb-show acb-pulse');
            }

            console.log('Amazon Corner Buddy: Hiding banner');

            // バナーを左に移動
            this.element.addClass('acb-swipe-hidden');

            // 0.4秒後に矢印インジケーターを表示
            setTimeout(() => {
                this.isSwipeHidden = true;
                this.isTransitioning = false;
                this.element.removeClass('acb-swipe-transitioning');
                
                if (this.swipeIndicator) {
                    this.swipeIndicator.addClass('acb-show');
                    // 3秒後にパルス効果を追加（ユーザーに気づいてもらうため）
                    setTimeout(() => {
                        if (this.swipeIndicator && this.swipeIndicator.hasClass('acb-show')) {
                            this.swipeIndicator.addClass('acb-pulse');
                        }
                    }, 3000);
                }

                this.saveSwipeState();
                console.log('Amazon Corner Buddy: Banner hidden, indicator shown');
            }, 400);
        }

        // バナーを表示
        showBanner() {
            if (this.isTransitioning || !this.isSwipeHidden) return;

            this.isTransitioning = true;
            
            console.log('Amazon Corner Buddy: Showing banner');

            // インジケーターを隠す
            if (this.swipeIndicator) {
                this.swipeIndicator.removeClass('acb-show acb-pulse');
            }

            // バナーを右から戻す
            this.element.removeClass('acb-swipe-hidden');
            this.element.addClass('acb-swipe-transitioning');

            // 0.4秒後に状態をリセット
            setTimeout(() => {
                this.isSwipeHidden = false;
                this.isTransitioning = false;
                this.element.removeClass('acb-swipe-transitioning');
                
                // 左矢印インジケーターを表示（バナーが表示されたので隠せることを示す）
                if (this.hideIndicator) {
                    this.hideIndicator.addClass('acb-show');
                    // 3秒後にパルス効果を追加（ユーザーに気づいてもらうため）
                    setTimeout(() => {
                        if (this.hideIndicator && this.hideIndicator.hasClass('acb-show')) {
                            this.hideIndicator.addClass('acb-pulse');
                        }
                    }, 3000);
                }
                
                this.saveSwipeState();
                console.log('Amazon Corner Buddy: Banner shown');
            }, 400);
        }

        // バナー隠し状態を保存
        saveHideState() {
            try {
                sessionStorage.setItem('acb_swipe_hidden', this.isSwipeHidden ? '1' : '0');
            } catch (e) {
                console.warn('Amazon Corner Buddy: Could not save hide state to sessionStorage');
            }
        }

        // バナー隠し状態を読み込み
        loadHideState() {
            try {
                const savedState = sessionStorage.getItem('acb_swipe_hidden');
                if (savedState === '1') {
                    // 隠し状態を復元
                    this.isSwipeHidden = true;
                    this.element.addClass('acb-swipe-hidden');
                    
                    if (this.swipeIndicator) {
                        this.swipeIndicator.addClass('acb-show acb-pulse');
                    }
                    
                    console.log('Amazon Corner Buddy: Restored hidden state from session');
                } else {
                    // バナーが表示状態の場合、左矢印インジケーターを表示
                    if (this.hideIndicator) {
                        this.hideIndicator.addClass('acb-show');
                        // 5秒後にパルス効果を追加（ユーザーに気づいてもらうため）
                        setTimeout(() => {
                            if (this.hideIndicator && this.hideIndicator.hasClass('acb-show')) {
                                this.hideIndicator.addClass('acb-pulse');
                            }
                        }, 5000);
                    }
                    
                    console.log('Amazon Corner Buddy: Hide indicator shown for visible banner');
                }
            } catch (e) {
                console.warn('Amazon Corner Buddy: Could not load hide state from sessionStorage');
            }
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

        // 現在の画面サイズに応じた適切なアイコンサイズを取得（バナー形式4:3対応）
        getCurrentIconSize() {
            const screenWidth = window.innerWidth || document.documentElement.clientWidth;
            
            let width, height;
            
            if (screenWidth <= 480) {
                // 超小型端末用（4:3比）
                width = this.settings.icon_size_mobile_small || 60;
                height = Math.round(width * 0.75); // 4:3比率
            } else if (screenWidth <= 768) {
                // モバイル用（4:3比）
                width = this.settings.icon_size_mobile || 80;
                height = Math.round(width * 0.75); // 4:3比率
            } else {
                // PC用（4:3比）
                width = this.settings.icon_size_pc || 120;
                height = Math.round(width * 0.75); // 4:3比率
            }
            
            return { width, height };
        }

        applySettings() {
            if (!this.element || this.element.length === 0) return;

            const iconSize = this.getCurrentIconSize();
            
            // バナー形式（4:3比）でサイズを適用
            this.element.css({
                'width': iconSize.width + 'px',
                'height': iconSize.height + 'px',
                'opacity': this.settings.opacity,
                'bottom': this.settings.position_bottom + 'px',
                'left': this.settings.position_left + 'px'
            });
            
            console.log(`Amazon Corner Buddy: Applied responsive banner size - Screen: ${window.innerWidth}px, Banner: ${iconSize.width}x${iconSize.height}px`);
            
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

            // スワイプで隠されている場合はアニメーション無効
            if (this.isSwipeHidden) {
                console.log('Amazon Corner Buddy: Animations disabled because banner is hidden');
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

            // スワイプで隠されている場合は吹き出し無効
            if (this.isSwipeHidden) {
                console.log('Amazon Corner Buddy: Speech bubble disabled because banner is hidden');
                return;
            }

            // アクセシビリティ設定チェック
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const messages = this.getGreetingMessages();
            let greetingMsg = messages.greeting[Math.floor(Math.random() * messages.greeting.length)];
            let ctaMsg = messages.cta[Math.floor(Math.random() * messages.cta.length)];
            
            // 25文字制限（フェイルセーフ）
            if (greetingMsg && greetingMsg.length > 25) {
                greetingMsg = greetingMsg.substring(0, 25);
                console.warn('Amazon Corner Buddy: Greeting message trimmed to 25 characters');
            }
            if (ctaMsg && ctaMsg.length > 25) {
                ctaMsg = ctaMsg.substring(0, 25);
                console.warn('Amazon Corner Buddy: CTA message trimmed to 25 characters');
            }

            // 第1段階：挨拶表示
            this.speechBubble.text(greetingMsg);
            this.speechBubble.removeClass('acb-second-phase acb-fade-out acb-pulse');
            this.speechBubble.addClass('acb-show');
            
            // 位置を再計算（メッセージ内容変更により高さが変わる可能性）
            setTimeout(() => {
                this.calculateSpeechBubblePosition();
            }, 50);

            // 第2段階：CTA表示（5秒後）- スムーズなテキスト切り替え
            setTimeout(() => {
                if (this.speechBubble && this.speechBubble.hasClass('acb-show')) {
                    // フェードアウト開始
                    this.speechBubble.addClass('acb-text-fade-out');
                    
                    // フェードアウト完了後にテキスト変更とフェードイン
                    setTimeout(() => {
                        if (this.speechBubble && this.speechBubble.hasClass('acb-show')) {
                            this.speechBubble.text(ctaMsg);
                            this.speechBubble.removeClass('acb-text-fade-out');
                            this.speechBubble.addClass('acb-second-phase acb-pulse acb-text-fade-in');
                            
                            // 位置を再計算（メッセージ内容変更により高さが変わる可能性）
                            setTimeout(() => {
                                this.calculateSpeechBubblePosition();
                            }, 50);
                            
                            // フェードイン完了後にクラス整理
                            setTimeout(() => {
                                if (this.speechBubble) {
                                    this.speechBubble.removeClass('acb-text-fade-in');
                                }
                            }, 150);
                        }
                    }, 150);
                }
            }, 5000);

            // フェードアウト（9秒後）
            setTimeout(() => {
                if (this.speechBubble && this.speechBubble.hasClass('acb-show')) {
                    this.speechBubble.addClass('acb-fade-out');
                    
                    // 完全に消去（1.5秒後）
                    setTimeout(() => {
                        this.speechBubble.removeClass('acb-show acb-second-phase acb-fade-out acb-pulse acb-text-fade-out acb-text-fade-in');
                    }, 1500);
                }
            }, 9000);
        }

        // 吹き出しを手動で非表示
        hideSpeechBubble() {
            if (this.speechBubble && this.speechBubble.length) {
                this.speechBubble.removeClass('acb-show acb-second-phase acb-fade-out acb-pulse acb-text-fade-out acb-text-fade-in');
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

        // 手動でバナー隠し（デバッグ用）
        triggerHideBanner() {
            this.hideBanner();
        }

        // 手動でバナー表示（デバッグ用）
        triggerShowBanner() {
            this.showBanner();
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
        console.log('Amazon Corner Buddy loaded! Available commands:');
        console.log('- AmazonCornerBuddy.triggerRandomAnimation() : ランダムアニメーション実行');
        console.log('- AmazonCornerBuddy.triggerSpeechBubble() : 吹き出し表示');
        console.log('- AmazonCornerBuddy.triggerHideBanner() : バナー隠し');
        console.log('- AmazonCornerBuddy.triggerShowBanner() : バナー表示');
        console.log('- AmazonCornerBuddy.stopAnimations() : アニメーション停止');
        console.log('- AmazonCornerBuddy.resumeAnimations() : アニメーション再開');
    }

})(jQuery);