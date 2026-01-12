<?php
/**
 * ネットワーク帯域に基づいた識別文字($slip=末尾)を生成する関数
 * 
 * @return string 生成された末尾
 */
function getEndChar(){
  // ipの一部を取得
  $ip = $_SERVER['REMOTE_ADDR'];
  $binaryIp = inet_pton($ip);
  if($binaryIp===false){
    return '？';
  }
  $isIpv6 = strlen($binaryIp) === 16;
  if ($isIpv6) {
      // IPv6の場合
      // 先頭4バイト (32bit) を抽出して16進数に戻す
      $ipPart = bin2hex(substr($binaryIp, 0, 4));
  } else {
      // IPv4の場合
      // 先頭1バイト (8bit) を抽出して16進数に戻す
      $ipPart = bin2hex(substr($binaryIp, 0, 1));
  }

  // 識別子リスト
  $slipList = ["S", "M", "V", "E", "J", "U", "N", "P", "G"];
  // 識別子選定
  $index = abs(crc32($ipPart)) % count($slipList);

  return $slipList[$index];
}