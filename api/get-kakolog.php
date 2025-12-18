<?php

if (!isset($_GET['bbs']) || empty($_GET['bbs'])) {
    echo 'bbsを指定してください。';
    exit;
}
if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $_GET['bbs'])) {
    echo 'bbsが不正です。';
    exit;
}

$bbsOfUrl = urlencode($_GET['bbs']);
$subjectPath = "../{$_GET['bbs']}/kakolog-subject.txt";
$subjectIndexPath = "../{$_GET['bbs']}/kakolog-subject.idx";
if (!is_file($subjectPath)) {
    echo '過去ログが存在しません。';
    exit;
}

// ページ初期化
$page = isset($_GET['page']) ? (int) $_GET['page'] : 0;

// キーワード初期化
$keywords = isset($_GET['keywords']) ? preg_split('/[\s　]+/u', $_GET['keywords'], -1, PREG_SPLIT_NO_EMPTY) : [];
$keywords = array_unique($keywords);
$keywordsOption = isset($_GET['and-or']) && $_GET['and-or'] === 'or' ? 'or' : 'and';

// 日付指定初期化
try {
    if (isset($_GET['since-date']) && !empty($_GET['since-date'])) {
        $dateTime = new DateTimeImmutable($_GET['since-date']);
        $sinceTime = $dateTime->format('U');
    }
    if (isset($_GET['until-date']) && !empty($_GET['until-date'])) {
        $dateTime = new DateTimeImmutable($_GET['until-date']);
        $dateTime =  $dateTime->add(new DateInterval('P1D'));
        $untilTime = $dateTime->format('U');
    }
} catch (\Exception $e) {
    echo '不正な日付が入力されました。';
    exit;
}
// レス数指定初期化
if (isset($_GET['min-res']) && !empty($_GET['min-res'])) {
    $minRes = (int) $_GET['min-res'];
}
if (isset($_GET['max-res']) && !empty($_GET['max-res'])) {
    $maxRes = (int) $_GET['max-res'];
}

// タイムゾーン指定
date_default_timezone_set('Asia/Tokyo');

// 定数定義
$MEMORY_LIMIT_SIZE = 50 * 1024 * 1024;
$ITEMS_PER_PAGE = 50;
$domain = $_SERVER['HTTP_HOST'];

// ドメインチェック
if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $domain)) {
    echo 'ホストが不正です。';
    exit;
}

// キーワード検索用の文字列正規化
function normalizeString($str)
{
    $str = mb_convert_kana($str, 'C');
    $str = html_entity_decode($str, ENT_HTML5);
    return $str;
}

/**
 * ファイルを全て読み込み、過去ログから1行ずつ逆順に抽出して返すジェネレータ関数
 * ※ファイルサイズが小さい場合のみ使用
 *
 * @param resource $subjectHandle ロック済みのファイルハンドル
 * @return \Generator - 逆順の各行を返す
 */
function readSmallFileLines($subjectHandle): \Generator
{
    $lines = [];
    while (($buffer = fgets($subjectHandle)) !== false) {
        $line = trim($buffer);
        if ($line !== '') {
            $line =  mb_convert_encoding($line, 'UTF-8', 'SJIS-win');
            $lines[] = $line;
        }
    }
    $lines = array_reverse($lines);
    foreach ($lines as $line) {
        yield $line;
    }
}

/**
 * インデックスに基づき、過去ログから1行ずつ逆順に抽出して返すジェネレータ関数
 * ※ファイルサイズが大きい場合のみ使用
 *
 * @param array $offsets 逆順ソート済みのオフセット配列
 * @param resource $subjectHandle ロック済みのファイルハンドル
 * @return \Generator - 逆順の各行を返す
 */
function readLargeFileLines($offsets, $subjectHandle): \Generator
{
    foreach ($offsets as $offset) {
        fseek($subjectHandle, $offset, SEEK_SET);
        $line = fgets($subjectHandle);
        if ($line === false) {
            continue;
        }
        $line = trim($line);
        $line = mb_convert_encoding($line, 'UTF-8', 'SJIS-win');
        yield $line;

    }
}

// ファイルサイズ測定　
$fileSize = filesize($subjectPath);
if ($fileSize === false) {
    echo '予期せぬエラーが発生しました。';
    exit;
}
$useIndexMode = $fileSize > $MEMORY_LIMIT_SIZE;

