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

**Releases**もしくは**任意のコミット**からスクリプト一式をダウンロードします。

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

## スクリプトのアップロード

ダウンロード及び編集したスクリプトファイルを全てサーバーの公開ディレクトリ直下へアップロードしてください。  
以上で設置処理は完了となります。

## Cloudflare

Cloudflare を導入しない場合は`/test/.use_cloudflare`を削除してください。

# 板の追加・管理

## 新規板の作成

スクリプトを設置後、`/test/create.php`にアクセスし、必要事項を入力します。  
作成コードは設置時に設定した`/test/createcode.cgi`の内容です。

## 板の設定

`/test/admin.php`より各種管理を行うことができます。

## 認証

現仕様では管理人や CAP ユーザーでも認証が必要です。  
認証後にメール欄に鍵を入力して書き込むことで Cookie に鍵情報が保存されるので、その後 mail 欄に`#passward`を入力すれば管理人あるいは CAP ユーザーとしてレスができます。

# 過去ログについて

過去ログ検索ページは`/test/kakolog.php?bbs={$bbs}`にあります。  
`{$bbs}`には板のディレクトリ名を入力してください。

# 開発・運用方針

ブランチやバージョニング、リリースについては [DEVELOPMENT.md](./DEVELOPMENT.md) を参照してください。

# Lisence

The license in the LICENSE.txt file applies, unless a separate license is listed in the source code.
