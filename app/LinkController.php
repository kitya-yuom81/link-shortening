<?php
namespace App;

final class LinkController {
    private LinkModel $model;

    public function __construct() {
        $this->model = new LinkModel();
    }

    public function home(): void {
        render('home', [
            'links' => $this->model->all(),
            'csrf'  => csrf_token(),
            'flash' => flashes(),
            'base'  => self::baseUrl(),
        ]);
    }

    // POST /create
    public function create(): void {
        if (!csrf_ok()) {
            redirect('/');
        }

        $url  = trim($_POST['url'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $code = ($code === '') ? null : $code;

        // Empty URL
        if ($url === '') {
            flash('error', 'URL is required.');
            redirect('/');
        }

        // Add https:// if missing
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            flash('error', 'Invalid URL.');
            redirect('/');
        }

        // Validate custom code
        if ($code !== null && !preg_match('/^[a-zA-Z0-9\-_]{3,20}$/', $code)) {
            flash('error', 'Custom code must be 3–20 chars (A–Z, a–z, 0–9, -, _).');
            redirect('/');
        }

        try {
            $final = $this->model->create($url, $code ?: null);
            flash('success', 'Short link created: ' . self::baseUrl() . '/r/' . $final);
        } catch (\PDOException $e) {
            // likely unique constraint (code already taken)
            flash('error', 'That code is already taken. Try another.');
        }

        redirect('/');
    }

    // POST /delete
    public function delete(): void {
        if (!csrf_ok()) {
            redirect('/');
        }

        $id = $_POST['id'] ?? '';
        if ($id !== '') {
            $this->model->delete($id);
            flash('info', 'Deleted.');
        }
        redirect('/');
    }

    public function go(array $params): void {
        $code = $params['code'] ?? '';
        $row  = $this->model->findByCode($code);

        if (!$row) {
            http_response_code(404);
            echo 'Unknown code';
            return;
        }

        $this->model->incrementClicks($row['id']);
        header('Location: ' . $row['url'], true, 302);
        exit;
    }

    private static function baseUrl(): string {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8082';
        return $scheme . '://' . $host;
    }
}
