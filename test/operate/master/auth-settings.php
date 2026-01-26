<?php

require_once './utils/get-json-file.php';

$settingFile = './operate/auth-settings.json';
if (is_file($settingFile)) {
    $settings = getJsonFile($settingFile);
} else {
    $settings = [];
}
if ($settings === false) {
    exit('認証設定の取得に失敗しました。');
}
if ($_POST['edit'] === 'true') {
    $settingNames = [
        'turnstile-sitekey',
        'turnstile-secretkey',
        'use-cloudflare',
        'use-strict-auth',
        'use-browser-fingerprint',
    ];
    foreach ($settingNames as $settingName) {
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
	<title>認証設定</title>
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
		<h1>認証設定</h1>
		<main>
			<form action="?mode=auth-settings" method="post">
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
					<div class="w-100">
						<label
							for="turnstile-sitekey"
							class="form-label"
							>Turnstile Sitekey</label
						>
						<input
							type="text"
							class="form-control"
							id="turnstile-sitekey"
							name="turnstile-sitekey"
							placeholder="1x00000000000000000000AA"
							value="<?= isset($settings['turnstile-sitekey']) ? $settings['turnstile-sitekey'] : ''; ?>"
						/>
					</div>
					<div class="w-100">
						<label
							for="turnstile-secretkey"
							class="form-label"
							>Turnstile Secret key</label
						>
						<input
							type="text"
							class="form-control"
							id="turnstile-secretkey"
							name="turnstile-secretkey"
							placeholder="1x0000000000000000000000000000000AA"
							value="<?= isset($settings['turnstile-secretkey']) ? $settings['turnstile-secretkey'] : ''; ?>"
						/>
					</div>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							value="checked"
							<?= !isset($settings['use-cloudflare']) || $settings['use-cloudflare'] === 'checked' ? 'checked' : ''; ?>
							id="use-cloudflare"
							name="use-cloudflare"
						/>
						<label
							class="form-check-label"
							for="use-cloudflare"
						>
							Cloudflareを使用する
						</label>
					</div>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							value="checked"
							<?= !isset($settings['use-strict-auth']) || $settings['use-strict-auth'] === 'checked' ? 'checked' : ''; ?>
							id="use-strict-auth"
							name="use-strict-auth"
						/>
						<label
							class="form-check-label"
							for="use-strict-auth"
						>
						  VPN等での認証を禁止する
						</label>
					</div>
					<div class="form-check">
						<input
							class="form-check-input"
							type="checkbox"
							value="checked"
							<?= isset($settings['use-browser-fingerprint']) && $settings['use-browser-fingerprint'] === 'checked' ? 'checked' : ''; ?>
							id="use-browser-fingerprint"
							name="use-browser-fingerprint"
						/>
						<label
							class="form-check-label"
							for="use-browser-fingerprint"
						>
						  認証時の環境チェックにブラウザ情報を使用する
						</label><br>
						※同意鍵が被りにくくなる代わりに複垢が作りやすくなります。
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