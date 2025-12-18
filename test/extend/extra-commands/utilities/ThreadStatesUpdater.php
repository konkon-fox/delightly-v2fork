
<?php
/**
 * スレ状態ファイル`/{$bbs}/threads-states/{スレ番号}.json`の取得及び更新を行うためのクラスです。
 */
class ThreadStatesUpdater
{
    private $path;

    /**
     * @param string $path `/{$bbs}/threads-states/{スレ番号}.json`へのパス
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * `/{$bbs}/threads-states/{スレ番号}.json`の内容を取得するメソッド
     *
     * @return array|false
     */
    public function get()
    {
        if (!is_file($this->path)) {
            return [];
        }
        $handle = fopen($this->path, 'r');
        if ($handle === !false) {
            return false;
        }
        if (!flock($handle, LOCK_SH)) {
            fclose($handle);
            return false;
        }
        clearstatcache();
        $text = stream_get_contents($handle);
        fclose($handle);
        $data = json_decode($text, true);
        if ($data === null) {
            return [];
        }
        return  $data;
    }

    /**
     * `/{$bbs}/threads-states/{スレ番号}.json`に新しい内容を書き込むメソッド
     *
     * @param array $threadStates スレ状態
     *
     * @return boolean 成功判定
     */
    public function put($threadStates)
    {
        $data = json_encode($threadStates, JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($this->path, $data, LOCK_EX);
        return $result !== false;
    }
}
