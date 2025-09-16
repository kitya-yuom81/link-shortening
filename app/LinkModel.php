<?php
namespace App;
use PDO;

final class LinkModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    /** @return array<int,array{id:string,code:string,url:string,clicks:int,created_at:string}> */
    public function all(): array {
        return $this->db->query("SELECT * FROM links ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(string $url, ?string $customCode = null): string {
        $code = $customCode ?: $this->newCode();
        $stmt = $this->db->prepare("INSERT INTO links(id,code,url) VALUES(:id,:code,:url)");
        $stmt->execute([
            ':id' => bin2hex(random_bytes(6)),
            ':code' => $code,
            ':url' => $url,
        ]);
        return $code;
    }

    public function findByCode(string $code): ?array {
        $st = $this->db->prepare("SELECT * FROM links WHERE code = :c LIMIT 1");
        $st->execute([':c'=>$code]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function incrementClicks(string $id): void {
        $this->db->prepare("UPDATE links SET clicks = clicks + 1 WHERE id = :id")->execute([':id'=>$id]);
    }

    public function delete(string $id): void {
        $this->db->prepare("DELETE FROM links WHERE id = :id")->execute([':id'=>$id]);
    }

    private function newCode(): string {
        do {
            $code = rtrim(strtr(base64_encode(random_bytes(4)),'+/','-_'), '=');
            $exists = $this->findByCode($code);
        } while ($exists);
        return $code;
    }
}
