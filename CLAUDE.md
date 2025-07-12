# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Amazon Corner Buddy は、WordPress サイトの左下角にAmazonバナー（prime-day.png、480×360px）を表示し、10秒間隔でランダムなアニメーションを実行するプラグインです。Ver2.2.0でレスポンシブサイズ管理システムと設定移行機能を追加しました。

## アーキテクチャ

### ファイル構成
- `amazon-corner-buddy.php` - メインプラグインファイル、定数定義とフック登録
- `includes/class-amazon-corner-buddy.php` - メインクラス（シングルトンパターン）
- `admin/admin-settings.php` - 管理画面の設定UI
- `assets/css/corner-buddy.css` - 6種類のアニメーション定義とスタイル
- `assets/js/corner-buddy.js` - アニメーション制御ロジック
- `assets/images/prime-day.png` - Amazon規約準拠のバナー画像（480×360px、4:3アスペクト比）

### 設計パターン
- **シングルトンパターン**: `Amazon_Corner_Buddy::get_instance()` でメインクラスのインスタンス管理
- **WordPressフック**: `wp_enqueue_scripts`, `wp_footer`, `admin_menu` 等を使用
- **設定API**: `register_setting()` を使用した設定管理

## 主要機能

### アニメーション
- 6種類のアニメーション: Shake, Bounce, Wiggle, Pulse, Rotate, Float
- アクセシビリティ対応: `prefers-reduced-motion` メディアクエリに対応
- パフォーマンス重視: タブ非アクティブ時はアニメーション停止

### 設定項目
- プラグイン有効/無効切り替え
- アニメーション間隔（1-60秒）
- **レスポンシブバナーサイズ（4:3アスペクト比自動計算）**
  - **PC用サイズ（769px以上）**: 幅60-200px（デフォルト120px）
  - **モバイル用サイズ（480-768px）**: 幅40-150px（デフォルト80px）
  - **超小型端末（-480px）**: 幅30-150px（デフォルト60px）
- 透明度（0.1-1.0）
- 位置調整（左端・下端からの距離）
- リンクURL設定
- バナー隠し機能（PC/モバイル共通）: 有効/無効切り替え
- 吹き出しメッセージ機能: 時間帯別カスタムメッセージ（25文字制限）

## 開発コマンド

### WordPressプラグインとしてのテスト
```bash
# WordPressローカル環境でプラグインをテスト
# wp-content/plugins/ ディレクトリにシンボリックリンクを作成するか、
# プラグインファイルを直接配置してWordPress管理画面から有効化
```

### コードスタイル・デバッグ
```bash
# PHPの構文チェック
php -l amazon-corner-buddy.php
php -l includes/class-amazon-corner-buddy.php
php -l admin/admin-settings.php

# JavaScriptの動作確認
# ブラウザコンソールで以下のコマンドが使用可能:
# AmazonCornerBuddy.triggerRandomAnimation()      # ランダムアニメーション実行
# AmazonCornerBuddy.triggerSpeechBubble()         # 吹き出し表示
# AmazonCornerBuddy.triggerHideBanner()           # バナー隠し（PC/モバイル）
# AmazonCornerBuddy.triggerShowBanner()           # バナー表示（PC/モバイル）
# AmazonCornerBuddy.stopAnimations()              # アニメーション停止
# AmazonCornerBuddy.resumeAnimations()            # アニメーション再開
```

## 重要な実装詳細

### データ永続化
- 設定は `acb_options` オプション名でWordPressデータベースに保存
- アクティベーション時にデフォルト設定を自動作成
- アンインストール時に設定を完全削除

### セキュリティ
- 直接アクセス防止: `!defined('ABSPATH')` チェック
- 設定値のサニタイズ: `sanitize_options()` メソッドで入力値検証
- ナンス検証: 管理画面フォームで `wp_nonce_field()` 使用
- URLエスケープ: `esc_url()`, `esc_attr()` 等を適切に使用

### フロントエンド
- jQueryを使用（WordPressバンドル版に依存）
- `wp_localize_script()` でPHPからJavaScriptに設定値を渡す
- Page Visibility API対応でパフォーマンス最適化

### CSS設計
- 他プラグインとの競合を避ける専用クラス名（`acb-`プレフィックス）
- `!important` を使用して他のスタイルに影響されない実装
- **動的サイズ設定**: CSS固定サイズからJavaScript動的制御に変更（v2.2.0）
- レスポンシブ対応（PC/モバイル/超小型端末）
- バナー形式（4:3アスペクト比）に最適化されたサイズ設定
- スワイプインジケーター（矢印マーク）のCSS実装

