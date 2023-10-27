<?php
/**
 * @param array $SETTING 板の設定
 */
function applyDiceCommand($SETTING)
{
    if ($SETTING['commands'] !== 'checked') {
        return;
    }
    if (strpos($_POST['name'], '!nocmd') !== false) {
        return;
    }
    if (!preg_match('/\![1-9]+[0-9]*[dD][1-9]+[0-9]*/', $_POST['comment'])) {
        return;
    }
    // 振れるダイスxの最大数
    $MAX_NUM_OF_DICE = 100;
    // ダイスの出目yの最大数
    $MAX_DICE_VALUE = 100;
    // 1レス内でダイスコマンドが発火する最大回数
    $DICE_LIMIT = 5;
    // x,yが最大数を超えているかの判定
    $xIsOver = false;
    $yIsOver = false;
    // 元本文のみ取得 ※<hr>以降はシステムメッセージなので対象外
    $commentParts = explode('<hr>', $_POST['comment']);
    // ダイス処理
    $commentParts[0] = preg_replace_callback(
        '/\!(([1-9]+[0-9]*)([dD])([1-9]+[0-9]*))/',
        function ($commandMatches) use ($MAX_NUM_OF_DICE, $MAX_DICE_VALUE, &$xIsOver, &$yIsOver) {
            $diceText = $commandMatches[1];
            $x = $commandMatches[2];
            $diceType = $commandMatches[3];
            $y = $commandMatches[4];
            // 最大数オーバー確認
            if($x > $MAX_NUM_OF_DICE) {
                $xIsOver = true;
            }
            if($y > $MAX_DICE_VALUE) {
                $yIsOver = true;
            }
            // 最大数オーバーなので処理しない
            if($x > $MAX_NUM_OF_DICE || $y > $MAX_DICE_VALUE) {
                return "【{$diceText}】";
            }
            // 通常処理
            $values = array_map(function () use ($y) {
                return mt_rand(1, $y);
            }, array_fill(0, $x, 1));
            $sum = array_sum($values);
            if($diceType === 'd') {
                $valuesAddition = implode('+', $values);
                return "<b>【{$diceText}:{$sum}({$valuesAddition})】</b>";
            } else {
                return "<b>【{$diceText}:{$sum}】</b>";
            }
        },
        $commentParts[0],
        $DICE_LIMIT
    );
    $newComment = implode('<hr>', $commentParts);
    // 例外メッセージ
    if($xIsOver || $yIsOver) {
        if(strpos($newComment, '<hr>') === false) {
            $newComment .= '<hr>';
        }
        if($xIsOver) {
            $newComment .= "★x(ダイスの個数)の最大値は{$MAX_NUM_OF_DICE}です。<br>";
        }
        if($yIsOver) {
            $newComment .= "★y(ダイスの出目)の最大値は{$MAX_DICE_VALUE}です。<br>";
        }
    }
    // 本文変更
    $_POST['comment'] = $newComment;
}

applyDiceCommand($SETTING);
