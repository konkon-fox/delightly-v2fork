<?php

require_once dirname(__FILE__, 2) . '/utils/get-json-file.php';

class SystemDB
{
    private $db = null;
    private $dbDir;
    private $dbFile;
    private $authLogFile;
    private $settingsFile;
    private $AUTH_DEFAULT_TTL = 90 * 24 * 60 * 60; // 90日
    private $KEY_ERROR_DEFAULT_TTL = 90 * 24 * 60 * 60; // 90日
    private $KEY_ERROR_DEFAULT_HOUR = 24;
    private $KEY_ERROR_DEFAULT_CHANCE = 10;

    public function __construct()
    {
        // DB用のフォルダ・ファイルを作成
        $dbDir = dirname(__FILE__, 2) . '/operate/db';
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0775, true)) {
                throw new Exception('dbディレクトリの作成に失敗しました');
            }
        }
        // 変数定義
        $this->dbDir = $dbDir;
        $this->dbFile = $dbDir . '/system.db';
        $this->authLogFile = dirname(__FILE__, 2) . '/HAP/log.cgi';
        $this->settingsFile = dirname(__FILE__, 2) . '/operate/system-settings.json';
    }

    private function getDB()
    {
        if ($this->db !== null) {
            return $this->db;
        }

        // SQLiteが使えるかチェック
        if (!extension_loaded('pdo_sqlite')) {
            throw new Exception('SQLiteが利用できません');
        }

        $isNewDB = !is_file($this->dbFile);

        $this->db = new PDO('sqlite:' . $this->dbFile);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 新規DBならテーブル作成
        if ($isNewDB) {
            $this->createTable();
        }

        return $this->db;
    }

    /**
     * 新規テーブルを作成するメソッド
     *
     * @return void
     */
    private function createTable()
    {
        // auto_vacuum を有効化
        $this->db->exec('PRAGMA auto_vacuum = FULL');

        // 認証ログテーブル作成
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS auth_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                date TEXT NOT NULL,
                status TEXT NOT NULL,
                wrt_agreement_key TEXT NOT NULL,
                ip TEXT NOT NULL,
                host TEXT NOT NULL,
                isp TEXT NOT NULL,
                asname TEXT NOT NULL,
                ua TEXT NOT NULL,
                ch_ua TEXT NOT NULL,
                client_id TEXT NOT NULL,
                en_file TEXT NOT NULL,
                account_id TEXT NOT NULL,
                posted_at INTEGER NOT NULL
            )
        ');

        //     // インデックス作成
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_auth_log_ip ON auth_log(ip)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_auth_log_posted_at ON auth_log(posted_at)');

        // 同意鍵エラーログテーブル作成
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS key_error_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                date TEXT NOT NULL,
                key TEXT NOT NULL,
                ip TEXT NOT NULL,
                host TEXT NOT NULL,
                ua TEXT NOT NULL,
                message TEXT NOT NULL,
                posted_at INTEGER NOT NULL
            )
        ');

        // インデックス作成
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_key_error_log_ip ON key_error_log(ip)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_key_error_log_posted_at ON key_error_log(posted_at)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_key_error_log_ip_posted_at ON key_error_log(ip, posted_at)');
    }

    // --------------------------------------
    // 認証ログ auth_log
    // --------------------------------------
    /**
     * 投稿ログの新規データを追加するメソッド
     *
     * @param array{
     *   date: string,
     *   status: string,
     *   wrt_agreement_key: string,
     *   ip: string,
     *   host: string,
     *   isp: string,
     *   asname: string,
     *   ua: string,
     *   ch_ua: string,
     *   client_id: string,
     *   en_file: string,
     *   account_id: string,
     *   posted_at: int,
     * } $data 認証データ
     * @return bool 成功判定
     */
    public function addToAuthLog($data)
    {
        try {
            $db = $this->getDB();

            $stmt = $db->prepare('
                INSERT INTO auth_log (
                    date, status, wrt_agreement_key, ip, host,
                    isp, asname, ua, ch_ua, client_id,
                    en_file, account_id, posted_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?
                )
            ');

            $stmt->execute([
                $data['date'],
                $data['status'],
                $data['wrt_agreement_key'],
                $data['ip'],
                $data['host'],
                $data['isp'],
                $data['asname'],
                $data['ua'],
                $data['ch_ua'],
                $data['client_id'],
                $data['en_file'],
                $data['account_id'],
                $data['posted_at'],
            ]);

            // 古いデータを削除する処理
            if (mt_rand(1, 100) === 1) { // 1/100の確率で発動
                // 板設定取得
                $settings = $this->getSystemSettings();
                if ($settings === false) {
                    throw new Exception('システム設定ファイルの取得に失敗しました。');
                }

                // TTL計算
                if (isset($settings['auth_log_ttl'])) {
                    $dayTtl = (int) $settings['auth_log_ttl'];
                    $ttl = $dayTtl * 24 * 60 * 60;
                } else {
                    $ttl = $this->AUTH_DEFAULT_TTL;
                }
                $stmt = $db->prepare('DELETE FROM auth_log WHERE posted_at < ?');
                $stmt->execute([$data['posted_at'] - $ttl]);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 認証ログからデータを検索するメソッド
     *
     * @param array{
     *   page?: int,
     *   per_page?: int,
     *   client_id?: string,
     *   ip?: string
     * } $options 検索オプション
     *
     * @return array{
     *   logs: array<int, array>,
     *   total_count: int
     * }|false 成功すれば結果を出力、失敗時はfalse
     */
    public function searchFromAuthLog($options = [])
    {
        try {
            $db = $this->getDB();

            // デフォルト値
            $page = isset($options['page']) ? max(1, (int) $options['page']) : 1;
            $perPage = isset($options['per_page']) ? (int) $options['per_page'] : 500;
            $offset = ($page - 1) * $perPage;

            // WHERE句とパラメータを構築
            $where = [];
            $params = [];

            // key検索
            if (isset($options['key']) && $options['key'] !== '') {
                $where[] = 'wrt_agreement_key LIKE ?';
                $params[] = '%' . $options['key'] . '%';
            }

            // client_id検索
            if (isset($options['client_id']) && $options['client_id'] !== '') {
                $where[] = 'client_id LIKE ?';
                $params[] = '%' . $options['client_id'] . '%';
            }

            // ip検索
            if (isset($options['ip']) && $options['ip'] !== '') {
                $where[] = 'ip LIKE ?';
                $params[] = '%' . $options['ip'] . '%';
            }

            // WHILE句を作成
            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // 検索一致件数取得
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM auth_log
                {$whereSQL}
            ");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // データ取得（新しい順）
            $stmt = $db->prepare("
                SELECT *
                FROM auth_log
                {$whereSQL}
                ORDER BY posted_at DESC, id DESC
                LIMIT ?
                OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$perPage, $offset]));
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'logs' => $logs,
                'total_count' => $total,
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 認証ログをファイル形式からDBへ移行するメソッド
     *
     * @return array{alertType:string, message:string}
     */
    public function migrateAuthLog()
    {
        // SQLiteが使えるか確認
        if (extension_loaded('pdo_sqlite') === false) {
            return [
                'alertType' => 'danger',
                'message' => 'SQLiteが使えない環境です。',
            ];
        }

        // 進行状況を取得
        $stateFile = $this->dbDir . '/migrateAuthLog.state';
        if (is_file($stateFile)) {
            $state = safe_file_get_contents($stateFile);
            if ($state === false) {
                return [
                    'alertType' => 'danger',
                    'message' => '進行状況の取得に失敗しました。',
                ];
            }
            if ($state === 'done') {
                return [
                    'alertType' => 'info',
                    'message' => '既に移行完了済みです。',
                ];
            }
            $offset = (int) $state;
        } else {
            $offset = 0;
        }

        // 過去ログファイルが無い場合
        if (!is_file($this->authLogFile)) {
            return [
                'alertType' => 'info',
                'message' => '移行処理は不要です。',
            ];
        }

        // 移行処理

        // ファイルを開く
        $CHUNK_SIZE = 1000;
        $logHandle = @fopen($this->authLogFile, 'r');
        if ($logHandle === false) {
            return [
                'alertType' => 'danger',
                'message' => '認証ログファイルが開けませんでした。',
            ];
        }
        if (!flock($logHandle, LOCK_SH)) {
            fclose($logHandle);
            return [
                'alertType' => 'danger',
                'message' => '認証ログファイルへのロック取得に失敗しました。',
            ];
        }

        // ポインタ移動
        if ($offset > 0) {
            fseek($logHandle, $offset, SEEK_SET);
        }

        // チャンク処理
        $processedLines = 0;
        $logs = [];
        while (!feof($logHandle) && $processedLines < $CHUNK_SIZE) {
            // 1行読み込む
            $line = fgets($logHandle);

            // 終端の場合
            if ($line === false) {
                break;
            }

            // 正しいデータなら配列に追加
            $data = preg_split('/<>/', trim($line), 11);
            if (count($data) === 11) {
                $log = $data;
                $unixTime = strtotime($log[0]);
                if ($unixTime === false) {
                    $unixTime = 0;
                }
                $wrtAgreementKey = $log[2];
                $accountId = $wrtAgreementKey === 'null' ? 'null' : hash('sha256', hash('sha256', md5($wrtAgreementKey) . preg_replace('/[^0-9]/', '', md5($wrtAgreementKey))));
                $log = array_merge($log, [$accountId, $unixTime]);
                $logs[] = $log;
            }

            // 次の行へ
            $processedLines++;
        }

        // ポインタ位置を進行状態ファイルへ記録
        $nextOffset = ftell($logHandle);
        // ファイル閉じる
        flock($logHandle, LOCK_UN);
        fclose($logHandle);

        // 取得したデータをDBへ追加
        try {
            $db = $this->getDB();
            $db->beginTransaction();

            $stmt = $db->prepare('
                INSERT INTO auth_log (
                    date, status, wrt_agreement_key, ip, host,
                    isp, asname, ua, ch_ua, client_id,
                    en_file, account_id, posted_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?
                )
            ');

            $insertedCount = 0;
            foreach ($logs as $log) {
                $stmt->execute($log);
                $insertedCount++;
            }
            $db->commit();
        } catch (Exception $e) {
            // エラー時はロールバック
            if (isset($db)) {
                $db->rollBack();
            }
            return [
                'alertType' => 'danger',
                'message' => '移行中にエラーが発生しました: ' . $e->getMessage(),
            ];
        }

        // ポインタ位置がファイルサイズ以上なら完了
        $oldLogSize = filesize($this->authLogFile);
        if ($nextOffset >= $oldLogSize) {
            $nextOffset = 'done';
            rename($this->authLogFile, $this->authLogFile . '.old');
        }
        // 進行状況を記録
        if ($processedLines > 0) {
            file_put_contents($stateFile, $nextOffset, LOCK_EX);
        }
        // 完了メッセージ
        if ($nextOffset === 'done') {
            return [
                'alertType' => 'success',
                'message' => "{$insertedCount}件移行。<br>DBへの移行が完了しました。",
            ];
        } else {
            $percent = round(($nextOffset / $oldLogSize) * 100, 2);
            return [
                'alertType' => 'success',
                'message' => "{$insertedCount}件移行。<br>進捗: {$percent}% 完了 ({$nextOffset} / {$oldLogSize} バイト)",
            ];
        }
    }
    // --------------------------------------
    // 同意鍵エラーログ key_error_log
    // --------------------------------------

    /**
     * 同意鍵エラーログの新規データを追加するメソッド
     *
     * @param array{
     *   date: string,
     *   key: string,
     *   ip: string,
     *   host: string,
     *   ua: string,
     *   message: string,
     *   posted_at: int,
     * } $data 投稿時のデータ
     * @return bool 成功判定
     */
    public function addToKeyErrorLog($data)
    {
        try {
            $db = $this->getDB();

            $stmt = $db->prepare('
                INSERT INTO key_error_log (
                    date, key, ip, host, ua,
                    message, posted_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?
                )
            ');

            $stmt->execute([
                $data['date'],
                $data['key'],
                $data['ip'],
                $data['host'],
                $data['ua'],
                $data['message'],
                $data['posted_at'],
            ]);

            // 古いデータを削除する処理
            if (mt_rand(1, 1000) === 1) { // 1/1000の確率で発動
                // 板設定取得
                $settings = $this->getSystemSettings();
                if ($settings === false) {
                    throw new Exception('システム設定ファイルの取得に失敗しました。');
                }

                // TTL計算
                if (isset($settings['key_error_log_ttl'])) {
                    $dayTtl = (int) $settings['key_error_log_ttl'];
                    $ttl = $dayTtl * 24 * 60 * 60;
                } else {
                    $ttl = $this->KEY_ERROR_DEFAULT_TTL;
                }
                $stmt = $db->prepare('DELETE FROM key_error_log WHERE posted_at < ?');
                $stmt->execute([$data['posted_at'] - $ttl]);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 同意鍵エラーログからデータを検索するメソッド
     *
     * @param array{
     *   page?: int,
     *   per_page?: int,
     *   key?: string,
     *   ip?: string
     * } $options 検索オプション
     *
     * @return array{
     *   logs: array<int, array>,
     *   total_count: int
     * }|false 成功すれば結果を出力、失敗時はfalse
     */
    public function searchFromKeyErrorLog($options = [])
    {
        try {
            $db = $this->getDB();

            // デフォルト値
            $page = isset($options['page']) ? max(1, (int) $options['page']) : 1;
            $perPage = isset($options['per_page']) ? (int) $options['per_page'] : 500;
            $offset = ($page - 1) * $perPage;

            // WHERE句とパラメータを構築
            $where = [];
            $params = [];

            // key検索
            if (isset($options['key']) && $options['key'] !== '') {
                $where[] = 'key LIKE ?';
                $params[] = '%' . $options['key'] . '%';
            }

            // ip検索
            if (isset($options['ip']) && $options['ip'] !== '') {
                $where[] = 'ip LIKE ?';
                $params[] = '%' . $options['ip'] . '%';
            }

            // WHILE句を作成
            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // 検索一致件数取得
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM key_error_log
                {$whereSQL}
            ");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // データ取得（新しい順）
            $stmt = $db->prepare("
                SELECT *
                FROM key_error_log
                {$whereSQL}
                ORDER BY posted_at DESC, id DESC
                LIMIT ?
                OFFSET ?
            ");
            $stmt->execute(array_merge($params, [$perPage, $offset]));
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'logs' => $logs,
                'total_count' => $total,
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 同意鍵を検証するメソッド
     *
     * @param array{
     *   date: string,
     *   ip: string,
     *   host: string,
     *   ua: string,
     *   posted_at: int,
     *   mail: string,
     *   wrt_agreement_key: string|null,
     *   wrt_agreement_key_sig: string|null
     * } $data 投稿時のデータ
     * @return array{account_id:string, hap_file:string, signature: string}|false 成功したら連想配列、失敗したらfalse
     */
    public function checkWrtAgreementKey($data)
    {
        // 同意鍵取得
        if (!isset($data['wrt_agreement_key'])) {
            $data['wrt_agreement_key'] = str_replace('#', '', $data['mail']);
        }
        if ($data['wrt_agreement_key'] === '') {
            $data['message'] = '同意鍵が空欄';
            $this->addToKeyErrorLog($data);
            return false;
        }

        // 板設定取得
        $settings = $this->getSystemSettings();
        if ($settings === false) {
            $data['ip'] = 'null';
            $data['message'] = 'システム設定ファイルの取得に失敗';
            $this->addToKeyErrorLog($data);
            return false;
        }

        // 署名作成
        $signature = hash_hmac('sha256', $data['wrt_agreement_key'], $settings['system_salt']);

        // 署名未検証なら失敗回数フィルタを通す
        if (!isset($data['wrt_agreement_key_sig']) || $data['wrt_agreement_key_sig'] !== $signature) {
            // 定数取得
            $errorHour = $settings['KEY_ERROR_DEFAULT_HOUR'] ?? $this->KEY_ERROR_DEFAULT_HOUR;
            $errorChance = $settings['KEY_ERROR_DEFAULT_CHANCE'] ?? $this->KEY_ERROR_DEFAULT_CHANCE;

            // IPアドレスで失敗回数チェック
            try {
                $db = $this->getDB();
                $stmt = $db->prepare('SELECT COUNT(*) FROM key_error_log WHERE ip = ? AND posted_at > ?');
                $ttl = $data['posted_at'] - $errorHour * 60 * 60;
                $stmt->execute([$data['ip'], $ttl]);
                $failedCount = $stmt->fetchColumn();
            } catch (Exception $e) {
                $data['ip'] = 'null';
                $data['message'] = 'DBへのアクセスに失敗: ' . $e;
                $this->addToKeyErrorLog($data);
                return false;
            }

            // 失敗回数が規定以上なら拒否
            if ($failedCount >= $errorChance) {
                $data['message'] = '同意鍵エラーが規定回数以上';
                $this->addToKeyErrorLog($data);
                return false;
            }
        }

        // HAPファイル存在確認
        $accountId = hash('sha256', hash('sha256', md5($data['wrt_agreement_key']) . preg_replace('/[^0-9]/', '', md5($data['wrt_agreement_key']))));
        $hapFile = dirname(__FILE__, 2) . '/HAP/' . $accountId . '.cgi';
        if (!is_file($hapFile)) {
            $data['message'] = 'HAPファイルが存在しない';
            $this->addToKeyErrorLog($data);
            return false;
        }

        // 結果返却
        return [
            'account_id' => $accountId,
            'hap_file' => $hapFile,
            'signature' => $signature,
        ];
    }

    /**
     * システム設定を取得する関数
     *
     * @return array|false 成功したら設定、失敗したらfalse
     */
    private function getSystemSettings()
    {
        // 板設定取得
        if (is_file($this->settingsFile)) {
            $settings = getJsonFile($this->settingsFile);
            if ($settings === false) {
                return false;
            }
        } else {
            $settings = [];
        }

        // ソルトが無ければ生成して保存
        if (!isset($settings['system_salt'])) {
            $settings['system_salt'] = bin2hex(random_bytes(32));
            file_put_contents($this->settingsFile, json_encode($settings, JSON_UNESCAPED_UNICODE), LOCK_EX);
        }

        // 連想配列を返却
        return $settings;
    }

    /**
     * SQLiteを使うかどうか判定するメソッド
     *
     * @return bool
     */
    public function isSQLiteMode()
    {
        // DBファイルが存在すればSQLiteモード
        if (is_file($this->dbFile)) {
            return true;
        }
        // どちらもなければ、SQLiteが使えるならSQLiteモード
        return extension_loaded('pdo_sqlite');
    }
}