// ファイルサイズにより処理を分岐させる
//   小さい場合…ファイル全体を読み込んで逆順にして配列化
//   大きい場合…インデックスを利用して逆順に一行ずつ配列化
if (!$useIndexMode) {
    $linesToProcess = [];
    // 過去ログファイルを開く
    $subjectHandle = fopen($subjectPath, 'r');
    if ($subjectHandle === false) {
        echo '過去ログファイルが開けません。';
        exit;
    }
    // 過去ログファイルをロック
    if (!flock($subjectHandle, LOCK_SH)) {
        echo '過去ログファイルへのロックに失敗しました。';
        fclose($subjectHandle);
    }

    $linesToProcess = readSmallFileLines($subjectHandle);
} else {
    if (!is_file($subjectIndexPath)) {
        echo '過去ログインデックスが存在しません。';
        exit;
    }
    // 過去ログインデックスを取得
    $subjectIndexHandle = fopen($subjectIndexPath, 'r');
    if ($subjectIndexHandle === false) {
        echo '過去ログインデックスが開けません。';
        exit;
    }
    if (flock($subjectIndexHandle, LOCK_SH)) {
        $idxContent = stream_get_contents($subjectIndexHandle);
        flock($subjectIndexHandle, LOCK_UN);
    } else {
        echo '過去ログインデックスへのロックに失敗しました。';
        fclose($subjectIndexHandle);
        exit;
    }
    fclose($subjectIndexHandle);

    // 過去ログインデックスをパース
    if ($idxContent === '') {
        $offsets = [];
    } else {
        $offsets = explode("\n", $idxContent);
        $offsets  = array_filter($offsets, function ($offset) {
            return $offset !== '';
        });
        $offsets = array_map(function ($offset) {
            return (int) $offset;
        }, $offsets);
        $offsets = array_reverse($offsets);
    }

    // 過去ログファイルを開く
    $subjectHandle = fopen($subjectPath, 'r');
    if ($subjectHandle === false) {
        echo '過去ログファイルが開けません。';
        exit;
    }
    // 過去ログファイルをロック
    if (!flock($subjectHandle, LOCK_SH)) {
        echo '過去ログファイルへのロックに失敗しました。';
        fclose($subjectHandle);
    }

    $linesToProcess = readLargeFileLines($offsets, $subjectHandle);
}

// 表示する過去ログのリスト
$kakologList = [];

$totalCount = 0;
$itemsCount = 0;
$itemOffset = $page * $ITEMS_PER_PAGE;

// イテレータを処理し、検索・ページングを同時に実行
foreach ($linesToProcess as $line) {
    // 不正な行はスキップ
    if (!preg_match('/^([0-9]+)\.dat<>(.+)\s\(([0-9]+)\)$/', $line, $matches)) {
        continue;
    }
    // 各値
    $thread = (int) $matches[1];
    $title  = $matches[2];
    $res    = (int) $matches[3];
    // 検索ワードでフィルタリング
    if (!empty($keywords)) {
        $normalizedTitle = normalizeString($title);
        switch ($keywordsOption) {
            case 'and':
                $isMatch = true;
                foreach ($keywords as $keyword) {
                    $normalizedKeyword = normalizeString($keyword);
                    if (mb_stripos($normalizedTitle, $normalizedKeyword) === false) {
                        $isMatch = false;
                        break;
                    }
                }
                if (!$isMatch) {
                    continue 2;
                }
                break;
            case 'or':
                $isMatch = false;
                foreach ($keywords as $keyword) {
                    $normalizedKeyword = normalizeString($keyword);
                    if (mb_stripos($normalizedTitle, $normalizedKeyword) !== false) {
                        $isMatch = true;
                        break;
                    }
                }
                if (!$isMatch) {
                    continue 2;
                }
                break;
        }
    }
    // 日付指定でフィルタリング
    if (isset($sinceTime) && $sinceTime > $thread) {
        continue;
    }
    if (isset($untilTime) && $untilTime <= $thread) {
        continue;
    }
    // レス数指定でフィルタリング
    if (isset($minRes) && $minRes > $res) {
        continue;
    }
    if (isset($maxRes) && $maxRes < $res) {
        continue;
    }

    // ページに収まるアイテムか判定
    if ($totalCount < $itemOffset) {
        $totalCount++;
        continue;
    }
    $totalCount++;
    if ($itemsCount >= $ITEMS_PER_PAGE) {
        continue;
    }
    // 表示する一覧リストへ追加
    $itemsCount++;
    $kakologList[] = [
        'thread' => $thread ,
        'title'  => $title,
        'res'    => $res
    ];
}

// 過去ログファイルを閉じる
flock($subjectHandle, LOCK_UN);
fclose($subjectHandle);

// 出力用のHTML
$html = '';

// 不正なページを検出
if ($totalCount > 0 && count($kakologList) === 0) {
    echo '不正な操作が行われました。';
    exit;
}

