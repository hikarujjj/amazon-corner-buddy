# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Amazon Corner Buddy は、WordPress サイトの左下角にAmazonバナー（prime-day.png、480×360px）を表示し、10秒間隔でランダムなアニメーションを実行するプラグインです。Ver2.1.0でバナー形式対応とモバイルスワイプ隠し機能を追加しました。

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
- バナーサイズ（4:3アスペクト比維持）
  - PC用サイズ（769px以上）: 120×90px（デフォルト）
  - モバイル用サイズ（768px以下）: 80×60px（デフォルト）
  - 超小型端末（480px以下）: 60×45px（デフォルト）
- 透明度（0.1-1.0）
- 位置調整（左端・下端からの距離）
- リンクURL設定
- スワイプ隠し機能（モバイルのみ）: 有効/無効切り替え
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
# AmazonCornerBuddy.triggerHideBanner()           # バナー隠し（モバイル）
# AmazonCornerBuddy.triggerShowBanner()           # バナー表示（モバイル）
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
- レスポンシブ対応（PC/モバイル/超小型端末）
- バナー形式（4:3アスペクト比）に最適化されたサイズ設定
- スワイプインジケーター（矢印マーク）のCSS実装

### スワイプ機能（v2.1.0）
- **Touch Events**: touchstart, touchmove, touchend でジェスチャー検出
- **スワイプ判定**: 水平50px以上移動、1秒以内、垂直移動の比率制限
- **状態管理**: sessionStorage による状態保持（ページ間維持）
- **矢印インジケーター**: 24px円形、オレンジグラデーション、ホバー/タップ効果
- **アニメーション**: 0.4秒のスムーズなトランジション、cubic-bezier カーブ

## 一般的な修正パターン

### 新しいアニメーションの追加
1. `assets/css/corner-buddy.css` に新しい `@keyframes` と `.acb-animation-*` クラスを追加
2. `assets/js/corner-buddy.js` の `animations` 配列に新しいクラス名を追加

### 設定項目の追加
1. `includes/class-amazon-corner-buddy.php` の `register_settings()` で新しいフィールドを追加
2. `admin/admin-settings.php` の管理画面UIに対応するHTML要素を追加
3. `sanitize_options()` メソッドで新しい設定のバリデーション追加
4. JavaScriptに新しい設定値を渡すため `wp_localize_script()` に追加

### バナーサイズの調整
- CSSの `#acb-corner-buddy` でデフォルトサイズを変更（4:3比維持）
- メディアクエリ `@media (max-width: 768px)` でモバイルサイズ調整
- メディアクエリ `@media (max-width: 480px)` で超小型端末サイズ調整
- 矢印インジケーターの位置もサイズに合わせて調整が必要

### WordPress要件
- PHP 7.4以上
- WordPress 5.0以上
- jQueryライブラリ（WordPressコア依存）

## バージョン情報
- 現在のバージョン: 2.1.0

## 最新の更新内容（v2.1.0）
### 🆕 新機能
- **モバイルスワイプ隠し機能**: 左スワイプでバナーを画面外に隠し、右矢印マークで復帰
- **バナー形式対応**: 480×360画像に対応したバナーレイアウト（4:3アスペクト比）
- **状態保持**: セッション中の隠し状態を維持

### 🔧 技術仕様
- **対象端末**: モバイル（768px以下）のみ
- **スワイプ判定**: 水平50px以上、1秒以内の左方向移動
- **矢印インジケーター**: 24px円形、オレンジグラデーション
- **管理画面設定**: スワイプ機能の有効/無効切り替え

## 重要な実装ポイント

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
- **プレビュー機能**: JavaScript によるリアルタイムプレビュー更新
- **文字数制限**: 吹き出しメッセージの25文字制限と入力制御