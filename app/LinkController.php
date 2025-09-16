<?php
namespace App;

final class LinkController {
    private LinkModel $model;
    public function __construct() { $this->model = new LinkModel(); }

    public function home(): void {
        render('home', [
            'links' => $this->model->all(),
            'csrf'  => csrf_token(),
            'flash' => flashes(),
            'base'  => self::baseUrl(),
        ]);
    }

    public function create(): void {
        if (!csrf_ok()) redirect('/');
        $url  = trim($_POST['url'] ?? '');
        $code = trim($_POST['code'] ?? '') ?: null;

        if ($url === '') { flash('error','URL is required.'); return redirect('/'); }
        if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
        if (!filter_var($url, FILTER_VALIDATE_URL)) { flash('error','Invalid URL.'); return redirect('/'); }
        if ($code !== null && !preg_match('/^[a-zA-Z0-9\-_]{3,20}$/', $code)) {
            flash('error','Custom code must be 3–20 chars (A–Z, a–z, 0–9, - , _).'); return redirect('/');
        }

        try {
            $final = $this->model->create($url, $code ?: null);
            flash('success','Short link created: ' . self::baseUrl() . '/r/' . $final);
        } catch (\PDOException $e) {
            flash('error','That code is already taken. Try another.');
        }
        redirect('/');
    }

    public function delete(): void {
        if (!csrf_ok()) redirect('/');
        $id = $_POST['id'] ?? '';
        if ($id) $this->model->delete($id);
        flash('info','Deleted.');
        redirect('/');
    }

    public function go(array $params): void {
        $code = $params['code'] ?? '';
        $row = $this->model->findByCode($code);
        if (!$row) { http_response_code(404); echo "Unknown code"; return; }
        $this->model->incrementClicks($row['id']);
        header('Location: ' . $row['url'], true, 302);
        exit;
    }

    private static function baseUrl(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8082';
        return $scheme . '://' . $host;
    }
}
