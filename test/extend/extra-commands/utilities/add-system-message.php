<?php
/**
 * コマンド処理でのシステムメッセージを本文に追加する関数定義
 *
 * @param string $text 追加したいシステムメッセージ
 * @return void
 */
function addSystemMessage($text)
{
    $commentParts = explode('<hr>', $_POST['comment']);
    if(count($commentParts) < 2) {
        array_push($commentParts, '');
    }
    $commentParts[1] .= $text;
    $_POST['comment'] = implode('<hr>', $commentParts);
}
