<?php

// 初期化
$SETTING['commands-sage'] ??= 'checked';
$SETTING['commands-nopic'] ??= 'checked';
$SETTING['commands-jien'] ??= 'checked';
$SETTING['commands-live'] ??= 'checked';
$SETTING['commands-slip'] ??= 'checked';
$SETTING['commands-slipname'] ??= 'checked';
$SETTING['commands-ken'] ??= 'checked';
$SETTING['commands-id'] ??= 'checked';
$SETTING['commands-siberia'] ??= 'checked';
$SETTING['commands-day'] ??= 'checked';
$SETTING['commands-month'] ??= 'checked';
$SETTING['commands-year'] ??= 'checked';
$SETTING['commands-host'] ??= 'checked';
$SETTING['commands-clientid'] ??= 'checked';
$SETTING['commands-nolink'] ??= 'checked';
$SETTING['commands-idchange'] ??= 'checked';
$SETTING['commands-cap'] ??= 'checked';
$SETTING['commands-auth'] ??= 'checked';
$SETTING['commands-NO'] ??= 'checked';
$SETTING['commands-AA'] ??= 'checked';
$SETTING['commands-ARR'] ??= 'checked';
$SETTING['commands-stop'] ??= 'checked';
$SETTING['commands-noid'] ??= 'checked';
$SETTING['commands-add'] ??= 'checked';
$SETTING['commands-idchange-first-res'] ??= 'checked';