### バナー隠し機能（v2.3.0でPC対応）
- **PC版操作**: クリック・キーボード（Tab/Enter/Space）でインジケーター操作
- **モバイル版操作**: スワイプジェスチャー + インジケータークリック
- **Touch Events**: touchstart, touchmove, touchend でジェスチャー検出（モバイルのみ）
- **スワイプ判定**: 水平50px以上移動、1秒以内、垂直移動の比率制限（モバイルのみ）
- **状態管理**: sessionStorage による状態保持（ページ間維持）
- **右矢印インジケーター（復帰用）**: バナー隠し時に表示、復帰ボタン
- **左矢印インジケーター（隠し用）**: バナー表示時に表示、隠せることを示すヒント
- **アニメーション**: 0.4秒のスムーズなトランジション、cubic-bezier カーブ
- **アクセシビリティ**: ツールチップ、ARIAラベル、キーボードフォーカス対応

## 一般的な修正パターン

### 新しいアニメーションの追加
1. `assets/css/corner-buddy.css` に新しい `@keyframes` と `.acb-animation-*` クラスを追加
2. `assets/js/corner-buddy.js` の `animations` 配列に新しいクラス名を追加

### 設定項目の追加
1. `includes/class-amazon-corner-buddy.php` の `register_settings()` で新しいフィールドを追加
2. `admin/admin-settings.php` の管理画面UIに対応するHTML要素を追加
3. `sanitize_options()` メソッドで新しい設定のバリデーション追加
4. JavaScriptに新しい設定値を渡すため `wp_localize_script()` に追加

### バナーサイズの調整（v2.2.0更新）
- **管理画面設定**: PC/モバイル/超小型端末の個別サイズ設定が可能
- **JavaScriptによる動的制御**: `getCurrentIconSize()` メソッドでレスポンシブサイズ計算
- **4:3アスペクト比自動計算**: 幅設定に基づき高さを自動計算（`width × 0.75`）
- **CSS側**: 固定サイズを削除し、JavaScript動的設定を優先
- 矢印インジケーターの位置もサイズに合わせて調整が必要

### WordPress要件
- PHP 7.4以上
- WordPress 5.0以上
- jQueryライブラリ（WordPressコア依存）

## バージョン情報
- 現在のバージョン: 2.3.1

## 最新の更新内容（v2.3.1）
### 🐛 バグ修正: モバイル版インジケーター重複表示問題の解決
- **問題の解決**: バナー隠し後に左向きインジケーター（≪）が残って右向きインジケーター（≫）と重複表示される問題を修正
- **根本原因**: `saveSwipeState()` 関数が存在せず状態保存が失敗していた問題を解決
- **関数名統一**: `saveSwipeState()` → `saveHideState()` に統一し、状態管理を安定化
- **インジケーター制御強化**: バナー表示/隠し時の相互排他的なインジケーター制御を徹底
- **状態復元改善**: セッション状態復元時に適切なインジケーターのみが表示されるよう修正

### 🔧 技術的改善
- **状態保存の修正**: `hideBanner()`, `showBanner()` 内の関数呼び出しを正しい `saveHideState()` に修正
- **重複防止処理**: インジケーター表示/非表示時に確実に対象外インジケーターを非表示にする処理を追加
- **セッション管理強化**: `loadHideState()` 関数でより明確な状態制御を実装

## 過去の更新内容（v2.3.0）
### 🎯 メジャーアップデート: PC版バナー隠し機能実装
- **PC版対応**: デスクトップでもバナーの表示/非表示操作が可能
- **統一UX**: PC/モバイルで一貫したバナー制御体験を提供
- **クリック操作**: PC版ではインジケーターをクリックで操作
- **キーボード対応**: Tab/Enter/Spaceキーでアクセシブルな操作
- **ツールチップ**: PC版にホバー時のヒント表示機能

## 重要な実装ポイント

### レスポンシブサイズシステムの実装（v2.2.0）
- **JavaScript動的制御**: `getCurrentIconSize()` メソッドで画面サイズに応じたサイズを返却
- **4:3アスペクト比計算**: `{ width, height: Math.round(width * 0.75) }` で一貫性を保持
- **CSS側の対応**: 固定サイズを削除し、JavaScriptからの動的設定を優先
- **設定値の管理**: PC/モバイル/超小型端末の3段階設定で柔軟な調整が可能
- **データ移行処理**: 既存の`icon_size`設定を`icon_size_mobile`に自動移行

### バナー形式の実装
- **画像サイズ**: 480×360px（4:3アスペクト比）を前提とした設計
- **レスポンシブ**: 画面サイズに応じて適切な表示サイズに調整
- **object-fit: contain**: 画像の比率を保持してコンテナにフィット

### スワイプ隠し機能の実装
- **モバイル判定**: `window.innerWidth <= 768` で動作制御
- **状態管理**: 3つの状態（visible/hidden/transitioning）を管理
- **既存機能との連携**: 隠し状態ではアニメーションと吹き出しを無効化
- **リサイズ対応**: デスクトップに切り替わった際の状態リセット

### 管理画面の実装
- **フォーム処理**: POST データの適切なサニタイズと検証
- **プレビュー機能**: JavaScript によるリアルタイムプレビュー更新（4:3比率対応）
- **文字数制限**: 吹き出しメッセージの25文字制限と入力制御
- **設定移行対応**: アクティベーション時の自動データ移行とバリデーション統一