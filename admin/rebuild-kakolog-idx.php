<?php

// -----------------------------------------------------------------
// 注意事項
// -----------------------------------------------------------------
// このファイルは管理者のみがアクセス可能な状態にしてください。
// ・basic認証をつける
// ・メンテナンス中のみアップロードする
// ・ローカルで処理する
// 等
//
// また、このファイルによるインデックス再構築中は掲示板を停止させてください。
// -----------------------------------------------------------------
// 使い方
// -----------------------------------------------------------------
// このファイルのURLに以下のクエリパラメータを取得してアクセスしてください。
//
// bbs= 再構築したい板のフォルダ名
// pass= 該当の板の管理パスワード
// -----------------------------------------------------------------
if (!isset($_GET['bbs']) || empty($_GET['bbs'])) {
    echo 'bbsを指定してください。';
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $_GET['bbs'])) {
    echo 'bbsが不正です。';
    exit;
}
if (!isset($_GET['pass']) || empty($_GET['pass'])) {
    echo 'passを指定してください。';
    exit;
}

$passFile = "../{$_GET['bbs']}/passfile.cgi";
$pass = file_get_contents($passFile);
if (!password_verify($_GET['pass'], $pass)) {
    echo 'passが違います';
    exit;
}

set_time_limit(0);

// 定数定義
$BBS_ID = $_GET['bbs'];
$CHUNK_SIZE = 1000;

$SUBJECT_PATH = "../{$BBS_ID}/kakolog-subject.txt";
$TMP_IDX_PATH   = "../{$BBS_ID}/kakolog-subject.tmp"; // 一時ファイル
$FINAL_IDX_PATH = "../{$BBS_ID}/kakolog-subject.idx";
$STATE_PATH     = "../{$BBS_ID}/rebuild.state"; // 処理進捗を記録するファイル

// -----------------------------------------------------------------
// 1. 状態の初期化と取得
// -----------------------------------------------------------------
// $STATE_PATHから前回終了時のバイトオフセットを取得
// 実行完了時は $STATE_PATH に 'DONE' が書き込まれる
if (is_file($STATE_PATH)) {
    $stateContent = trim(file_get_contents($STATE_PATH));
    if ($stateContent === 'DONE') {
        $timestamp = filemtime($STATE_PATH);
        $formattedTime = date('Y-m-d H:i:s', $timestamp);
        echo 'インデックス再構築は既に完了しています。<br>';
        echo "(完了日時: {$formattedTime})<br>";
        echo "<br>";
        echo "再度行う場合はBBSフォルダ直下のrebuild.stateを削除してください。";
        exit;
    }
    // 処理開始バイト位置
    $startOffset = (int) $stateContent;
} else {
    // 初回実行時、または状態ファイルがない場合は、オフセットを0から開始
    $startOffset = 0;
    // 初回実行時のみ、既存の一時ファイルを削除
    if (is_file($TMP_IDX_PATH)) {
        unlink($TMP_IDX_PATH);
    }
}

// -----------------------------------------------------------------
// 2. ファイルを開く
// -----------------------------------------------------------------
// 過去ログファイルを開く
$subjectHandle = @fopen($SUBJECT_PATH, 'r');
if ($subjectHandle === false) {
    die("過去ログファイルが開けません。");
}

// 一時インデックスファイルを開く
$tmpIdxHandle = @fopen($TMP_IDX_PATH, 'a');
if ($tmpIdxHandle === false) {
    fclose($subjectHandle);
    die("インデックス一時ファイルが開けません。");
}

// -----------------------------------------------------------------
// 3. ロックとシーク
// -----------------------------------------------------------------
// 過去ログファイルに共有ロック
if (!flock($subjectHandle, LOCK_SH)) {
    fclose($subjectHandle);
    fclose($tmpIdxHandle);
    die("過去ログファイルへのロック取得に失敗しました。");
}
// 一時インデックスファイルに排他ロック
if (!flock($tmpIdxHandle, LOCK_EX)) {
    fclose($subjectHandle);
    fclose($tmpIdxHandle);
    die("インデックス一時ファイルへのロック取得に失敗しました。");
}

// 前回終了した位置へポインタを移動 (初回は0)
if ($startOffset > 0) {
    fseek($subjectHandle, $startOffset, SEEK_SET);
}

// -----------------------------------------------------------------
// 4. チャンク処理
// -----------------------------------------------------------------
$processedLines = 0;
$newOffsets = [];

while (!feof($subjectHandle) && $processedLines < $CHUNK_SIZE) {
    // 追記するオフセットを取得
    $currentOffset = ftell($subjectHandle);

    // 1行読み込む
    $line = fgets($subjectHandle);

    // 終端
    if ($line === false) {
        break;
    }

    // 正しいデータならオフセット配列に追加
    if (preg_match('/^([0-9]+)\.dat<>(.+)\s\(([0-9]+)\)$/', $line)) {
        $newOffsets[] = $currentOffset;
    }

    // 次の行へ
    $processedLines++;
}
$nextOffset = ftell($subjectHandle);

// -----------------------------------------------------------------
// 5. 状態と一時ファイルへの書き込み
// -----------------------------------------------------------------
if ($processedLines > 0) {
    // 今回取得したオフセット配列を一時ファイルに追記
    fwrite($tmpIdxHandle, implode("\n", $newOffsets) . "\n");

    // 次の開始位置を記録
    file_put_contents($STATE_PATH, $nextOffset);
}

// -----------------------------------------------------------------
// 6. ロック解除しファイルを閉じる
// -----------------------------------------------------------------
flock($tmpIdxHandle, LOCK_UN);
flock($subjectHandle, LOCK_UN);
fclose($tmpIdxHandle);
fclose($subjectHandle);

// -----------------------------------------------------------------
// 7. 最終処理と結果出力 (HTML)
// -----------------------------------------------------------------
$subjectSize = filesize($SUBJECT_PATH);

echo '<!DOCTYPE html><html><body>';
if ($nextOffset >= $subjectSize) {
    // 完了処理: 一時ファイルを本番ファイルに上書きし、状態ファイルを 'DONE' にする
    rename($TMP_IDX_PATH, $FINAL_IDX_PATH);
    file_put_contents($STATE_PATH, 'DONE');

    echo '<p style="color: green; font-weight: bold;">インデックス再構築が完了しました！</p>';
    echo "<p>全ファイルサイズ: {$subjectSize} バイト / 処理済み: {$nextOffset} バイト</p>";

} else {
    // 続行処理: 進捗状況を表示
    $percent = round(($nextOffset / $subjectSize) * 100, 2);
    $remaining = $subjectSize - $nextOffset;

    echo "<p>進捗: {$percent}% 完了 ({$nextOffset} / {$subjectSize} バイト)</p>";
    echo "<p>次の {$CHUNK_SIZE} 行を処理する場合はページを再読み込みしてください。</p>";
}
echo '</body></html>';
