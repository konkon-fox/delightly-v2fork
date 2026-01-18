<?php

include './utils/get-json-file.php';

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
				<div class="d-flex flex-column row-gap-3 align-items-start">
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
					<button
						type="submit"
						class="btn btn-primary"
					>
						適用
					</button>
				</div>
			</form>
		</main>
</body>
</html>