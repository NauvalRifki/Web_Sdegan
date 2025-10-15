<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="left-side">
        <img src="{{ asset('images/login.png') }}" alt="Login"> <!-- Gambar logo -->
        <p class="logo-text">Sistem Deteksi Dini Gejolak Harga Pangan</p>
    </div>
    <div class="right-side">
        <div class="login-container">
            <h2>LOGIN</h2>
            <form action="{{ url('/login') }}" method="post">
                @csrf
                <div class="input-group">
                    <label for="id">ID</label>
                    <input type="text" id="id" name="id" placeholder="Enter your ID" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="input-group">
                    <input type="submit" value="Login">
                </div>
                @if (session('error'))
                    <p>{{ session('error') }}</p>
                @endif
            </form>
            <div class="additional-links">
                <p>Anda Pengguna Umum?? Klik Tombol ini untuk Mengunjungi Dashboard</p>
                <button class="dashboard-btn" onclick="goToDashboard()">Dashboard</button>
            </div>
        </div>
    </div>

    <script>
        function goToDashboard() {
            window.location.href = '{{ url('dashboard_umum') }}';
        }
    </script>
</body>
</html>
