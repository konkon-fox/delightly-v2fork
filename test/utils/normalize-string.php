<?php

/**
 * キーワード検索用の文字列正規化関数
 *
 * @param string $str 正規化対象の文字列
 *
 * @return string 正規化された文字列
 */
function normalizeString($str)
{
    $str = mb_convert_kana($str, 'C');
    $str = html_entity_decode($str, ENT_HTML5);
    return $str;
}
