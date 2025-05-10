<!DOCTYPE html>
<html>
<head>
    <title>Status Artikel</title>
</head>
<body>
    <h3>Halo {{ $artikel->user->nama_depan ?? 'Penulis' }},</h3>
    <p>Status artikel <strong>"{{ $artikel->title }}"</strong> telah diperbarui menjadi: 
    <strong>{{ ucfirst($artikel->verifikasi_admin) }}</strong>.</p>

    <p>Terima kasih telah mengirimkan artikel Anda.</p>
</body>
</html>