if ($SETTING['commands'] === 'checked') {
    if ($SETTING['commands-sage'] === 'checked' && str_contains($message, '!sage')) {
        $sage = true;
    }
    if ($SETTING['commands-nopic'] === 'checked' && str_contains($message, '!nopic')) {
        $SETTING['NOPIC'] = 'checked';
    }
    if ($SETTING['commands-jien'] === 'checked' && str_contains($message, '!jien')) {
        $SETTING['id'] = 'checked';
        $SETTING['slip'] = 'checked';
        $SETTING['disp_slipname'] = 'checked';
        $SETTING['BBS_JP_CHECK'] = 'checked';
        $SETTING['ID_RESET'] = 'year';
    }
    if ($SETTING['commands-live'] === 'checked' && str_contains($message, '!live')) {
        $SETTING['threadcheck'] = '';
        $SETTING['timecheck'] = '';
        if ($SETTING['BBS_SAMBA24'] > 0) {
            $SETTING['BBS_SAMBA24'] = $SETTING['BBS_SAMBA24'] / 2;
        }
    }
    if ($SETTING['commands-slip'] === 'checked' && str_contains($message, '!slip')) {
        $SETTING['slip'] = 'checked';
    }
    if ($SETTING['commands-slipname'] === 'checked' && str_contains($message, '!slipname')) {
        $SETTING['disp_slipname'] = 'checked';
    }
    if ($SETTING['commands-ken'] === 'checked' && str_contains($message, '!ken')) {
        $SETTING['BBS_JP_CHECK'] = 'checked';
    }
    if ($SETTING['commands-id'] === 'checked' && str_contains($message, '!id')) {
        $SETTING['id'] = 'checked';
    }
    if ($SETTING['commands-siberia'] === 'checked' && str_contains($message, '!siberia')) {
        $SETTING['id'] = 'siberia';
    }
    if ($SETTING['commands-day'] === 'checked' && str_contains($message, '!day')) {
        $SETTING['ID_RESET'] = 'day';
    }
    if ($SETTING['commands-month'] === 'checked' && str_contains($message, '!month')) {
        $SETTING['ID_RESET'] = 'month';
    }
    if ($SETTING['commands-year'] === 'checked' && str_contains($message, '!year')) {
        $SETTING['ID_RESET'] = 'year';
    }
    if ($SETTING['commands-host'] === 'checked' && str_contains($message, '!host')) {
        $SETTING['fusianasan'] = 'name';
    }
    if ($SETTING['commands-clientid'] === 'checked' && str_contains($message, '!clientid')) {
        $SETTING['fusianasan'] = 'id';
    }
    if ($SETTING['commands-nolink'] === 'checked' && str_contains($message, '!nolink')) {
        $SETTING['DISABLE_LINK'] = 'checked';
    }
    if (
        $SETTING['commands-idchange'] === 'checked' &&
        (
            str_contains($message, '!idchange') ||
            str_contains($message, '!changeid') ||
            str_contains($message, '!chid')
        )
    ) {
        $SETTING['BBS_ID_CHANGE'] = 'checked';
    }
    if ($SETTING['commands-cap'] === 'checked' && str_contains($message, '!cap')) {
        $SETTING['cap_only'] = 'checked';
    }
    if ($SETTING['commands-auth'] === 'checked' && str_contains($message, '!auth')) {
        $SETTING['Authentication_required'] = 'checked';
    }
    if ($SETTING['commands-NO'] === 'checked' && str_contains($message, '!NO')) {
        $SETTING['disable_supervisor'] = 'checked';
    }
    if ($SETTING['commands-AA'] === 'checked' && str_contains($message, '!AA')) {
        $SETTING['BBS_AA'] = 'checked';
    }
    if ($SETTING['commands-ARR'] === 'checked' && str_contains($message, '!ARR')) {
        $SETTING['NAME_ARR'] = 'checked';
    }
    if (
        $SETTING['commands-stop'] === 'checked' && str_contains($message, '!stop') &&
        isset($number) &&
        !$admin
    ) {
        Error('このスレッドは停止しました');
    }
    if ($SETTING['commands-noid'] === 'checked' && str_contains($message, '!noid')) {
        $SETTING['id'] = '';
        $SETTING['slip'] = '';
        $SETTING['disp_slipname'] = '';
        $SETTING['BBS_JP_CHECK'] = '';
    }
    if ($supervisor || $admin) {
        if ($SETTING['commands-stop'] === 'checked' && str_contains($_POST['comment'], '!stop')) {
            $stop = true;
        }
        // 追記
        if (
            $SETTING['commands-add'] === 'checked' && str_contains($_POST['comment'], '!add') &&
            preg_match("/\!add(.*)/", $_POST['comment'], $addMatches) &&
            $number > 1
        ) {
            $commentMax = $authorized ? $SETTING['BBS_MESSAGE_COUNT'] * 3 : $SETTING['BBS_MESSAGE_COUNT'];
            $addComment = $addMatches[1];
            if (mb_strlen($message, 'UTF-8') + mb_strlen($addComment, 'UTF-8') > $commentMax) {
                if (!str_contains($_POST['comment'], '<hr>')) {
                    $_POST['comment'] .= '<hr>';
                }
                $_POST['comment'] .= '★追記できる文字数を超えています。<br>';
            } else {
                $reload = true;
                $messageParts = explode('<hr>', $message);
                $messageParts[0] .= "<br><font class=\"add\" color=\"red\">※追記 {$DATE}</font>{$addComment}";
                $message = implode('<hr>', $messageParts);
            }
        }
        // idchange >>1からの適用
        if (
            $newthread &&
            $SETTING['commands-idchange'] === 'checked' &&
            $SETTING['commands-idchange-first-res'] === 'checked' &&
            (
                str_contains($_POST['comment'], '!idchange') ||
                str_contains($_POST['comment'], '!changeid') ||
                str_contains($_POST['comment'], '!chid')
            )
        ) {
            $SETTING['BBS_ID_CHANGE'] = 'checked';
        }
        // noid >>1からの適用
        if (
            $newthread &&
            $SETTING['commands-noid'] === 'checked' &&
            $SETTING['commands-idchange-first-res'] === 'checked' &&
            str_contains($_POST['comment'], '!noid')
        ) {
            $SETTING['id'] = '';
            $SETTING['slip'] = '';
            $SETTING['disp_slipname'] = '';
            $SETTING['BBS_JP_CHECK'] = '';
        }
    }
}
