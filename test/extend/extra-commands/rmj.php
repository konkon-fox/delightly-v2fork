<?php
/**
 * !rmj構文を置換する関数。別箇所でも使用するので関数として独立させています。
 *
 * @param string $text 置換対象の文字列
 * @return string 置換後の文字列
 */
function replaceRmj($text)
{
    // 入れ子の最大数
    $MAX_NEST = 3;
    // !rmj◯で使用可能な番号の最大数
    $MAX_RMJ_NUMBER = 20;
    // !rmj構文を置換する上限数
    $REPLACE_LIMIT = 100;

    $rmjReplaceList = [];
    $replaceCount = 0;
    // !rmj構文を解釈
    for($i = 0;$i < $MAX_NEST;$i++) {
        $text = preg_replace_callback('/(\!rmj([0-9]*)):(((?!(\!rmj([0-9]*):(([^\/:])*\/)+([^\/:])*:|:)).)+):/', function ($matches) use (&$rmjReplaceList, &$replaceCount, $MAX_RMJ_NUMBER, $REPLACE_LIMIT) {
            // 例外処理
            $rmjNumber = $matches[2];
            if(((int) $rmjNumber) > $MAX_RMJ_NUMBER) {
                return $matches[0];
            }
            // 置換上限確認
            $replaceCount++;
            if($replaceCount > $REPLACE_LIMIT) {
                return $matches[0];
            }
            // 選択肢抽出
            $items = explode('/', $matches[3]);
            if(count($items) < 2) {
                return $matches[0];
            }
            // rmj置換準備
            $replace = [];
            $replace['rmj'] = $matches[1];
            $replace['items'] = $items;
            array_push($rmjReplaceList, $replace);
            return $matches[1];
        }, $text);
    }
    // 解釈した!rmjを置換
    $rmjReplaceList = array_reverse($rmjReplaceList);
    $replaceCount = 0;
    foreach($rmjReplaceList as $replace) {
        $pattern = preg_quote($replace['rmj']);
        $text = preg_replace_callback("/{$pattern}(?![0-9])/", function ($matches) use ($replace, &$replaceCount, $REPLACE_LIMIT) {
            // 置換上限確認
            $replaceCount++;
            if($replaceCount > $REPLACE_LIMIT) {
                return $matches[0];
            }
            // 抽選処理
            $key = array_rand($replace['items'], 1);
            $value = $replace['items'][$key];
            return "<b>{$value}</b>";
        }, $text);
    }
    return $text;
}

/**
 * 名前欄及び本文の!rmj構文を置換する処理。
 *
 * @param array $SETTING 板の設定
 * @param int $LV 投稿者のレベル
 * @return void
 */
function applyRmjCommand($SETTING, $LV)
{
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if(strpos($_POST['name'], '!rmj') === false && strpos($_POST['comment'], '!rmj') === false) {
        return;
    }
    // レベル制限
    $MIN_LV = 1;
    if($LV < $MIN_LV) {
        Error("レベルが{$MIN_LV}未満のユーザーは!rmjコマンドを使えません。");
    }
    // 名前欄置換
    if (strpos($_POST['name'], '!rmj') !== false) {
        $_POST['name'] = replaceRmj($_POST['name']);
    }
    // 本文置換
    if (strpos($_POST['comment'], '!rmj') !== false) {
        $commentParts = explode('<hr>', $_POST['comment']);
        $commentParts[0] = replaceRmj($commentParts[0]);
        if($commentParts[0] === '') {
            $commentParts[0] = '　';
        }
        if(count($commentParts) >= 2) {
            $commentParts[1] = replaceRmj($commentParts[1]);
        }
        $_POST['comment'] = implode('<hr>', $commentParts);
    }
}

applyRmjCommand($SETTING, $LV);
