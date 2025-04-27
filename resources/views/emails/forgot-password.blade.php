<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        .container {
            padding: 20px;
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Halo,</h2>
        <p>Berikut adalah kode verifikasi untuk reset password Anda:</p>
        <p class="code">{{ $verificationCode }}</p>
        <p>Masukkan kode ini di aplikasi untuk melanjutkan proses reset password.</p>
        <p>Terima kasih!</p>
    </div>
</body>
</html>
