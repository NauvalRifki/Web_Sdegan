<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pangan</title>
    <link rel="stylesheet" href="{{ asset ('css/data_pangan.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body> 
    <div class="container">
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

        <h2 class="center-text">Data Pangan</h2>
        
        <div class="main-content">
            <div style="width: 60%; margin: 0 auto; display: flex; justify-content: center; gap: 20px;">
                <a href="{{ route('dashboard_admin.sub_menu.input_data') }}" class="btn-custom btn-success">
                    <i class="fa-solid fa-plus-circle"></i> Tambah Data Pangan
                </a>
                <a href="{{ route('dashboard_admin.sub_menu.verify_data') }}" class="btn-custom btn-danger">
                    <i class="fa-solid fa-check-circle"></i> Verifikasi Data
                </a>
            </div>          
        
        <form action="{{ route('dashboard_admin.sub_menu.data.bulkDelete') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data yang dipilih?')">
            @csrf
            <div class="table-container">
                <table id="entryDataTable" class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Nama Komoditas</th>
                            <th>Harga HET</th>
                            <th>Harga Kemarin</th>
                            <th>Harga Hari Ini</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dataKomoditas as $data)
                            <tr>
                                <td>
                                    <input type="checkbox" class="row-checkbox" name="ids[]" value="{{ $data->id }}">
                                </td>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $data->tanggal ?? '-' }}</td>
                                <td>{{ $data->nama_komoditas ?? '-' }}</td>
                                <td>Rp.{{ number_format($data->harga_het ?? 0, 0, ',', '.') }}</td>
                                <td>Rp.{{ number_format($data->harga_kemarin ?? 0, 0, ',', '.') }}</td>
                                <td>Rp.{{ number_format($data->harga_hari_ini ?? 0, 0, ',', '.') }}</td>
                                <td>
                                    @if($data->status_verifikasi == 'Valid')
                                        ✅ Valid
                                    @elseif($data->status_verifikasi == 'Tidak_valid')
                                        ❌ Tidak Valid Harga Salah
                                    @elseif($data->status_verifikasi == 'Harga_Tidak_Wajar')
                                        ⚠️ Tidak Valid: Harga Tidak Wajar
                                    @elseif($data->status_verifikasi == 'Data_Ganda')
                                        ⚠️ Tidak Valid: Data Ganda
                                    @else
                                        Belum Diverifikasi
                                    @endif
                                </td>                                                                                              
                                <td class="text-center">
                                    <a href="{{ route('dashboard_admin.sub_menu.data.edit', $data->id) }}" class="btn btn-edit btn-sm">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted" style="padding: 20px;">
                                    <em>Belum ada data pangan hari ini.</em>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>                    
                </table>
            </div>  
            <div class="bulk-action-container">
                <button type="submit" class="btn-bulk-delete">
                    <i class="fas fa-trash"></i> Hapus Data
                </button>
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
        </form>

        <!-- The Modalimport -->
        <div id="importDataModal" class="modal">
            <!-- Modal content -->
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Import Data</h2>
                <form action="importexcel" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file". required>
                    <button type="submit" class="btn-import">Upload</button>
                </form>
            </div>
        </div>

        <!-- Modal -->
        <div id="profileModal" class="modalp">
            <div class="modalp-content">
                <span class="closep">&times;</span>
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
    
    <script>
        // Fungsi untuk konfirmasi delete
        function confirmDelete(event) {
            event.preventDefault(); // Mencegah form submit secara otomatis
            const confirmed = confirm("Apakah Anda yakin ingin menghapus data ini?");
            if (confirmed) {
                event.target.submit(); // Submit form jika pengguna mengkonfirmasi
            }
        }
    </script>    
    <script>
        // Ambil elemen modal profil
        var profileModal = document.getElementById("profileModal");
    
        // Ambil elemen yang membuka modal profil
        var profileBtn = document.getElementById("profile-link");
    
        // Ambil elemen <span> yang menutup modal profil
        var profileSpan = document.getElementsByClassName("closep")[0];
    
        // Ketika pengguna mengklik link profil, buka modal
        profileBtn.onclick = function() {
            profileModal.style.display = "block"; // Tampilkan modal
            setTimeout(function() { 
                profileModal.classList.add("show");
                profileModal.classList.remove("hide"); // Pastikan untuk menghapus kelas 'hide' jika ada
            }, 10); // Waktu untuk memastikan display:block diterapkan
        }
    
        // Ketika pengguna mengklik <span> (x), tutup modal
        profileSpan.onclick = function() {
            profileModal.classList.remove("show"); // Hapus kelas 'show' untuk memulai animasi keluar
            profileModal.classList.add("hide"); // Tambahkan kelas 'hide' untuk memulai animasi penutupan
            setTimeout(function() { 
                profileModal.style.display = "none"; // Sembunyikan modal setelah animasi selesai
            }, 300); // Durasi yang sesuai dengan animasi CSS (0.3s)
        }
    
        // Ketika pengguna mengklik di luar modal, tutup modal
        window.onclick = function(event) {
            if (event.target == profileModal) {
                profileModal.classList.remove("show"); // Hapus kelas 'show' untuk memulai animasi keluar
                profileModal.classList.add("hide"); // Tambahkan kelas 'hide' untuk memulai animasi penutupan
                setTimeout(function() { 
                    profileModal.style.display = "none"; // Sembunyikan modal setelah animasi selesai
                }, 300); // Durasi yang sesuai dengan animasi CSS (0.3s)
            }
        }
    
        // Ambil elemen modal import
        var importDataModal = document.getElementById("importDataModal");
    
        // Ambil elemen yang membuka modal import
        var importDataBtn = document.getElementById("importDataBtn");
    
        // Ambil elemen <span> yang menutup modal import
        var importDataSpan = document.getElementsByClassName("close")[0];
    
        // Ketika pengguna mengklik tombol import, buka modal
        importDataBtn.onclick = function() {
            importDataModal.style.display = "block"; // Set display to block first
            setTimeout(function() {
                importDataModal.classList.add("show");
            }, 10); // Slight delay to ensure the transition runs
        }
    
        // Ketika pengguna mengklik <span> (x), tutup modal
        importDataSpan.onclick = function() {
            importDataModal.classList.remove("show");
            setTimeout(function() {
                importDataModal.style.display = "none";
            }, 300); // Delay to match the transition duration
        }
    
        // Ketika pengguna mengklik di luar modal, tutup modal
        window.onclick = function(event) {
            if (event.target == importDataModal) {
                importDataModal.classList.remove("show");
                setTimeout(function() {
                    importDataModal.style.display = "none";
                }, 300); // Delay to match the transition duration
            }
        }
    </script>
    <script>
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

    <script>
        // Update status dari localStorage ke kolom Status di Data Pangan
        document.querySelectorAll('tr').forEach(function(row, index) {
            let status = localStorage.getItem('status_' + index);
            let statusCell = row.querySelector('#status-' + index);

            if (status) {
                if (status === 'valid') {
                    statusCell.innerHTML = '✅ Valid';
                } else if (status === 'tidak_valid') {
                    statusCell.innerHTML = '❌ Tidak Valid';
                }
            }
        });
    </script>
    <script>
        function toggleSelectAll(source) {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = source.checked);
        }
    </script>

</body>
</html>
