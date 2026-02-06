<?php

/**
 * NetDNS2ライブラリを利用してIPアドレスからホストを逆引きする関数
 * v2.0.7
 *
 * @param int $NOWTIME 投稿時のUNIX秒
 *
 * @return string ホスト名 or IPアドレス(失敗時)
 */
function getHostByIp($NOWTIME)
{
    // キャッシュ有効期限(デフォルト3日間)
    $CAHCHE_TTL = 3 * 24 * 60 * 60;

    // ipアドレスのハッシュを計算
    $ip = $_SERVER['REMOTE_ADDR'];
    $ipHash = md5($ip);

    // キャッシュファイルを検索
    $cacheDir = dirname(__FILE__, 2) . '/hosts';
    $cacheFile = $cacheDir . '/host_' . $ipHash . '.txt';
    if (is_file($cacheFile) && filemtime($cacheFile) + $CAHCHE_TTL >= $NOWTIME) {
        $content = safe_file_get_contents($cacheFile);
        if ($content !== false) {
            // キャッシュの中身を返却
            return $content;
        }
    }

    // 古いキャッシュを削除
    if (mt_rand(1, 1000) === 1) {
        foreach (glob($cacheDir . '/host_*.txt') as $file) {
            if (filemtime($file) + $CAHCHE_TTL < $NOWTIME) {
                unlink($file);
            }
        }
    }

    // オートローダーを登録
    spl_autoload_register(function ($class) {
        // NetDNS2の名前空間のクラスのみ処理
        if (strpos($class, 'NetDNS2\\') === 0) {
            // 名前空間をファイルパスに変換
            $file = dirname(__FILE__, 2) . '/libs/' . str_replace('\\', '/', $class) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        }
    });

    try {
        $resolver = new \NetDNS2\Resolver(['nameservers' => ['1.1.1.1', '8.8.8.8'], 'timeout' => 1]);
        $result = $resolver->query($ip, 'PTR');

        if (!empty($result->answer)) {
            // ホスト取得
            $host = $result->answer[0]->ptrdname->value();
            $host = rtrim($host, '.');
            // キャッシュに保存
            file_put_contents($cacheFile, $host, LOCK_EX);
            // 返却
            return $host;
        } else {
            // キャッシュに保存
            file_put_contents($cacheFile, $ip, LOCK_EX);
            return $ip;
        }
    } catch (\NetDNS2\Exception) {
        // キャッシュに保存
        file_put_contents($cacheFile, $ip, LOCK_EX);
        return $ip;
    }
}
