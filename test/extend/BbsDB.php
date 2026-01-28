<?php

require_once dirname(__FILE__, 2) . '/utils/get-json-file.php';

class BbsDB
{
    private $db = null;
    private $dbDir;
    private $dbFile;
    private $postLogFile;
    private $errorLogFile;
    private $settingsFile;
    private $DEFAULT_TTL = 30 * 24 * 60 * 60; // 30日
    private $MAX_TTL = 365 * 24 * 60 * 60; // 365日

    public function __construct($bbsPath)
    {
        // 例外処理
        if (!is_dir($bbsPath)) {
            throw new Exception('板が存在しません。');
        }
        $bbs = basename($bbsPath);
        $excludeDir = [
            'admin',
            'api',
            'images',
            'static',
            'test',
        ];
        if (in_array($bbs, $excludeDir, true)) {
            throw new Exception('bbsが不正です。');
        }

        // DB用のフォルダ・ファイルを作成
        $dbDir = $bbsPath . '/db';
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0775, true)) {
                throw new Exception('dbディレクトリの作成に失敗しました');
            }
        }
        $htaccessFile = $dbDir . '/.htaccess';
        if (!is_file($htaccessFile)) {
            $htaccessContent = <<<EOM
            # Apache 2.4+
            <IfModule mod_authz_core.c>
                Require all denied
            </IfModule>

            # Apache 2.2
            <IfModule !mod_authz_core.c>
                Order deny,allow
                Deny from all
            </IfModule>
            EOM;
            if (file_put_contents($htaccessFile, $htaccessContent, LOCK_EX) === false) {
                throw new Exception('.htaccessの作成に失敗しました');
            }
        }
        $indexFile = $dbDir . '/index.php';
        if (!is_file($indexFile)) {
            if (file_put_contents($indexFile, '<?php http_response_code(403); exit;', LOCK_EX) === false) {
                throw new Exception('index.phpの作成に失敗しました');
            }
        }

        // 変数定義
        $this->dbDir = $dbDir;
        $this->dbFile = $dbDir . '/bbs.db';
        $this->postLogFile = $bbsPath . '/LOG.cgi';
        $this->errorLogFile = $bbsPath . '/errors.cgi';
        $this->settingsFile = $bbsPath . '/setting.json';
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

        // 投稿ログテーブル作成
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS post_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                mail TEXT NOT NULL,
                date_id TEXT NOT NULL,
                comment TEXT NOT NULL,
                title TEXT NOT NULL,
                thread TEXT NOT NULL,
                number INTEGER NOT NULL,
                host TEXT NOT NULL,
                ip TEXT NOT NULL,
                ua TEXT NOT NULL,
                ch_ua TEXT NOT NULL,
                accept TEXT NOT NULL,
                client_id TEXT NOT NULL,
                lv INTEGER NOT NULL,
                port TEXT NOT NULL,
                cf_ipcountry TEXT NOT NULL,
                hap_ip TEXT NOT NULL,
                hap_area TEXT NOT NULL,
                hap_slip TEXT NOT NULL,
                account_id TEXT,
                posted_at INTEGER NOT NULL
            )
        ');

        // インデックス作成
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_post_log_ip ON post_log(ip)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_post_log_client_id ON post_log(client_id)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_post_log_posted_at ON post_log(posted_at)');

        // エラーログテーブル作成
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS error_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                error TEXT NOT NULL,
                name TEXT NOT NULL,
                mail TEXT NOT NULL,
                date_id TEXT NOT NULL,
                comment TEXT NOT NULL,
                title TEXT NOT NULL,
                thread TEXT NOT NULL,
                number INTEGER NOT NULL,
                host TEXT NOT NULL,
                ip TEXT NOT NULL,
                ua TEXT NOT NULL,
                ch_ua TEXT NOT NULL,
                accept TEXT NOT NULL,
                client_id TEXT NOT NULL,
                lv INTEGER NOT NULL,
                port TEXT NOT NULL,
                cf_ipcountry TEXT NOT NULL,
                hap_ip TEXT NOT NULL,
                hap_area TEXT NOT NULL,
                hap_slip TEXT NOT NULL,
                account_id TEXT,
                posted_at INTEGER NOT NULL
            )
        ');

        // インデックス作成
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_error_log_ip ON error_log(ip)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_error_log_client_id ON error_log(client_id)');
        $this->db->exec('CREATE INDEX IF NOT EXISTS idx_error_log_posted_at ON error_log(posted_at)');
    }

    // --------------------------------------
    // 投稿ログ post_log
    // --------------------------------------

    /**
     * 投稿ログの新規データを追加するメソッド
     *
     * @param array{
     *   name: string,
     *   mail: string,
     *   date_id: string,
     *   comment: string,
     *   title: string,
     *   thread: string,
     *   number: int,
     *   host: string,
     *   ip: string,
     *   ua: string,
     *   ch_ua: string,
     *   accept: string,
     *   client_id: string,
     *   lv: int,
     *   port: string,
     *   cf_ipcountry: string,
     *   hap_ip: string,
     *   hap_area: string,
     *   hap_slip: string,
     *   account_id: string|null,
     *   posted_at: int
     * } $data 投稿データ
     * @return bool 成功判定
     */
    public function addToPostLog($data)
    {
        try {
            $db = $this->getDB();

            $stmt = $db->prepare('
                INSERT INTO post_log (
                    name, mail, date_id, comment, title,
                    thread, number, host, ip, ua,
                    ch_ua, accept, client_id, lv, port,
                    cf_ipcountry, hap_ip, hap_area, hap_slip, account_id,
                    posted_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?
                )
            ');

            $stmt->execute([
                $data['name'],
                $data['mail'],
                $data['date_id'],
                $data['comment'],
                $data['title'],
                $data['thread'],
                $data['number'],
                $data['host'],
                $data['ip'],
                $data['ua'],
                $data['ch_ua'],
                $data['accept'],
                $data['client_id'],
                $data['lv'],
                $data['port'],
                $data['cf_ipcountry'],
                $data['hap_ip'],
                $data['hap_area'],
                $data['hap_slip'],
                $data['account_id'] ?? null,
                $data['posted_at'],
            ]);

            // 古いデータを削除する処理
            if (mt_rand(1, 10000) === 1) { // 1/10000の確率で発動
                // 板設定取得
                if (is_file($this->settingsFile)) {
                    $settings = getJsonFile($this->settingsFile);
                    if ($settings === false) {
                        throw new ErrorException();
                    }
                } else {
                    $settings = [];
                }

                // TTL計算
                if (isset($settings['post_log_ttl'])) {
                    $dayTtl = (int) $settings['post_log_ttl'];
                    $ttl = min($dayTtl * 24 * 60 * 60, $this->MAX_TTL);
                } else {
                    $ttl = $this->DEFAULT_TTL;
                }
                $stmt = $db->prepare('DELETE FROM post_log WHERE posted_at < ?');
                $stmt->execute([$data['posted_at'] - $ttl]);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 投稿ログからデータを検索するメソッド
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
    public function searchFromPostLog($options = [])
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

            // date_id検索
            if (isset($options['date_id']) && $options['date_id'] !== '') {
                $where[] = 'date_id LIKE ?';
                $params[] = '%' . $options['date_id'] . '%';
            }

            // 本文検索
            if (isset($options['comment']) && $options['comment'] !== '') {
                $where[] = 'comment LIKE ?';
                $params[] = '%' . $options['comment'] . '%';
            }

            // WHILE句を作成
            $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            // 検索一致件数取得
            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM post_log
                {$whereSQL}
            ");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // データ取得（新しい順）
            $stmt = $db->prepare("
                SELECT *
                FROM post_log
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
     * 投稿ログをファイル形式からDBへ移行するメソッド
     *
     * @return array{alertType:string, message:string}
     */
    public function migratePostLog()
    {
        // SQLiteが使えるか確認
        if (extension_loaded('pdo_sqlite') === false) {
            return [
                'alertType' => 'danger',
                'message' => 'SQLiteが使えない環境です。',
            ];
        }

        // 進行状況を取得
        $stateFile = $this->dbDir . '/migratePostLog.state';
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
        if (!is_file($this->postLogFile)) {
            return [
                'alertType' => 'info',
                'message' => '移行処理は不要です。',
            ];
        }

        // 移行処理

        // ファイルを開く
        $CHUNK_SIZE = 1000;
        $logHandle = @fopen($this->postLogFile, 'r');
        if ($logHandle === false) {
            return [
                'alertType' => 'danger',
                'message' => '投稿ログファイルが開けませんでした。',
            ];
        }
        if (!flock($logHandle, LOCK_SH)) {
            fclose($logHandle);
            return [
                'alertType' => 'danger',
                'message' => '投稿ログファイルへのロック取得に失敗しました。',
            ];
        }

        // ポインタ移動
        if ($offset > 0) {
            fseek($logHandle, $offset, SEEK_SET);
        }

        // チャンク処理
        $processedLines = 0;
        $logs = [];
        $intIndex = [6, 13];
        while (!feof($logHandle) && $processedLines < $CHUNK_SIZE) {
            // 1行読み込む
            $line = fgets($logHandle);

            // 終端の場合
            if ($line === false) {
                break;
            }

            // 正しいデータなら配列に追加
            $data = preg_split('/<>/', trim($line), 19);
            if (count($data) === 19) {
                $log = [];
                foreach ($data as $index => $value) {
                    if (in_array($index, $intIndex, true)) {
                        $log[] = (int) $value;
                    } else {
                        $log[] = $value;
                    }
                }
                $dateId = $log[2];
                if (preg_match('/([0-9]{4}\/[0-9]{2}\/[0-9]{2}).*([0-9]{2}:[0-9]{2}:[0-9]{2})/', $dateId, $matches)) {
                    $unixTime = strtotime(str_replace('/', '-', $matches[1] . ' ' . $matches[2]));
                    if ($unixTime === false) {
                        $unixTime = 0;
                    }
                } else {
                    $unixTime = 0;
                }
                $log = array_merge($log, [null, $unixTime]);
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
                INSERT INTO post_log (
                    name, mail, date_id, comment, title,
                    thread, number, host, ip, ua,
                    ch_ua, accept, client_id, lv, port,
                    cf_ipcountry, hap_ip, hap_area, hap_slip, account_id,
                    posted_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?
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
        $subjectSize = filesize($this->postLogFile);
        if ($nextOffset >= $subjectSize) {
            $nextOffset = 'done';
            rename($this->postLogFile, $this->postLogFile . '.old');
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
            $percent = round(($nextOffset / $subjectSize) * 100, 2);
            return [
                'alertType' => 'success',
                'message' => "{$insertedCount}件移行。<br>進捗: {$percent}% 完了 ({$nextOffset} / {$subjectSize} バイト)",
            ];
        }
    }

    // --------------------------------------
    // エラーログ error_log
    // --------------------------------------

    /**
     * エラーログの新規データを追加するメソッド
     *
     * @param array{
     *   error: string,
     *   name: string,
     *   mail: string,
     *   date_id: string,
     *   comment: string,
     *   title: string,
     *   thread: string,
     *   number: int,
     *   host: string,
     *   ip: string,
     *   ua: string,
     *   ch_ua: string,
     *   accept: string,
     *   client_id: string,
     *   lv: int,
     *   port: string,
     *   cf_ipcountry: string,
     *   hap_ip: string,
     *   hap_area: string,
     *   hap_slip: string,
     *   account_id: string|null,
     *   posted_at: int
     * } $data 投稿データ
     * @return bool 成功判定
     */
    public function addToErrorLog($data)
    {
        try {
            $db = $this->getDB();

            $stmt = $db->prepare('
                INSERT INTO error_log (
                    error,
                    name, mail, date_id, comment, title,
                    thread, number, host, ip, ua,
                    ch_ua, accept, client_id, lv, port,
                    cf_ipcountry, hap_ip, hap_area, hap_slip, account_id,
                    posted_at
                ) VALUES (
                    ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?
                )
            ');

            $stmt->execute([
                $data['error'],
                $data['name'],
                $data['mail'],
                $data['date_id'],
                $data['comment'],
                $data['title'],
                $data['thread'],
                $data['number'],
                $data['host'],
                $data['ip'],
                $data['ua'],
                $data['ch_ua'],
                $data['accept'],
                $data['client_id'],
                $data['lv'],
                $data['port'],
                $data['cf_ipcountry'],
                $data['hap_ip'],
                $data['hap_area'],
                $data['hap_slip'],
                $data['account_id'],
                $data['posted_at'],
            ]);

            // 古いデータを削除する処理
            if (mt_rand(1, 1000) === 1) { // 1/1000の確率で発動
                // 板設定取得
                if (is_file($this->settingsFile)) {
                    $settings = getJsonFile($this->settingsFile);
                    if ($settings === false) {
                        throw new ErrorException();
                    }
                } else {
                    $settings = [];
                }

                // TTL計算
                if (isset($settings['error_log_ttl'])) {
                    $dayTtl = (int) $settings['error_log_ttl'];
                    $ttl = min($dayTtl * 24 * 60 * 60, $this->MAX_TTL);
                } else {
                    $ttl = $this->DEFAULT_TTL;
                }
                $stmt = $db->prepare('DELETE FROM error_log WHERE posted_at < ?');
                $stmt->execute([$data['posted_at'] - $ttl]);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * エラーログからデータを検索するメソッド
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
    public function searchFromErrorLog($options = [])
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
                FROM error_log
                {$whereSQL}
            ");
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // データ取得（新しい順）
            $stmt = $db->prepare("
                SELECT *
                FROM error_log
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
     * エラーログをファイル形式からDBへ移行するメソッド
     *
     * @return array{alertType:string, message:string}
     */
    public function migrateErrorLog()
    {
        // SQLiteが使えるか確認
        if (extension_loaded('pdo_sqlite') === false) {
            return [
                'alertType' => 'danger',
                'message' => 'SQLiteが使えない環境です。',
            ];
        }

        // 進行状況を取得
        $stateFile = $this->dbDir . '/migrateErrorLog.state';
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
        if (!is_file($this->errorLogFile)) {
            return [
                'alertType' => 'info',
                'message' => '移行処理は不要です。',
            ];
        }

        // 移行処理

        // ファイルを開く
        $CHUNK_SIZE = 1000;
        $logHandle = @fopen($this->errorLogFile, 'r');
        if ($logHandle === false) {
            return [
                'alertType' => 'danger',
                'message' => 'エラーログファイルが開けませんでした。',
            ];
        }
        if (!flock($logHandle, LOCK_SH)) {
            fclose($logHandle);
            return [
                'alertType' => 'danger',
                'message' => 'エラーログファイルへのロック取得に失敗しました。',
            ];
        }

        // ポインタ移動
        if ($offset > 0) {
            fseek($logHandle, $offset, SEEK_SET);
        }

        // チャンク処理
        $processedLines = 0;
        $logs = [];
        $intIndex = [7, 14];
        while (!feof($logHandle) && $processedLines < $CHUNK_SIZE) {
            // 1行読み込む
            $line = fgets($logHandle);

            // 終端の場合
            if ($line === false) {
                break;
            }

            // 正しいデータなら配列に追加
            $data = preg_split('/<>/', trim($line), 20);
            if (count($data) === 20) {
                $log = [];
                foreach ($data as $index => $value) {
                    if (in_array($index, $intIndex, true)) {
                        $log[] = (int) $value;
                    } else {
                        $log[] = $value;
                    }
                }
                $dateId = $log[3];
                if (preg_match('/([0-9]{4}\/[0-9]{2}\/[0-9]{2}).*([0-9]{2}:[0-9]{2}:[0-9]{2})/', $dateId, $matches)) {
                    $unixTime = strtotime(str_replace('/', '-', $matches[1] . ' ' . $matches[2])) ?? 0;
                } else {
                    $unixTime = 0;
                }
                $log = array_merge($log, [null, $unixTime]);
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
                INSERT INTO error_log (
                    error,
                    name, mail, date_id, comment, title,
                    thread, number, host, ip, ua,
                    ch_ua, accept, client_id, lv, port,
                    cf_ipcountry, hap_ip, hap_area, hap_slip, account_id,
                    posted_at
                ) VALUES (
                    ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?
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
        $subjectSize = filesize($this->errorLogFile);
        if ($nextOffset >= $subjectSize) {
            $nextOffset = 'done';
            rename($this->errorLogFile, $this->errorLogFile . '.old');
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
            $percent = round(($nextOffset / $subjectSize) * 100, 2);
            return [
                'alertType' => 'success',
                'message' => "{$insertedCount}件移行。<br>進捗: {$percent}% 完了 ({$nextOffset} / {$subjectSize} バイト)",
            ];
        }
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
