# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## プロジェクト概要

Amazon Corner Buddy は、WordPress サイトの左下角にAmazonアイコン（prime-day.png）を表示し、10秒間隔でランダムなアニメーションを実行するプラグインです。Ver2.0.0でPC/モバイル別サイズ対応とAmazon規約準拠の画像に更新されました。

## アーキテクチャ

### ファイル構成
- `amazon-corner-buddy.php` - メインプラグインファイル、定数定義とフック登録
- `includes/class-amazon-corner-buddy.php` - メインクラス（シングルトンパターン）
- `admin/admin-settings.php` - 管理画面の設定UI
- `assets/css/corner-buddy.css` - 6種類のアニメーション定義とスタイル
- `assets/js/corner-buddy.js` - アニメーション制御ロジック
- `assets/images/prime-day.png` - Amazon規約準拠のアイコン画像

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
- アイコンサイズ（20-100px）
  - PC用サイズ（769px以上）
  - モバイル用サイズ（768px以下）
- 透明度（0.1-1.0）
- 位置調整（左端・下端からの距離）
- リンクURL設定

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
# AmazonCornerBuddy.triggerRandomAnimation()
# AmazonCornerBuddy.stopAnimations()
# AmazonCornerBuddy.resumeAnimations()
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
- 他プラグインとの競合を避ける専用クラス名
- `!important` を使用して他のスタイルに影響されない実装
- レスポンシブ対応（モバイル、タブレット）

## 一般的な修正パターン

### 新しいアニメーションの追加
1. `assets/css/corner-buddy.css` に新しい `@keyframes` と `.acb-animation-*` クラスを追加
2. `assets/js/corner-buddy.js` の `animations` 配列に新しいクラス名を追加

### 設定項目の追加
1. `includes/class-amazon-corner-buddy.php` の `register_settings()` で新しいフィールドを追加
2. `admin/admin-settings.php` の管理画面UIに対応するHTML要素を追加
3. `sanitize_options()` メソッドで新しい設定のバリデーション追加

### WordPress要件
- PHP 7.4以上
- WordPress 5.0以上
- jQueryライブラリ（WordPressコア依存）

## バージョン情報
- 現在のバージョン: 2.0.0

## 重要な修正項目
- メインファイルのバージョン定数とプラグインヘッダーの整合性確認が必要