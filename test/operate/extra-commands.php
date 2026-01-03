<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['edit'] === 'yes') {
    $extraCommandsList = [
      'commands-max',
      'commands-dice',
      'commands-774',
      'commands-gobi',
      'commands-chtt',
      'commands-ninkey',
      'commands-pool',
      'commands-rmj',
      'commands-ngk',
      'commands-sticky',
    ];
    foreach ($extraCommandsList as $settingName) {
        if (isset($_POST[$settingName])) {
            $SETTING[$settingName] = $_POST[$settingName];
        } else {
            $SETTING[$settingName] = '';
        }
    }
    foreach ($SETTING as $name => $value) {
        $SET .= $name . '=' . $SETTING[$name] . "\n";
    }
    file_put_contents($setfile, json_encode($SETTING, JSON_UNESCAPED_UNICODE), LOCK_EX);
    file_put_contents($settxt, mb_convert_encoding($SET, 'SJIS-win', 'UTF-8'), LOCK_EX);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content>
  <meta name="author" content>
  <title>追加コマンドのオンオフ設定</title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
		crossorigin="anonymous"
	/>
</head>
<body>
<?php
$bbs = basename($_REQUEST['bbs']);
$safeBbs = htmlspecialchars($bbs, ENT_QUOTES, 'UTF-8');
?>
  <div class="container d-flex flex-column row-gap-2">
    <header class="d-flex flex-column row-gap-1">
      <form action="?bbs=<?= $safeBbs; ?>" method="post">
        <input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
        <button type="submit" class="btn btn-sm btn-secondary">← 管理ページへ戻る</button>
      </form>
      <form action="?bbs=<?= $safeBbs; ?>&mode=boardsetting" method="post">
        <input type="hidden" name="password" value="<?=htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');?>">
        <button type="submit" class="btn btn-sm btn-secondary">← 掲示板設定へ戻る</button>
      </form>
    </header>
		<h1>追加コマンドのオンオフ設定</h1>
    <main>
      <form method="post" action="">
      <input type="hidden" name="password" value="<?=$_POST['password'];?>">
      <input type="hidden" name="edit" value="yes">
      <!-- 以降各コマンドのオンオフ設定 アルファベット順 -->
      <div class="d-flex flex-column align-items-start row-gap-2">
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!chtt</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-chtt" <?= !isset($SETTING['commands-chtt']) || $SETTING['commands-chtt'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">dice(!xDy)</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-dice" <?= !isset($SETTING['commands-dice']) || $SETTING['commands-dice'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!gobi</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-gobi" <?= !isset($SETTING['commands-gobi']) || $SETTING['commands-gobi'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!max</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-max" <?= !isset($SETTING['commands-max']) || $SETTING['commands-max'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!ngk</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-ngk" <?= !isset($SETTING['commands-ngk']) || $SETTING['commands-ngk'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!ninkey</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-ninkey" <?= !isset($SETTING['commands-ninkey']) || $SETTING['commands-ninkey'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!pool</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-pool" <?= !isset($SETTING['commands-pool']) || $SETTING['commands-pool'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!rmj</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-rmj" <?= !isset($SETTING['commands-rmj']) || $SETTING['commands-rmj'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!sticky</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-sticky" <?= !isset($SETTING['commands-sticky']) || $SETTING['commands-sticky'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!774</div>
          <div class="col-auto">
            <label>
              <input type="checkbox" value="checked" name="commands-774" <?= !isset($SETTING['commands-774']) || $SETTING['commands-774'] === 'checked' ? 'checked' : ''; ?>>
              有効
            </label>
          </div>
        </div>
        <!--  -->
      </div>
      <hr>
      <button type="submit" class="btn btn-primary">適用</button>
      </form>
    </main>
  </div>
</body>
</html>