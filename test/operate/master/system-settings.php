<?php

require_once './utils/get-json-file.php';

$settingFile = './operate/system-settings.json';
if (is_file($settingFile)) {
    $settings = getJsonFile($settingFile);
} else {
    $settings = [];
}
if ($settings === false) {
    exit('システム設定の取得に失敗しました。');
}

$settings['enable_host_lookup'] ??= '';
$settings['auth_log_ttl'] ??= '90';
$settings['key_error_log_ttl'] ??= '90';
$settings['KEY_ERROR_DEFAULT_HOUR'] ??= '24';
$settings['KEY_ERROR_DEFAULT_CHANCE'] ??= '10';

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
	<title>システム設定</title>
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
		<h1>システム設定</h1>
		<main>
			<form action="?mode=system-settings" method="post">
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
				<ul class="list-group">
					<li class="list-group-item">
						<div class="form-check">
							<input
								class="form-check-input"
								type="checkbox"
								value="checked"
								<?=  $settings['enable_host_lookup'] === 'checked' ? 'checked' : ''; ?>
								id="enable_host_lookup"
								name="enable_host_lookup"
							/>
							<label
								class="form-check-label"
								for="enable_host_lookup"
							>
								投稿者のIPアドレスからホストを逆引きする<br>
								※投稿時の処理時間が少し長くなる可能性があります。
							</label>
						</div>
					</li>
					<li class="list-group-item">
						<label for="auth_log_ttl" class="form-label">認証ログの保存期間(日)</label>
						<div class="row">
							<div class="col-4 col-md-2">
								<input 
									type="number"
									class="form-control d-inline-block"
									id="auth_log_ttl"
									name="auth_log_ttl"
									placeholder="90"
									value="<?= htmlspecialchars($settings['auth_log_ttl'], ENT_QUOTES, 'UTF-8'); ?>"
								>
							</div>
						</div>
					</li>
					<li class="list-group-item">
						<label for="key_error_log_ttl" class="form-label">同意鍵エラーログの保存期間(日)</label>
						<div class="row">
							<div class="col-4 col-md-2">
								<input
									type="number" 
									class="form-control d-inline-block" 
									id="key_error_log_ttl" 
									name="key_error_log_ttl" 
									placeholder="90"
									value="<?= htmlspecialchars($settings['key_error_log_ttl'], ENT_QUOTES, 'UTF-8'); ?>"
								>
							</div>
						</div>
					</li>
					<li class="list-group-item">
						<label for="KEY_ERROR_DEFAULT_HOUR" class="form-label">同意鍵エラーの規定時間(時間)</label>
						<div class="row">
							<div class="col-4 col-md-2">
								<input
									type="number" 
									class="form-control d-inline-block" 
									id="KEY_ERROR_DEFAULT_HOUR" 
									name="KEY_ERROR_DEFAULT_HOUR" 
									placeholder="24"
									value="<?= htmlspecialchars($settings['KEY_ERROR_DEFAULT_HOUR'], ENT_QUOTES, 'UTF-8'); ?>"
								>
							</div>
						</div>
						<label for="KEY_ERROR_DEFAULT_CHANCE" class="form-label mt-2">同意鍵エラーの規定回数</label>
						<div class="row">
							<div class="col-4 col-md-2">
								<input
									type="number" 
									class="form-control d-inline-block" 
									id="KEY_ERROR_DEFAULT_CHANCE" 
									name="KEY_ERROR_DEFAULT_CHANCE" 
									placeholder="10"
									value="<?= htmlspecialchars($settings['KEY_ERROR_DEFAULT_CHANCE'], ENT_QUOTES, 'UTF-8'); ?>"
								>
							</div>
						</div>
						<p class="mt-2">
							h = 同意鍵エラーの規定時間<br>
							x = 同意鍵エラーの規定回数<br>
							とした場合、過去h時間以内にx回数同意鍵の入力に失敗していると、同意鍵の検証前に投稿失敗となります。
						</p>
					</li>
				</ul>
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