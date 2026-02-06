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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['edit'] === 'yes') {
    $extraCommandsList = [
        'commands-sage',
        'commands-nopic',
        'commands-jien',
        'commands-live',
        'commands-slip',
        'commands-slipname',
        'commands-ken',
        'commands-id',
        'commands-siberia',
        'commands-day',
        'commands-month',
        'commands-year',
        'commands-host',
        'commands-clientid',
        'commands-nolink',
        'commands-idchange',
        'commands-cap',
        'commands-auth',
        'commands-NO',
        'commands-AA',
        'commands-ARR',
        'commands-stop',
        'commands-noid',
        'commands-add',
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
  <title>基本コマンドのオンオフ設定</title>
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
		<h1>基本コマンドのオンオフ設定</h1>
    <main>
      <p>
        >>1に文字列があると有効になるコマンド類です。
      </p>
      <form method="post" action="">
      <input type="hidden" name="password" value="<?=$_POST['password'];?>">
      <input type="hidden" name="edit" value="yes">
      <div class="d-flex flex-column align-items-start row-gap-2">
        <!--  -->
        <div class="row">
          <div class="col-auto fw-bold">!sage</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-sage"
              <?= $SETTING['commands-sage'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!nopic</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-nopic"
              <?= $SETTING['commands-nopic'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!jien</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-jien"
              <?= $SETTING['commands-jien'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!live</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-live"
              <?= $SETTING['commands-live'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!slip</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-slip"
              <?= $SETTING['commands-slip'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!slipname</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-slipname"
              <?= $SETTING['commands-slipname'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!ken</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-ken"
              <?= $SETTING['commands-ken'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!id</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-id"
              <?= $SETTING['commands-id'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!siberia</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-siberia"
              <?= $SETTING['commands-siberia'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!day</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-day"
              <?= $SETTING['commands-day'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!month</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-month"
              <?= $SETTING['commands-month'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!year</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-year"
              <?= $SETTING['commands-year'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!host</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-host"
              <?= $SETTING['commands-host'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!clientid</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-clientid"
              <?= $SETTING['commands-clientid'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!nolink</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-nolink"
              <?= $SETTING['commands-nolink'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!idchange</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-idchange"
              <?= $SETTING['commands-idchange'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!cap</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-cap"
              <?= $SETTING['commands-cap'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!auth</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-auth"
              <?= $SETTING['commands-auth'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!NO</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-NO"
              <?= $SETTING['commands-NO'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!AA</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-AA"
              <?= $SETTING['commands-AA'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!ARR</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-ARR"
              <?= $SETTING['commands-ARR'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!stop</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-stop"
              <?= $SETTING['commands-stop'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!noid</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-noid"
              <?= $SETTING['commands-noid'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
          </div>
        </div>
        <div class="row">
          <div class="col-auto fw-bold">!add</div>
          <div class="col-auto">
            <label
              ><input type="checkbox" value="checked" name="commands-add"
              <?= $SETTING['commands-add'] === 'checked' ? 'checked' : ''; ?>>有効</label
            >
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