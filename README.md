# yoAdminPortal

管理ポータルおよびビルダーツール。ポータル画面のカスタマイズとリンク管理を行います。

---

## 📋 目次

1. [概要](#概要)
2. [機能](#機能)
3. [セットアップ](#セットアップ)
4. [使用方法](#使用方法)
5. [設定](#設定)

---

## 概要

yoAdminPortalは、複数のWebアプリケーションへのリンクを一元管理するカスタマイズ可能なポータル画面を提供します。

- **viewer.php** - ポータル表示画面（ユーザー向け）
- **builder.php** - ポータル編集画面（管理者向け）

---

## 機能

- 🔗 **リンク管理**: アイコン付きリンクカードの作成・編集・削除
- 🎨 **テーマ設定**: 環境別のカラーテーマ（dev/staging/production）
- 👥 **権限制御**: ユーザーごとのリンク表示制御
- 🔐 **SSO連携**: yoSSOによる統一認証

---

## セットアップ

### 1. 必要環境

- PHP 7.4 以上
- yoSSO（認証システム）

### 2. 初期セットアップ

```bash
cd yoAdminPortal
php setup.php
```

セットアップスクリプトは以下を行います：

- `shared/` ディレクトリの設定ファイルを `../shared/` に展開
- 既存ファイルとの競合を検出してレポート
- yoSSOのセットアップ状況を確認

出力例：
```
=================================
  yoAdminPortal セットアップ
=================================

[共有ディレクトリ]
[·] shared ディレクトリは既に存在します

[ファイル展開]
[·] session_config.php はグローバル側に既に存在します（スキップ）
[✓] check_session.php をコピーしました
[·] theme.css は同一です（スキップ）
[·] theme.js は同一です（スキップ）

[依存関係]
[·] yoSSO は既にセットアップ済みです

=================================
  セットアップ完了！
=================================
```

### 3. yoSSOとの連携

yoAdminPortalはyoSSOによる認証が必要です。先にyoSSOをセットアップしてください：

```bash
cd ../yoSSO
php setup.php
```

---

## 使用方法

### ポータル表示（viewer.php）

```
http://localhost/mngtools/yoAdminPortal/viewer.php
```

ログイン後、設定されたリンクカードが表示されます。

### ポータル編集（builder.php）

```
http://localhost/mngtools/yoAdminPortal/builder.php
```

リンクの追加・編集・削除、タイトルの変更などが可能です。

---

## 設定

### portal_config.json

ポータルの設定ファイルです：

```json
{
    "title": "Admin Portal",
    "target_env": "dev",
    "base_color": "#3b82f6",
    "links": [
        {
            "label": "Dashboard",
            "url": "/dashboard",
            "icon": "fa-chart-line"
        }
    ]
}
```

| 項目 | 説明 |
|------|------|
| title | ポータルのタイトル |
| target_env | 環境識別子（dev/staging/production） |
| base_color | テーマカラー |
| links | リンクカードの配列 |

### 権限設定

ユーザーごとのリンク表示は `mnguser` で管理されます。  
`permissions` 配列にURLを追加すると、そのリンクが表示されます。  
`*` を設定するとすべてのリンクが表示されます。