<?php

require_once './utils/get-json-file.php';

$settingFile = './operate/data/commands-constant-settings.json';
if (is_file($settingFile)) {
    $settings = getJsonFile($settingFile);
} else {
    $settings = [];
}
if ($settings === false) {
    exit('コマンド定数設定の取得に失敗しました。');
}

$settings['DICE_MAX_NUM_OF_DICE'] ??= '100';
$settings['DICE_MAX_DICE_VALUE'] ??= '100';
$settings['DICE_LIMIT'] ??= '5';
$settings['MAX_NEW_MAX'] ??= '4000';
$settings['RMJ_MAX_NEST'] ??= '3';
$settings['RMJ_MAX_RMJ_NUMBER'] ??= '20';
$settings['RMJ_REPLACE_LIMIT'] ??= '100';
$settings['COMMA_LIMIT'] ??= '20';
$settings['COMMA_COMMENT_LIMIT'] ??= '100';
$settings['GOBI_MAX_GOBI_LENGTH'] ??= '100';
$settings['RMJ_MIN_LV'] ??= '0';
$settings['INTERVAL_MIN_SECOND'] ??= '0';
$settings['INTERVAL_MAX_SECOND'] ??= '60';

if ($_POST['edit'] === 'true') {
    foreach ($settings as $settingName => $_) {
        if (isset($_POST[$settingName])) {
            $settings[$settingName] = $_POST[$settingName];
        } else {
            $settings[$settingName] = '';
        }
    }
    file_put_contents($settingFile, json_encode($settings, JSON_UNESCAPED_UNICODE), LOCK_EX);
}

?><!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content>
	<meta name="author" content>
	<title>コマンド定数設定</title>
	<link
		href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
		rel="stylesheet"
		integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM"
		crossorigin="anonymous"
	/>
</head>
<body>
	<div class="container d-flex flex-column row-gap-2">
		<header>
			<form action="master.php" method="post">
				<input type="hidden" name="code" value="<?= htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8'); ?>">
				<button type="submit" class="btn btn-sm btn-secondary">← 管理ページへ戻る</button>
			</form>
		</header>
		<h1>コマンド定数設定</h1>
		<main>
			<p>
				各コマンドのオンオフ設定は板管理ページから行ってください。
			</p>
			<form action="?mode=commands-constant-settings" method="post">
				<input
					type="hidden"
					name="code"
					value="<?=htmlspecialchars($_POST['code'], ENT_QUOTES, 'UTF-8');?>"
				/>
				<input
					type="hidden"
					name="edit"
					value="true"
				/>
				<div class="d-flex flex-column row-gap-2">
					<!--  -->
					<div class="card">
						<div class="card-header">!comma</div>
						<div class="card-body">
							<label for="COMMA_LIMIT" class="form-label">コンマセット可能な数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="COMMA_LIMIT"
										placeholder="20"
										value="<?= htmlspecialchars($settings['COMMA_LIMIT'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="COMMA_COMMENT_LIMIT" class="form-label">コンマセット可能な文章の最大文字数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="COMMA_COMMENT_LIMIT"
										placeholder="100"
										value="<?= htmlspecialchars($settings['COMMA_COMMENT_LIMIT'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
						</div>
					</div>
					<!--  -->
					<div class="card">
						<div class="card-header">!xDy (ダイスコマンド)</div>
						<div class="card-body">
							<label for="DICE_MAX_NUM_OF_DICE" class="form-label">振れるダイスxの最大数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="DICE_MAX_NUM_OF_DICE"
										placeholder="100"
										value="<?= htmlspecialchars($settings['DICE_MAX_NUM_OF_DICE'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="DICE_MAX_DICE_VALUE" class="form-label">ダイスの出目yの最大数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="DICE_MAX_DICE_VALUE"
										placeholder="100"
										value="<?= htmlspecialchars($settings['DICE_MAX_DICE_VALUE'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="DICE_LIMIT" class="form-label">1レス内でダイスコマンドが発火する最大回数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="DICE_LIMIT"
										placeholder="5"
										value="<?= htmlspecialchars($settings['DICE_LIMIT'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
						</div>
					</div>
					<!--  -->
					<div class="card">
						<div class="card-header">!gobi</div>
						<div class="card-body">
							<label for="GOBI_MAX_GOBI_LENGTH" class="form-label">語尾の最大文字数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="GOBI_MAX_GOBI_LENGTH"
										placeholder="100"
										value="<?= htmlspecialchars($settings['GOBI_MAX_GOBI_LENGTH'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
						</div>
					</div>
					<!--  -->
					<div class="card">
						<div class="card-header">!interval</div>
						<div class="card-body">
							<label for="INTERVAL_MIN_SECOND" class="form-label">設定可能な投稿間隔(秒)の最小値</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="INTERVAL_MIN_SECOND"
										placeholder="0"
										value="<?= htmlspecialchars($settings['INTERVAL_MIN_SECOND'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="INTERVAL_MAX_SECOND" class="form-label">設定可能な投稿間隔(秒)の最大値</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="INTERVAL_MAX_SECOND"
										placeholder="60"
										value="<?= htmlspecialchars($settings['INTERVAL_MAX_SECOND'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
						</div>
					</div>
					<!--  -->
					<div class="card">
						<div class="card-header">!max</div>
						<div class="card-body">
							<label for="MAX_NEW_MAX" class="form-label">設定可能なレス上限の最大値</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="MAX_NEW_MAX"
										placeholder="4000"
										value="<?= htmlspecialchars($settings['MAX_NEW_MAX'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
						</div>
					</div>
					<!--  -->
					<div class="card">
						<div class="card-header">!rmj</div>
						<div class="card-body">
							<label for="RMJ_MAX_NEST" class="form-label">入れ子の最大数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="RMJ_MAX_NEST"
										placeholder="3"
										value="<?= htmlspecialchars($settings['RMJ_MAX_NEST'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="RMJ_MAX_RMJ_NUMBER" class="form-label">!rmj◯で使用可能な番号の最大数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="RMJ_MAX_RMJ_NUMBER"
										placeholder="20"
										value="<?= htmlspecialchars($settings['RMJ_MAX_RMJ_NUMBER'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="RMJ_REPLACE_LIMIT" class="form-label">!rmj構文を置換する上限数</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="RMJ_REPLACE_LIMIT"
										placeholder="100"
										value="<?= htmlspecialchars($settings['RMJ_REPLACE_LIMIT'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
							<hr>
							<label for="RMJ_MIN_LV" class="form-label">!rmj使用可能な最低レベル</label>
							<div class="row">
								<div class="col-4 col-md-2">
									<input 
										type="number"
										class="form-control d-inline-block"
										name="RMJ_MIN_LV"
										placeholder="0"
										value="<?= htmlspecialchars($settings['RMJ_MIN_LV'], ENT_QUOTES, 'UTF-8'); ?>"
									>
								</div>
							</div>
						</div>
					</div>
					<!--  -->
				</div>
				<button
					type="submit"
					class="btn btn-primary mt-2"
				>
					適用
				</button>
			</form>
		</main>
</body>
</html>