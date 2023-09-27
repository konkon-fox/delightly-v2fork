<?php
 if ($SETTING['commands'] == "checked") {
   if (strpos($message, '!sage') !== false) $sage = true;
   if (strpos($message, '!nopic') !== false) $SETTING['NOPIC'] = "checked";
   if (strpos($message, '!noid') !== false) {
   $SETTING['id'] = "";
   $SETTING['slip'] = "";
   $SETTING['disp_slipname'] = "";
   $SETTING['BBS_JP_CHECK'] = "";
   }
   if (strpos($message, '!jien') !== false) {
   $SETTING['id'] = "checked";
   $SETTING['slip'] = "checked";
   $SETTING['disp_slipname'] = "checked";
   $SETTING['BBS_JP_CHECK'] = "checked";
   $SETTING['ID_RESET'] = "year";
   }
   if (strpos($message, '!live') !== false) {
   $SETTING['threadcheck'] = "";
   $SETTING['timecheck'] = "";
   if ($SETTING['BBS_SAMBA24'] > 0) $SETTING['BBS_SAMBA24'] = $SETTING['BBS_SAMBA24'] / 2;
   }
   if (strpos($message, '!slip') !== false) $SETTING['slip'] = "checked";
   if (strpos($message, '!slipname') !== false) $SETTING['disp_slipname'] = "checked";
   if (strpos($message, '!ken') !== false) $SETTING['BBS_JP_CHECK'] = "checked";
   if (strpos($message, '!id') !== false) $SETTING['id'] = "checked";
   if (strpos($message, '!siberia') !== false) $SETTING['id'] = "siberia";
   if (strpos($message, '!day') !== false) $SETTING['ID_RESET'] = "day";
   if (strpos($message, '!month') !== false) $SETTING['ID_RESET'] = "month";
   if (strpos($message, '!year') !== false) $SETTING['ID_RESET'] = "year";
   if (strpos($message, '!host') !== false) $SETTING['fusianasan'] = "name";
   if (strpos($message, '!clientid') !== false) $SETTING['fusianasan'] = "id";
   if (strpos($message, '!nolink') !== false) $SETTING['DISABLE_LINK'] = "checked";
   if (strpos($message, '!idchange') !== false) $SETTING['BBS_ID_CHANGE'] = "checked";
   if (strpos($message, '!cap') !== false) $SETTING['cap_only'] = "checked";
   if (strpos($message, '!auth') !== false) $SETTING['Authentication_required'] = "checked";
   if (strpos($message, '!NO') !== false) $SETTING['disable_supervisor'] = "checked";
   if (strpos($message, '!AA') !== false) $SETTING['BBS_AA'] = "checked";
   if (strpos($message, '!ARR') !== false) $SETTING['NAME_ARR'] = "checked";
   if (strpos($message, '!stop') !== false && $number != 1) Error("このスレッドは停止しました");
   // !SETTING
   if (preg_match_all("/!SETTING:(.*?):(.*?)(\s|　|<br>)/", $_POST['comment'], $SETS, PREG_SET_ORDER)) {
    foreach ($SETS as $SET) {
    $SETTING[$SET[1]] = $SET[2];
    }
   }
if ($supervisor || $admin) {
  if (strpos($_POST['comment'], "!") !== false) $reload = true;
  if (strpos($_POST['comment'], '!stop') !== false) $stop = true;
   // 追記
   if (preg_match("/!add(.*)/", $_POST['comment'], $match) && $number != 1) {
    $message .="<br><font class=\"add\" color=\"red\">※追記 {$DATE}</font>{$match[1]}";
   }
  // 部分削除
   if (preg_match_all("/!saku:(.*?)(\s|　|<br>)/", $_POST['comment'], $sakus, PREG_SET_ORDER)) {
    foreach ($sakus as $sakujyo) {
    $message = str_replace($sakujyo[1],'',$message);
    }
   }
}
 }