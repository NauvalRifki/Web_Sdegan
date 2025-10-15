<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna</title>
    <link rel="stylesheet" href="{{asset('css/user/index.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="navbar">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo">
                <p class="logo-text">S-DEGAN</p>
                <p class="logo-text">Sistem Deteksi Dini Gejolak Harga Pangan</p>
            </div>
            <button class="toggle-btn">☰</button>
            <div class="menu">
                <a href="index"><i class="fas fa-home"></i> Dashboard</a>
                <a href="SumberData"><i class="fas fa-book"></i> Regulasi</a>
                <a href="pengguna"><i class="fas fa-user"></i> User</a>
                <a href="data_pangan"><i class="fas fa-keyboard"></i> Data Pangan</a>
                <a href="rekap"><i class="fas fa-chart-line"></i> Rekap Data</a>
                <a href="javascript:void(0)" id="profile-link"><i class="fas fa-user-circle"></i> Profil</a>
            </div>
            <div class="datetime">
                <span id="current-time"></span>
                <hr>
                <span id="current-date"></span>
            </div>
        </div>

        <!-- Konten Utama -->
        <div class="container">
            
            <div class="header-text">
                <p>User Management</p>
            </div>

            <div class="main-content">
                <div class="table-container">
                    <div class="button-container">
                        <a href="{{ route('pengguna.create') }}" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Tambah Pengguna
                        </a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @foreach ($pengguna as $data)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->id }}</td>
                                <td>{{ $data->nama }}</td>
                                <td>{{ $data->email }}</td>
                                <td>{{ $data->role }}</td>
                                <td>
                                    <a href="{{ route('pengguna.edit', $data->id) }}" class="btn btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('pengguna.destroy', $data->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Apa anda yakin ingin menghapus pengguna ini?')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>                                
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Flash message alert -->
            @if(session('success'))
            <div id="alert-success" class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div id="alert-error" class="alert alert-danger">
                {{ session('error') }}
            </div>
            @endif
        </div>
    </div>

    <script>
        // Ambil elemen tombol toggle dan navbar
        const toggleBtn = document.querySelector('.toggle-btn');
        const navbar = document.querySelector('.navbar');
        const container = document.querySelector('.container');
        const layoutContainer = document.querySelector(".layout-container");

        // Fungsi untuk menyembunyikan atau menampilkan sidebar
        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('hidden');  // Toggle class untuk sidebar
            container.classList.toggle('sidebar-hidden'); // Menyesuaikan konten ketika sidebar disembunyikan
            toggleBtn.classList.toggle('hide'); // Menambahkan class untuk memutar panah
            container.classList.toggle("shifted"); // Menggeser container
            layoutContainer.classList.toggle("shifted");
        });
    </script>
    
    <!-- Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Profil User</h2>
            <div class="profile-details">
                <div class="profile-info">
                    <p>Nama: {{ Auth::user()->nama }}</p>
                    <p>Role: {{ Auth::user()->role }}</p>
                    <p>Email: {{ Auth::user()->email }}</p>
                    <a href="/logout" class="btn-logout">Logout</a>
                </div>
                <div class="profile-logo">
                    <img src="{{ asset('images/profileLogo.png') }}" alt="Profile Logo" />
                </div>
            </div>
        </div>
    </div>

        <script>
        // Ambil elemen modal
        var modal = document.getElementById("profileModal");

        // Ambil elemen yang membuka modal
        var btn = document.getElementById("profile-link");

        // Ambil elemen <span> yang menutup modal
        var span = document.getElementsByClassName("close")[0];

        // Ketika pengguna mengklik link profil, buka modal
        btn.onclick = function() {
        modal.style.display = "block"; // Tampilkan modal
        setTimeout(function() { 
            modal.classList.add("show");
            modal.classList.remove("hide"); // Pastikan untuk menghapus kelas 'hide' jika ada
        }, 10); // Waktu untuk memastikan display:block diterapkan
        }

        // Ketika pengguna mengklik <span> (x), tutup modal
        span.onclick = function() {
        modal.classList.remove("show"); // Hapus kelas 'show' untuk memulai animasi keluar
        modal.classList.add("hide"); // Tambahkan kelas 'hide' untuk memulai animasi penutupan
        setTimeout(function() { 
            modal.style.display = "none"; // Sembunyikan modal setelah animasi selesai
        }, 300); // Durasi yang sesuai dengan animasi CSS (0.3s)
        }

        // Ketika pengguna mengklik di luar modal, tutup modal
        window.onclick = function(event) {
        if (event.target == modal) {
            modal.classList.remove("show"); // Hapus kelas 'show' untuk memulai animasi keluar
            modal.classList.add("hide"); // Tambahkan kelas 'hide' untuk memulai animasi penutupan
            setTimeout(function() { 
                modal.style.display = "none"; // Sembunyikan modal setelah animasi selesai
            }, 300); // Durasi yang sesuai dengan animasi CSS (0.3s)
        }
        }
        
        document.addEventListener('DOMContentLoaded', (event) => {
            // Seleksi elemen alert
            const alertSuccess = document.getElementById('alert-success');
            const alertError = document.getElementById('alert-error');
            
            // Fungsi untuk menghilangkan elemen
            function hideAlert(alertElement) {
                if (alertElement) {
                    alertElement.style.transition = "opacity 0.5s ease-out";
                    alertElement.style.opacity = "0";
                    setTimeout(() => alertElement.remove(), 500); // Hapus elemen setelah transisi
                }
            }
            
            // Set waktu untuk menghilangkan alert
            setTimeout(() => hideAlert(alertSuccess), 2000); //2 detik
            setTimeout(() => hideAlert(alertError), 2000);
        });
        </script>
        <script>
            function updateDateTime() {
                const now = new Date();
                const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
            
                document.getElementById('current-date').innerText = now.toLocaleDateString('id-ID', optionsDate);
                document.getElementById('current-time').innerText = now.toLocaleTimeString('id-ID', optionsTime);
            }
            
            // Update setiap detik
            setInterval(updateDateTime, 1000);
            updateDateTime(); // Panggil sekali saat halaman dimuat
        </script>
</body>
</html>
