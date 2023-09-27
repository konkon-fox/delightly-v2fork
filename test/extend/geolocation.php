<?php
 $options =array(
        'http' =>array(
                'method' => "GET",
                )
        );
 $url = "http://ip-api.com/json/".$_SERVER['REMOTE_ADDR']."?fields=countryCode,regionName,city,asname,mobile,proxy,hosting&lang=ja";
 $cp = curl_init();
 /*オプション:リダイレクトされたらリダイレクト先のページを取得する*/
 curl_setopt($cp, CURLOPT_RETURNTRANSFER, 1);
 /*オプション:URLを指定する*/
 curl_setopt($cp, CURLOPT_URL, $url);
 /*オプション:タイムアウト時間を指定する*/
 curl_setopt($cp, CURLOPT_TIMEOUT, 2000);
 /*オプション:ユーザーエージェントを指定する*/
curl_setopt($cp, CURLOPT_USERAGENT, "Mozilla/5.0 P2/2.5 (iPad; CPU OS 13_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/87.0.4280.77 Mobile/15E148 Safari/604.1");
 curl_setopt($cp, CURLOPT_HEADER, true);
 $source = curl_exec($cp);
 $curlInfo = curl_getinfo($cp);
   // ヘッダを一緒に出力したときは分割させる
   $headerSize = false;
   if ( isset($curlInfo["header_size"]) && $curlInfo["header_size"]!="" ) {
      $headerSize = $curlInfo["header_size"];
   }
   $head = substr($source, 0, $headerSize); // ヘッダ部
 $head = str_replace(["\r\n", "\r", "\n"], "\n", $head);
 $header = explode("\n", $head);
 foreach ($header as $tmp) {
 list($key, $value) = explode(": ", $tmp);
 $HTTP[$key] = $value;
 }
   $data = substr($source, $headerSize);    // ボディ部
 curl_close($cp);
 $area = json_decode($data, true);
 if (!$area["asname"]) $area["asname"] = preg_replace("/[0-9]/", "", $HOST);
 // 国名取得(CFを通さないサーバの場合)
 if (empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
  if ($area["countryCode"]) $_SERVER['HTTP_CF_IPCOUNTRY'] = $area["countryCode"];
  else $_SERVER['HTTP_CF_IPCOUNTRY'] = "JP";
 }
 // モバイルを検出
 if ($area['mobile'] == true && $slip == "0" && strpos($HOST, 'bbtec.net') === false && strpos($HOST, 'ocn.ne.jp') === false && strpos($HOST, 'dion.ne.jp') === false) {
  $slip = "S";
  $SLIP_SP = true;
  $SLIP_NAME = $area["asname"];
 }
 // 県名
 if (!$area["regionName"]) $area["regionName"] = "catv?";
 if ($SETTING['BBS_JP_CHECK'] == 1) $ken = $area["regionName"];
 elseif ($SETTING['BBS_JP_CHECK'] == 2) $ken = $area["city"];
 elseif ($SETTING['BBS_JP_CHECK'] == 3) $ken = $area["regionName"].$area["city"];
 elseif ($SETTING['BBS_JP_CHECK'] == 4) $ken = $area["asname"];
 elseif ($SETTING['BBS_JP_CHECK'] == 5) $ken = $area["regionName"]." ".$area["asname"];
 elseif ($SETTING['BBS_JP_CHECK'] == 6) $ken = $area["regionName"].$area["city"]." ".$area["asname"];
 if ($area["city"] == "Chiyoda") {
  if ($slip == "d") $ken = "茸";
  elseif ($slip != "0") $ken = "光";
  elseif ($area["asname"] == "KDDI" || strpos($HOST, 'dion.ne.jp') !== false) $ken = "dion軍";
 }
 if ($area["city"] == "東京" || $area["city"] == "大阪市") {
  if ($slip == "d") $ken = "茸";
  elseif ($slip == "M") $ken = "ジパング";
  elseif ($slip != "0") $ken = "光";
  elseif ($area["asname"] == "KDDI" || strpos($HOST, 'dion.ne.jp') !== false) $ken = "dion軍";
 }
 if ($area["city"] == "港区") {
  if ($slip == "p") $ken = "SB-iPhone";
  elseif ($slip == "r") $ken = "SB-Android";
  elseif ($area["asname"] == "GIGAINFRA" || strpos($HOST, 'bbtec.net') !== false) $ken = "やわらか銀行";
 }
 $ken = trim($ken);
 // PROXYを検出
 if ($area['proxy'] == true) {
  if ($SETTING['BBS_BBX_PASS'] != "on" && !$authorized) Error("未承認ユーザーはVPN・PROXYから投稿することはできません");
  $slip = "8";
 }
 // hostingを検出
 if ($area['hosting'] == true && $slip != "8") {
  if ($SETTING['BBS_BBX_PASS'] != "on" && !$authorized) Error("未承認ユーザーはhostingから投稿することはできません");
  $slip = "h";
 }

 $provider = $area["regionName"].$area["asname"].$area['mobile'];