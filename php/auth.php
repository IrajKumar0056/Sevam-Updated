<?php
// Authentication and session helper functions
require_once __DIR__ . '/db.php';

function set_flash($key, $message, $type = 'success') {
    $_SESSION['flash'][$key] = [
        'msg' => $message,
        'type' => $type
    ];
}

function get_flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $data = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $data;
    }
    return null;
}

function render_flash($key) {
    $flash = get_flash($key);
    if ($flash) {
        $alertClass = 'alert-' . ($flash['type'] === 'error' ? 'danger' : $flash['type']);
        echo "<div class='alert {$alertClass}'><span>" . escape($flash['msg']) . "</span></div>";
    }
}
