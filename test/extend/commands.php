<?php

if ($SETTING['commands'] === 'checked') {
    if (str_contains($message, '!sage')) {
        $sage = true;
    }
    if (str_contains($message, '!nopic')) {
        $SETTING['NOPIC'] = 'checked';
    }
    if (str_contains($message, '!jien')) {
        $SETTING['id'] = 'checked';
        $SETTING['slip'] = 'checked';
        $SETTING['disp_slipname'] = 'checked';
        $SETTING['BBS_JP_CHECK'] = 'checked';
        $SETTING['ID_RESET'] = 'year';
    }
    if (str_contains($message, '!live')) {
        $SETTING['threadcheck'] = '';
        $SETTING['timecheck'] = '';
        if ($SETTING['BBS_SAMBA24'] > 0) {
            $SETTING['BBS_SAMBA24'] = $SETTING['BBS_SAMBA24'] / 2;
        }
    }
    if (str_contains($message, '!slip')) {
        $SETTING['slip'] = 'checked';
    }
    if (str_contains($message, '!slipname')) {
        $SETTING['disp_slipname'] = 'checked';
    }
    if (str_contains($message, '!ken')) {
        $SETTING['BBS_JP_CHECK'] = 'checked';
    }
    if (str_contains($message, '!id')) {
        $SETTING['id'] = 'checked';
    }
    if (str_contains($message, '!siberia')) {
        $SETTING['id'] = 'siberia';
    }
    if (str_contains($message, '!day')) {
        $SETTING['ID_RESET'] = 'day';
    }
    if (str_contains($message, '!month')) {
        $SETTING['ID_RESET'] = 'month';
    }
    if (str_contains($message, '!year')) {
        $SETTING['ID_RESET'] = 'year';
    }
    if (str_contains($message, '!host')) {
        $SETTING['fusianasan'] = 'name';
    }
    if (str_contains($message, '!clientid')) {
        $SETTING['fusianasan'] = 'id';
    }
    if (str_contains($message, '!nolink')) {
        $SETTING['DISABLE_LINK'] = 'checked';
    }
    if (
        str_contains($message, '!idchange') ||
        str_contains($message, '!changeid') ||
        str_contains($message, '!chid')
    ) {
        $SETTING['BBS_ID_CHANGE'] = 'checked';
    }
    if (str_contains($message, '!cap')) {
        $SETTING['cap_only'] = 'checked';
    }
    if (str_contains($message, '!auth')) {
        $SETTING['Authentication_required'] = 'checked';
    }
    if (str_contains($message, '!NO')) {
        $SETTING['disable_supervisor'] = 'checked';
    }
    if (str_contains($message, '!AA')) {
        $SETTING['BBS_AA'] = 'checked';
    }
    if (str_contains($message, '!ARR')) {
        $SETTING['NAME_ARR'] = 'checked';
    }
    if (
        str_contains($message, '!stop') &&
        isset($number) &&
        !$admin
    ) {
        Error('このスレッドは停止しました');
    }
    if (str_contains($message, '!noid')) {
        $SETTING['id'] = '';
        $SETTING['slip'] = '';
        $SETTING['disp_slipname'] = '';
        $SETTING['BBS_JP_CHECK'] = '';
    }
    // !SETTING
    if (preg_match_all("/!SETTING:(.*?):(.*?)(\s|　|<br>)/", $_POST['comment'], $SETS, PREG_SET_ORDER)) {
        foreach ($SETS as $SET) {
            $SETTING[$SET[1]] = $SET[2];
        }
    }
    if ($supervisor || $admin) {
        if (str_contains($_POST['comment'], '!stop')) {
            $stop = true;
        }
        // 追記
        if (preg_match("/\!add(.*)/", $_POST['comment'], $addMatches) && isset($number)) {
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
        // 部分削除
        if (preg_match_all("/!saku:(.*?)(\s|　|<br>)/", $_POST['comment'], $sakus, PREG_SET_ORDER)) {
            foreach ($sakus as $sakujyo) {
                $message = str_replace($sakujyo[1], '', $message);
            }
        }
        // idchange
        if (
            $newthread &&
            (
                str_contains($_POST['comment'], '!idchange') ||
                str_contains($_POST['comment'], '!changeid') ||
                str_contains($_POST['comment'], '!chid')
            )
        ) {
            $SETTING['BBS_ID_CHANGE'] = 'checked';
        }
        // noid
        if ($newthread && strpos($_POST['comment'], '!noid') !== false) {
            $SETTING['id'] = '';
            $SETTING['slip'] = '';
            $SETTING['disp_slipname'] = '';
            $SETTING['BBS_JP_CHECK'] = '';
        }
    }
}
