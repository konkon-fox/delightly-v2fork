<?php
// smart phone marks
if (strpos($HOST, 'spmode') !== false) {
 if (strpos($HOST, 'msb') !== false) $SLIP_NAME = "ｽﾌﾟｯｯ";
 elseif (strpos($HOST, 'msc') !== false) $SLIP_NAME = "ｽｯﾌﾟ";
 elseif (strpos($HOST, 'msd') !== false) $SLIP_NAME = "ｽｯｯﾌﾟ";
 elseif (strpos($HOST, 'mse') !== false) $SLIP_NAME = "ｽﾌﾟﾌﾟ";
 elseif (strpos($HOST, 'msf') !== false) $SLIP_NAME = "ｽﾌｯ";
 else $SLIP_NAME = "ｽﾌﾟｰ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'au-net') !== false) {
 if (strpos($HOST, 'KD027') !== false || strpos($HOST, 'kd027') !== false) $SLIP_NAME = "ｱｳｱｳｱｰ";
 elseif (strpos($HOST, 'KD036') !== false || strpos($HOST, 'kd036') !== false) $SLIP_NAME = "ｱｳｱｳｲｰ";  elseif (strpos($HOST, 'KD106') !== false || strpos($HOST, 'kd106') !== false) $SLIP_NAME = "ｱｳｱｳｳｰ";
 elseif (strpos($HOST, 'KD111') !== false || strpos($HOST, 'kd111') !== false) $SLIP_NAME = "ｱｳｱｳｴｰ";
 elseif (strpos($HOST, 'KD119') !== false || strpos($HOST, 'kd119') !== false) $SLIP_NAME = "ｱｳｱｳｵｰ";
 elseif (strpos($HOST, 'KD182249') !== false || strpos($HOST, 'kd182249') !== false || strpos($HOST, 'KD182250') !== false || strpos($HOST, 'kd182250') !== false || strpos($HOST, 'KD1822512') !== false || strpos($HOST, 'kd1822512') !== false) $SLIP_NAME = "ｱｳｱｳｶｰ";
 elseif (strpos($HOST, 'KD182251') !== false || strpos($HOST, 'kd182251') !== false) $SLIP_NAME = "ｱｳｱｳｷｰ";
 elseif (strpos($HOST, 'UQ') !== false || strpos($HOST, 'uq') !== false) { 
  $SLIP_NAME = "ｱｳｱｳｸｰ";
  $MM = true;
 }
 else $SLIP_NAME = "ｱｳｱｳ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'openmobile') !== false) {
 $SLIP_NAME = "ｵｯﾍﾟｹ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'panda-world') !== false) {
 if (strpos($HOST, 'tss') !== false || strpos($HOST, 'pw126152') !== false || strpos($HOST, 'pw126161') !== false || strpos($HOST, 'pw126186') !== false || strpos($HOST, 'pw126199') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾗ";
 elseif (strpos($HOST, 'kyb') !== false || strpos($HOST, 'pw126205') !== false || strpos($HOST, 'pw126214') !== false || strpos($HOST, 'pw126225') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾘ";
 elseif (strpos($HOST, 'pw126236') !== false || strpos($HOST, 'pw126237') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾙ";
 elseif (strpos($HOST, 'pw126245') !== false || strpos($HOST, 'pw126247') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾚ";
 elseif (strpos($HOST, 'pw126253') !== false || strpos($HOST, 'pw126254') !== false || strpos($HOST, 'pw126255') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾛ";
 else $SLIP_NAME = "ｻｻｸｯﾃﾛ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'access-internet') !== false) {
 $SLIP_NAME = "ｱ-ｸｾ-";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'e-mobile') !== false) {
 $SLIP_NAME = "ｴ-ｲﾓ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'emobile') !== false) {
 $SLIP_NAME = "ｲﾓ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'air.mopera.net') !== false) {
 $SLIP_NAME = "ｴｱﾍﾟﾗ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'mopera') !== false) {
 $SLIP_NAME = "ﾍﾟﾗﾍﾟﾗ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'google-proxy') !== false) {
 $SLIP_NAME = "ｸﾞｸﾞﾚｶｽ";
 $SLIP_SP = true;
}elseif (strpos($HOST, 'wi-fi.wi2') !== false) {
 $SLIP_NAME = "ﾜｲｰﾜ2";
 $WF = true;
}elseif (strpos($HOST, 'wi-fi.kddi') !== false) {
 $SLIP_NAME = "ｱｳｳｨﾌ";
 $WF = true;
}elseif (strpos($HOST, 'm-zone') !== false) {
 $SLIP_NAME = "ｴﾑｿﾞﾈ";
 $WF = true;
}elseif (strpos($HOST, 'wi-fi.fc2') !== false) {
 $SLIP_NAME = "ｴﾌｼｰﾂｰ";
 $WF = true;
}elseif (strpos($HOST, 'wi2.co.jp') !== false || strpos($HOST, 'wi2.ne.jp') !== false) {
 $SLIP_NAME = "ﾜｲﾜｲ";
 $WF = true;
}elseif (strpos($HOST, 'freespot.com') !== false) {
 $SLIP_NAME = "ﾌﾘｽﾎﾟ";
 $WF = true;
}elseif (strpos($HOST, '7spot') !== false) {
 $SLIP_NAME = "ｾﾌﾞﾝ";
 $WF = true;
}elseif (strpos($HOST, 'family-wifi') !== false) {
 $SLIP_NAME = "ﾌｧﾐﾏ";
 $WF = true;
}elseif (strpos($HOST, 'freemobile.jp') !== false) {
 $SLIP_NAME = "ﾌﾘﾓﾊﾞ";
 $WF = true;
}elseif (strpos($HOST, 'ntt-bp.net') !== false) {
 $SLIP_NAME = "ﾐｶｶｳｨﾌｨ";
 $WF = true;
}elseif (strpos($HOST, 'wi-fi') !== false) {
 $SLIP_NAME = "ﾜｲｰﾜ";
 $WF = true;
}elseif (strpos($HOST, 'vmobile') !== false) {
 $SLIP_NAME = "ﾌﾞｰｲﾓ";
 $MM = true;
}elseif (strpos($HOST, 'mp') !== false && strpos($HOST, 'ap.nuro.jp') !== false) {
 $SLIP_NAME = "ｿﾈｯﾄ"; // So-net モバイル LTE
 $MM = true;
}elseif (strpos($HOST, 'wimax') !== false || strpos($HOST, 'wmaxuq') !== false) {
 $SLIP_NAME = "ﾜｲﾓﾏｰ";
 $MM = true;
}elseif (strpos($HOST, 'wi-gate.net') !== false) {
 $SLIP_NAME = "ﾜｷｹﾞｰ";
 $MM = true;
}elseif (strpos($HOST, 'kualnet.jp') !== false) {
 $SLIP_NAME = "ﾜｲｴﾃﾞｨ";
 $MM = true;
}elseif (strpos($HOST, 'omed01.tokyo') !== false) {
 $SLIP_NAME = "ﾜﾝﾄﾝｷﾝ";
 $MM = true;
}elseif (strpos($HOST, 'omed01.osaka') !== false) {
 $SLIP_NAME = "ﾊﾞｯﾐﾝｸﾞｸ";
 $MM = true;
}elseif (strpos($HOST, 'mineo') !== false) {
 $SLIP_NAME = "ｵｲｺﾗﾐﾈｵ";
 $MM = true;
}elseif (strpos($HOST, 'neoau1') !== false) {
 $SLIP_NAME = "ﾄﾞﾅﾄﾞﾅｰ";
 $MM = true;
}elseif (strpos($HOST, 'dcm2') !== false) {
 $SLIP_NAME = "ﾄﾞｺｸﾞﾛ";
 $MM = true;
}elseif (strpos($HOST, 'libmo') !== false) {
 $SLIP_NAME = "ﾌﾞﾓｰ";
 $MM = true;
}elseif (strpos($HOST, 'ap.mvno.net') !== false) {
 $SLIP_NAME = "ｱﾒ";
 $MM = true;
}else {
 $SLIP_NAME = "ﾜｯﾁｮｲ";
}
if ($HOST == $_SERVER['REMOTE_ADDR'] && !$ipv6) {
 $SLIP_NAME = "JP";
}
if (strpos($_SERVER['REMOTE_ADDR'], '133.106') !== false || strpos($_SERVER['REMOTE_ADDR'], '193.119') !== false || strpos($_SERVER['REMOTE_ADDR'], '133.100') !== false) {
 $SLIP_NAME = "ﾃﾃﾝﾃﾝﾃﾝ";
 $MM = true;
}
if (strpos($HOST, 'rakuten') !== false || strpos($_SERVER['REMOTE_ADDR'], '240b:c0') !== false) {
 $SLIP_NAME = "ﾗｸｯﾍﾟﾍﾟ";
 $MM = true;
}
if (strpos($_SERVER['REMOTE_ADDR'], '103.5.14') !== false) {
 $SLIP_NAME = "ﾜｲｰﾜ2";
 $WF = true;
}
if (strpos($HOST, '2001:240:24') !== false) {
 $SLIP_NAME = "ﾌﾞｰｲﾓ";
 $MM = true;
}
// IPv6用(特殊)
if (strpos($HOST, '240a:61:') !== false) {
 if (strpos($HOST, '240a:61:a') !== false || strpos($HOST, '240a:61:c') !== false || strpos($HOST, '240a:61:e') !== false || strpos($HOST, '240a:61:1') !== false || strpos($HOST, '240a:61:2') !== false || strpos($HOST, '240a:61:3') !== false || strpos($HOST, '240a:61:4') !== false) $SLIP_NAME = "ｽﾌﾟｯｯ";
 elseif (strpos($HOST, '240a:61:5') !== false || strpos($HOST, '240a:61:6') !== false || strpos($HOST, '240a:61:7') !== false || strpos($HOST, '240a:61:8') !== false || strpos($HOST, '240a:61:b') !== false || strpos($HOST, '240a:61:9') !== false || strpos($HOST, '240a:61:d') !== false || strpos($HOST, '240a:61:f') !== false) $SLIP_NAME = "ｽｯﾌﾟ";
 else $SLIP_NAME = "ｽﾌﾟｰ";
 $SLIP_SP = true;
}
if (strpos($HOST, '240a:6b:') !== false) {
 if (strpos($HOST, '240a:6b:a') !== false || strpos($HOST, '240a:6b:c') !== false || strpos($HOST, '240a:6b:e') !== false || strpos($HOST, '240a:6b:1') !== false || strpos($HOST, '240a:6b:2') !== false || strpos($HOST, '240a:6b:3') !== false || strpos($HOST, '240a:6b:4') !== false) $SLIP_NAME = "ｽｯｯﾌﾟ";
 elseif (strpos($HOST, '240a:6b:5') !== false || strpos($HOST, '240a:6b:6') !== false || strpos($HOST, '240a:6b:7') !== false || strpos($HOST, '240a:6b:8') !== false || strpos($HOST, '240a:6b:b') !== false || strpos($HOST, '240a:6b:9') !== false || strpos($HOST, '240a:6b:d') !== false || strpos($HOST, '240a:6b:f') !== false) $SLIP_NAME = "ｽﾌﾟﾌﾟ";
 else $SLIP_NAME = "ｽﾌﾟｯ";
 $SLIP_SP = true;
}
if (strpos($HOST, '2001:268:9') !== false) {
 if (strpos($HOST, '2001:268:9a') !== false || strpos($HOST, '2001:268:9e') !== false || strpos($HOST, '2001:268:9f') !== false) $SLIP_NAME = "ｱｳｱｳｱｰ";
 elseif (strpos($HOST, '2001:268:9b') !== false || strpos($HOST, '2001:268:9c') !== false || strpos($HOST, '2001:268:9d') !== false) $SLIP_NAME = "ｱｳｱｳｲｰ";
 elseif (strpos($HOST, '2001:268:98') !== false || strpos($HOST, '2001:268:91') !== false) $SLIP_NAME = "ｱｳｱｳｳｰ";
 elseif (strpos($HOST, '2001:268:92') !== false || strpos($HOST, '2001:268:93') !== false) $SLIP_NAME = "ｱｳｱｳｴｰ";
 elseif (strpos($HOST, '2001:268:94') !== false || strpos($HOST, '2001:268:95') !== false) $SLIP_NAME = "ｱｳｱｳｵｰ";
 elseif (strpos($HOST, '2001:268:96') !== false || strpos($HOST, '2001:268:97') !== false) $SLIP_NAME = "ｱｳｱｳｶｰ";
 elseif (strpos($HOST, '2001:268:99') !== false) $SLIP_NAME = "ｱｳｱｳｷｰ";
 else $SLIP_NAME = "ｱｳｱｳ";
 $SLIP_SP = true;
}
if (strpos($HOST, '2400:2200:') !== false) {
 if (strpos($HOST, '2400:2200:a') !== false || strpos($HOST, '2400:2200:c') !== false || strpos($HOST, '2400:2200:e') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾗ";
 elseif (strpos($HOST, '2400:2200:1') !== false || strpos($HOST, '2400:2200:2') !== false || strpos($HOST, '2400:2200:3') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾘ";
 elseif (strpos($HOST, '2400:2200:4') !== false || strpos($HOST, '2400:2200:5') !== false || strpos($HOST, '2400:2200:6') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾙ";
 elseif (strpos($HOST, '2400:2200:7') !== false || strpos($HOST, '2400:2200:8') !== false || strpos($HOST, '2400:2200:b') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾚ";
 elseif (strpos($HOST, '2400:2200:9') !== false || strpos($HOST, '2400:2200:d') !== false || strpos($HOST, '2400:2200:f') !== false) $SLIP_NAME = "ｻｻｸｯﾃﾛﾛ";
 else $SLIP_NAME = "ｻｻｸｯﾃﾛ";
 $SLIP_SP = true;
}
if ($admin) $SLIP_NAME = "★";

// ID末尾表示
if ($HOST == $_SERVER['REMOTE_ADDR'] && !$ipv6) $slip = "H";
if ($MM) $slip = "M";
elseif ($WF) $slip = "F";
elseif (strpos($HOST, 'spmode') !== false || strpos($HOST, '240a:61:') !== false || strpos($HOST, '240a:6b:') !== false) $slip = "d";
elseif (strpos($HOST, 'au-net') !== false || strpos($HOST, '2001:268:9') !== false) $slip = "a";
elseif (strpos($HOST, 'panda-world') !== false || strpos($HOST, '2400:2200:') !== false) $slip = "p";
elseif (strpos($HOST, 'openmobile') !== false) $slip = "r";
elseif (strpos($HOST, 'access-internet') !== false) $slip = "x";
elseif (strpos($HOST, 'e-mobile') !== false) $slip = "E";
elseif (strpos($HOST, 'mopera.net') !== false) $slip = "D";
elseif (strpos($HOST, 'google-proxy') !== false) $slip = "X";