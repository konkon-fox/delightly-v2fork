# このスクリプトについて

このスクリプトは nore 氏の作成した掲示板スクリプト「delight v2」から独自の改修を加えたものです。  
https://git.3chan.cc/stat2/delightly-v2fork/ にて改修が行われていましたが、管理人と連絡が取れなくなったためリポジトリを github へ仮移行しました。
当時の issues は https://fox-tools.pages.dev/stat2-delightly-v2fork-issues/ にまとめてあります。

# 元仕様

このスクリプトのフォーク元である「delight v2」の仕様については [README_OLD.md](./README_OLD.md) を参照ください。

# 設置手順

## サーバーの条件

PHP を使えることが必須条件です。旧仕様では PHP 7.4 - PHP 8.2 で動作確認済とのことです。

## スクリプトのダウンロード

**Releases**からスクリプト一式をダウンロードします。

## ソースコードの変更

### 作成コード

`/test/createcode.cgi`に任意のパスワードを入力し、保存します。
このパスワードは掲示板を作成する時に必要となります。

### Cloudflare Turnstile

v2-fork 版では認証に Cloudflare Turnstile を使用しています。  
Cloudflare に登録し、Turnstile ウィジェットを作成します。  
`/test/auth.php`を開き、`$sitekey`を上記で取得したサイトキーに、`$SECRET_KEY`を上記で取得したシークレットキーに、それぞれ変更します。

### index2.html

トップページの HTML ファイルです。
自由に編集してください。

## ファイルへのアクセス拒否やリダイレクトの設定

.htaccess を利用可能な場合はこの項目を飛ばしてください。  
nginx サーバーを使用する場合は[nginx.conf の設定例](./nginx.conf.example)を参考に設定ファイルを変更してください。

## Cloudflare

Cloudflare を導入しない場合は`/test/.use_cloudflare`を削除してください。

## 認証の厳格モード

デフォルトでは認証の厳格モード（VPN・海外回線等の拒否）が有効です。 制限を緩めたい場合は、以下のファイルを削除してください。

- 対象ファイル: `/test/.use_strict_auth`

## スクリプトのアップロード

ダウンロード及び編集したスクリプトファイルを全てサーバーの公開ディレクトリ直下へアップロードしてください。  
以上で設置処理は完了となります。

# 板の追加・管理

## 新規板の作成

スクリプトを設置後、`/test/create.php`にアクセスし、必要事項を入力します。  
作成コードは設置時に設定した`/test/createcode.cgi`の内容です。

## 板の設定

`/test/admin.php`より各種管理を行うことができます。

## 認証

現仕様では管理人や CAP ユーザーでも認証が必要です。  
認証後にメール欄に鍵を入力して書き込むことで Cookie に鍵情報が保存されるので、その後 mail 欄に`#passward`を入力すれば管理人あるいは CAP ユーザーとしてレスができます。

# システム全体の管理

## システム全体の設定

`/test/master.php`よりシステム全体の管理を行うことができます。  
認証時には設置時に`/test/createcode.cgi`で設定したパスワードを入力してください。  
なお、本ページは簡易的なパスワード認証がありますが、セキュリティ向上のため Basic 認証等も併用することを推奨します。

# 過去ログについて

過去ログ検索ページは`/test/kakolog.php?bbs={$bbs}`にあります。  
`{$bbs}`には板のディレクトリ名を入力してください。

# 開発・運用方針

ブランチやバージョニング、リリースについては [CONTRIBUTING.md](./CONTRIBUTING.md) を参照してください。

# 商用利用および外部 API について

本スクリプトの利用に関しては [LICENSE](./LICENSE) に従います。

ただし、本スクリプトは認証時に `ip-api.com` の API を利用しています。
この API の **Free プランは規約により商用利用が禁止されています。**

商用サイトで本スクリプトを運用する場合は、以下のいずれかの対応を行ってください。

1. [ip-api.com](https://ip-api.com/) の有料プランに加入し、必要に応じて認証ロジックを修正する。
2. 認証処理内の `ip-api.com` を利用している箇所を無効化、または別の判定ロジックへ差し替える。

# Lisence

The license in the LICENSE.txt file applies, unless a separate license is listed in the source code.
