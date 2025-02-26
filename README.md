# このスクリプトについて
このスクリプトはnore氏の作成した掲示板スクリプト「delight v2」から独自の改修を加えたものです。  
https://git.3chan.cc/stat2/delightly-v2fork/ にて改修が行われていましたが、管理人と連絡が取れなくなったためリポジトリをgithubへ仮移行しました。

## 元仕様
https://delight.rentalbbs.net/delight.html に記載されていた仕様情報です。  
v2-fork版(当スクリプト)では変更されている場合があります。

### 概要
そのまま設置すればレンタル掲示板として使えますし、/test/createcode.cgiに作成パスを入れれば一般の掲示板としても使えます  
※認証にはreCAPTCHAへの登録、画像アップロード機能にはimgurAPIへの登録が必要です  
※このスクリプトの使用に関して発生したいかなるトラブルにも責任を負いかねます  
※慌てて開発したので致命的ではない不具合がいくつかあります

### 設置方法
v2.zipを解凍後、v2ディレクトリ内の全てのディレクトリやファイルを設置  
設置後/test/create.phpにアクセスして板を作成  
作成後/test/admin.phpにアクセスしてログイン・板設定変更  
※利用者に勝手に掲示板を作成されないようにするには/test/createcode.cgiに作成パスを入れてください

### 注意事項
- PHP 7.4 - PHP 8.2で動作確認済
- 設置先のサーバでmbstringが有効化されている必要があります

### スレッドフロート型掲示板共通の機能
スレッド、age/sage、トリップ(10桁・12桁)、専用ブラウザ、ID、ID末尾、回線別ニックネーム表示など

### delight独自の機能
TL、認証システム、地域名表示、本文の装飾、アイコン機能など

### その他仕様
- メール欄は表示されません(不可視化)。代わりにID末尾等の情報が表示されます
- Web版が大幅に拡張されています(後述)
- 掲示板の設定で過去ログを残すか消去するか選択可能

### 認証システムとは
- スパムや荒らしの対策のため投稿を行うにはreCAPTCHAを用いた認証を行い、鍵を発行する必要があります
細かな仕様はこちら -> [ClientID(同意鍵)システム(v2)](https://w.atwiki.jp/3chjp/pages/36.html)

### TL(タイムライン)とは
- 掲示板に投稿されたレスを最新順に並べたものです ※専ブラ版TLは古い順
- TLのみに投稿することもできます(TL限定投稿). Web版、専ブラ版共にTLの投稿欄から投稿してください
- TLには過去ログはありません. TL限定投稿はTLの保持数を超えた時点で閲覧できなくなります
- 「sage」での投稿はTLに反映されません
- 専ブラ版TLでは1番目にローカルルール、2番目に告知欄が表示されています

### IDについて
IDが変わるまでの時間は掲示板毎の設定で変更できます ※コマンド機能が有効の掲示板ではスレッド毎にも変更できます  
IDの生成仕様は5ch.net/bbspink.comのKOROKOROと似た仕様ですが一部異なる部分があります  
生成仕様の詳細は以下の通りです：  
自演防止のため認証時の情報を使用してIDを生成  
認証時の回線が固定回線(末尾0): 1234-ABCD  
認証時の回線が固定回線(末尾0)以外: S234-ABCD  
1,2 -> IP第一オクテット  
3,4 -> プロバイダ名  
A,B -> UserAgent  
C,D -> ブラウザのヘッダ等情報  
S -> 回線別末尾(a,d,Mなど)  
ID、ID末尾、回線別ニックネーム等の細かな仕様はこちら -> [BBS_SLIP(v2)](https://w.atwiki.jp/3chjp/pages/37.html)

### Web版について
- Webブラウザで掲示板のTL、スレ一覧、履歴、未読のいずれかのページで「ホーム画面に追加」「ショートカットを作成」などを行うとWebアプリ風に使えます
- ダークモードが使用可能 ※初期設定では端末のテーマに自動で合わせます
- ID、ワード、タイトルのミュート(NG)機能を使用可能(一部を除き正規表現も使用可)
- 履歴、未読表示に対応(掲示板毎・過去ログは不可)
- スレッド検索、スレ内検索が可能(正規表現使用可)
- 返信機能、レスやURLなどのコピー、次スレッド作成、ID抽出など

### コマンド機能・スレッド主機能について
コマンド機能とスレッド主機能はそれぞれ掲示板の設定で有効になっている場合は使用できます  
細かな仕様はこちら -> [コマンド一覧(v2)](https://w.atwiki.jp/3chjp/pages/35.html)


# 設置手順

## サーバーの条件
PHPを使えることが必須条件です。旧仕様ではPHP 7.4 - PHP 8.2で動作確認済とのことです。

## スクリプトのダウンロード
**Releases**もしくは**任意のコミット**からスクリプト一式をダウンロードします。

## ソースコードの変更
### 作成コード
`/test/createcode.cgi`に任意のパスワードを入力し、保存します。
このパスワードは掲示板を作成する時に必要となります。

### Cloudflare Turnstile
v2-fork版では認証にCloudflare Turnstileを使用しています。  
Cloudflareに登録し、Turnstileウィジェットを作成します。  
`/test/auth.php`を開き、`$sitekey`を上記で取得したサイトキーに、`$SECRET_KEY`を上記で取得したシークレットキーに、それぞれ変更します。

### index2.html
トップページのHTMLファイルです。
自由に編集してください。

## ファイルへのアクセス拒否やリダイレクトの設定
.htaccessを利用可能な場合はこの項目を飛ばしてください。  
nginxサーバーを使用する場合は同等の設定になるように設定ファイルを変更してください。

## スクリプトのアップロード
ダウンロード及び編集したスクリプトファイルを全てサーバーの公開ディレクトリ直下へアップロードしてください。  
以上で設置処理は完了となります。

## Cloudflare
Cloudflareを導入しない場合は`/test/.use_cloudflare`を削除してください。


# 板の追加・管理
## 新規板の作成
スクリプトを設置後、`/test/create.php`にアクセスし、必要事項を入力します。  
作成コードは設置時に設定した`/test/createcode.cgi`の内容です。

## 板の設定
`/test/admin.php`より各種管理を行うことができます。

## 認証
現仕様では管理人やCAPユーザーでも認証が必要です。  
認証後にメール欄に鍵を入力して書き込むことでCookieに鍵情報が保存されるので、その後mail欄に`#passward`を入力すれば管理人あるいはCAPユーザーとしてレスができます。


# Lisence
The license in the LICENSE.txt file applies, unless a separate license is listed in the source code.