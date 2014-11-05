<?php
require_once "mysql.inc.php";
class Comics
{
    public $id = '';
    public $name = '';
    public $vol_hash = '';
    public $total_hash = '';
    public $series_id = '';
    public $url = '';
    public $vol = '';
    public $total = '';
    public $created_at = '';
    public $counter = 1;
    public $urls = [];
    private $table = 'comics';
    private $db;

    private function replaceCs($hash, $start, $length, $is_str = false) {
        $str = substr($hash, $start, $length);
        return $is_str ? $str : preg_replace('/[a-z]*/', '', $str);
    }

    private function getHashPosition($page) {
        return (($page - 1) / 10) % 10 + ((($page - 1) % 10) * 3);
    }

    private function setVolHash() {
        $f = 50;
        for($i = 0; $i < strlen($this->total_hash) / $f; $i++){
            if($this->replaceCs($this->total_hash, $i * $f, 4) == $this->vol) {
                $this->vol_hash = $this->replaceCs($this->total_hash, $i * $f, $f, $f);
                break;
            }
        }
        if($this->vol_hash == ''){
            $this->vol_hash = $this->replaceCs($this->total_hash, strlen($this->total_hash) - $f, $f);
        }
    }

    public function __construct($total_hash, $url, $name) {
        $this->db = Mysql::getDb();
        $this->total_hash = $total_hash;
        $this->url = $url;
        $this->name = $name;
        if ($this->exist()) {
            $this->updateCounter();
        }
        $this->vol = max(explode('-', explode('=', $url)[1])[0], 1);
        if (preg_match('/http:\/\/m./', $url)) {
            $this->series_id = explode('.', explode('_', $url)[1])[0];
        } else {
            $this->series_id = explode('.', explode('-', $url)[1])[0];
        }
        $this->setVolHash();
        $this->total = $this->replaceCs($this->vol_hash, 7, 3);
        for ($i = 1; $i <= $this->total; $i++) {
            $img_hash = $this->replaceCs($this->vol_hash, $this->getHashPosition($i) + 10, 3, 50);
            if (strlen($img_hash) == 0) {
                $this->urls = [];
                return;
            }
            $this->urls[] = "http://img" . $this->replaceCs($this->vol_hash, 4, 2) . '.8comic.com/'
                . $this->replaceCs($this->vol_hash, 6, 1) . '/' . $this->series_id
                . '/' . $this->replaceCs($this->vol_hash, 0, 4) . '/'
                . str_pad($i, 3, "0", STR_PAD_LEFT) . '_'
                . $img_hash . '.jpg';
        }
        $this->created_at = time();
        if (!$this->exist()) {
            $this->save();
        }
    }

    public function exist () {
        $result = $this->db->query("select count(1) from comics where name='{$this->name}' and url='{$this->url}' and total_hash='{$this->total_hash}'")->fetch()[0];
        return (bool)$result;
    }

    private function updateCounter()
    {
        $this->db->query("update comics set counter = counter + 1 where name='{$this->name}' and url='{$this->url}' and total_hash='{$this->total_hash}'")->fetch()[0];
    }

    public static function search($pattern) {
        if (is_array($pattern)) {
            foreach ($pattern as $key => $value) {
                $query[] = "`$key`=" . $value;
            }
            $query_string = implode(' AND ', $query);
        }
    }

    public static function addRecord()
    {
        $dbm = $this->db->query("select count(1) as count from comics "
            . "where name={$this->name} and vol_hash={$this->vol_hash} "
            . "total_hash={$this->total_hash} and url={$this->url} "
            . "vol = {$this->vol}");
        $dbm->execute()->fetch();
        return $dbm['count'];
    }

    private function save() {
        $stmt = $this->db->prepare("insert into comics (name, vol_hash, total_hash, url, vol, created_at, total, series_id, counter, urls) values(:name, :vol_hash, :total_hash, :url, :vol, :created_at, :total, :series_id, :counter, :urls)");
        $stmt->bindParam(':name', $this->name, PDO::PARAM_STR, 255);
        $stmt->bindParam(':vol_hash', $this->vol_hash, PDO::PARAM_STR, 255);
        $stmt->bindParam(':total_hash', $this->total_hash, PDO::PARAM_STR, 255);
        $stmt->bindParam(':url', $this->url, PDO::PARAM_STR, 255);
        $stmt->bindParam(':vol', $this->vol, PDO::PARAM_INT);
        $stmt->bindParam(':created_at', $this->created_at, PDO::PARAM_INT);
        $stmt->bindParam(':total', $this->total, PDO::PARAM_INT);
        $stmt->bindParam(':series_id', $this->series_id, PDO::PARAM_INT);
        $stmt->bindParam(':counter', $this->counter, PDO::PARAM_INT);
        $stmt->bindParam(':urls', json_encode($this->urls), PDO::PARAM_STR, 255);
        $stmt->execute();
        $this->id = $stmt->errorInfo();
    }
}
