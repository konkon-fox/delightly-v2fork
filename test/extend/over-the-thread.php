<?php

/**
 * 板ごとのカスタム本文を取得する関数
 *
 * @return string カスタム本文
 */
function getCustomComment()
{
    $bbs = basename($_POST['board']);
    $file = __DIR__ . '/../../' . $bbs . '/over-the-thread.cgi';
    if (!is_file($file)) {
        return '';
    }
    $content = safe_file_get_contents($file);
    if ($content === false) {
        return '';
    }

    // 本文を抽選
    $comments = explode('{$br}', $content);
    $comments = array_filter($comments);
    if (empty($comments)) {
        return '';
    }
    $key = array_rand($comments);
    $comment = $comments[$key];

    // 本文をエスケープ処理
    // 文末の改行と空白を削除
    $comment = preg_replace('/[\h\v]+\z/u', '', $comment);
    // 文頭の改行を削除
    $comment = preg_replace('/\A[\v]+/u', '', $comment);
    // htmlタグをエスケープ
    $comment = htmlspecialchars($comment, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // 改行コードを<br>に変換
    $comment = str_replace(["\r\n", "\r", "\n"], '<br>', $comment);

    return $comment;
}

/**
 * レス上限に達したスレに通知レスを追加する関数
 *
 */
function addOverTheThread(
    $isMax,
    $maxResLimit,
    $NOWTIME,
    $DATFILE,
    $THREADFILE
) {
    if (!$isMax) {
        return;
    }

    $number = $maxResLimit + 1;
    $customComment = '';
    $customComment = getCustomComment();

    if (!empty($customComment)) {
        // カスタム本文から追記分を作成
        $datLine = "{$number}<><>Over {$maxResLimit}<>{$customComment}<>\n";
    } else {
        // カスタム本文が無い場合はデフォルト追記文を作成

        // lifetimeを計算
        $diff = $NOWTIME - $_POST['thread'];
        $dateParts = [
            '日' => floor($diff / 86400),
            '時間' => floor(($diff % 86400) / 3600),
            '分' => floor(($diff % 3600) / 60),
            '秒' => $diff % 60,
        ];
        $dateDiffText = '';
        $isStart = false;
        foreach ($dateParts as $unit => $value) {
            if ($value > 0 || $isStart) {
                $dateDiffText .= " {$value}{$unit}";
                $isStart = true;
            }
        }
        if (empty($dateDiffText)) {
            $dateDiffText = ' 0秒';
        }
        // 追記分作成
        $datLine = "{$number}<><>Over {$maxResLimit}<>このスレッドは{$maxResLimit}を超えました。 <br> 新しいスレッドを立ててください。<br>life time:{$dateDiffText}<>\n";
    }

    // datに追記
    addNewResToDat($DATFILE, mb_convert_encoding($datLine, 'SJIS-win', 'UTF-8'));
    addNewResToDat($THREADFILE, $datLine);
}
addOverTheThread(
    $isMax,
    $maxResLimit,
    $NOWTIME,
    $DATFILE,
    $THREADFILE
);