// 検索情報 & ページャー & 件数表示
$html .= '<div class="d-flex flex-column row-gap-1 border-bottom border-secondary">';
// 検索ワード表示
if (!empty($keywords)) {
    $separator = $keywordsOption === 'or' ? 'or' : '&';
    $displayKeywords = htmlspecialchars(implode("」{$separator}「", $keywords), ENT_QUOTES, 'UTF-8');
    $html .= '<div>';
    $html .= "「{$displayKeywords}」での検索結果";
    $html .= '</div>';
}
// 期間指定表示
if (isset($sinceTime) || isset($untilTime)) {
    $sinceTimeText = isset($sinceTime) ? htmlspecialchars($_GET['since-date'], ENT_QUOTES, 'UTF-8') : '';
    $untilTimeText = isset($untilTime) ? htmlspecialchars($_GET['until-date'], ENT_QUOTES, 'UTF-8') : '';
    $html .= "<div>(期間：{$sinceTimeText}～{$untilTimeText})</div>";
}
// レス数指定表示
if (isset($minRes) || isset($maxRes)) {
    $minResText = isset($minRes) ? $minRes : '';
    $maxResText = isset($maxRes) ? $maxRes : '';
    $html .=  "<div>(レス数：{$minResText}～{$maxResText})</div>";
}
// ページャー
$pager = '';
$pager .=  '<div class="d-flex column-gap-1 ms-auto">';
// 最初へボタン
$firstPage = 0;
$pager .=  '<button';
$pager .=  " hx-get=\"/api/get-kakolog.php?page={$firstPage}\"";
$pager .=  ' hx-target="#result"';
$pager .=  ' hx-swap="innerHTML"';
$pager .=  ' hx-include="#search-form"';
$pager .=  ' hx-indicator="#loading"';
$pager .=  ' class="btn btn-sm btn-secondary"';
if ($page == 0) {
    $pager .=  ' disabled';
}
$pager .=  '>';
$pager .=  '最初へ';
$pager .=  '</button>';
// 前へボタン
$prevPage = $page - 1;
$pager .=  '<button';
$pager .=  " hx-get=\"/api/get-kakolog.php?page={$prevPage}\"";
$pager .=  ' hx-target="#result"';
$pager .=  ' hx-swap="innerHTML"';
$pager .=  ' hx-include="#search-form"';
$pager .=  ' hx-indicator="#loading"';
$pager .=  ' class="btn btn-sm btn-secondary"';
if ($page === 0) {
    $pager .=  ' disabled';
}
$pager .=  '>';
$pager .=  '前へ';
$pager .=  '</button>';
// 前へボタン
$nextPage = $page + 1;
$pager .=  '<button';
$pager .=  " hx-get=\"/api/get-kakolog.php?page={$nextPage}\"";
$pager .=  ' hx-target="#result"';
$pager .=  ' hx-swap="innerHTML"';
$pager .=  ' hx-include="#search-form"';
$pager .=  ' hx-indicator="#loading"';
$pager .=  ' class="btn btn-sm btn-secondary"';
if (($page + 1) * $ITEMS_PER_PAGE >= $totalCount) {
    $pager .=  ' disabled';
}
$pager .=  '>';
$pager .=  '次へ';
$pager .=  '</button>';
// 最後へボタン
$lastPage = $totalCount === 0 ? 0 : (int) (ceil($totalCount / $ITEMS_PER_PAGE) - 1);
$pager .=  '<button';
$pager .=  " hx-get=\"/api/get-kakolog.php?page={$lastPage}\"";
$pager .=  ' hx-target="#result"';
$pager .=  ' hx-swap="innerHTML"';
$pager .=  ' hx-include="#search-form"';
$pager .=  ' hx-indicator="#loading"';
$pager .=  ' class="btn btn-sm btn-secondary"';
if ($page === $lastPage) {
    $pager .=  ' disabled';
}
$pager .=  '>';
$pager .=  '最後へ';
$pager .=  '</button>';
// ページャー終了
$pager .=  '</div>';
$html .= $pager;
// 件数
$startPage = $totalCount === 0 ? 0 : $page * $ITEMS_PER_PAGE + 1;
$endPage = $totalCount === 0 ? 0 : $startPage + count($kakologList) - 1;
$html .=  '<div class="ms-auto">';
$html .=  "{$startPage}-{$endPage}件 / {$totalCount}件";
$html .=  '</div>';
// 検索情報 & ページャー & 件数表示 終了
$html .=  '</div>';

// 件数0
if ($totalCount === 0) {
    $html .= '<div class="m-1">(´・ω・｀)「ないよ」</div>';
    echo $html;
    exit;
}

// 過去ログ一覧本体
foreach ($kakologList as $index => $kakolog) {
    $decodedTitle = html_entity_decode($kakolog['title'], ENT_QUOTES);
    $title = htmlspecialchars($decodedTitle, ENT_QUOTES, 'UTF-8');
    $date = date('Y-m-d H:i', $kakolog['thread']);
    $url = "https://{$domain}/test/read.cgi/{$bbsOfUrl}/{$kakolog['thread']}/";
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $html .= '<div class="d-flex flex-column align-items-start row-gap-2 p-1 border-bottom border-secondary">';
    $html .= "<a href=\"/#{$bbsOfUrl}/{$kakolog['thread']}/\">{$title} ({$kakolog['res']})</a>";
    $html .= '<div class="d-flex justify-content-between align-items-end w-100">';
    $html .= "<span>{$date}</span>";
    $html .= "<button class=\"ms-auto btn btn-primary copy-button\" data-index=\"{$index}\">URLをコピー</button>";
    $html .= "<input type=\"text\" value=\"{$safeUrl}\" class=\"hidden-input\" id=\"hidden-input-{$index}\" readOnly/>";
    $html .= '</div>';
    $html .= '</div>';
}

// 下部のページャー
$html .= '<div class="d-flex flex-column align-items-end mt-2">';
$html .= $pager;
$html .= '</div>';

// 最終出力
echo $html;
