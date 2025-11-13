<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Data</title>
    <link rel="stylesheet" href="{{ asset('css/verify_data.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <a href="verify_data"><i class="fas fa-check-circle"></i> Verifikasi</a>
                <a href="rekap"><i class="fas fa-chart-line"></i> Rekap Data</a>
                <a href="javascript:void(0)" id="profile-link"><i class="fas fa-user-circle"></i> Profil</a>
            </div>
            <div class="datetime-op">
                <span id="current-time"></span>
                <hr>
                <span id="current-date"></span>
            </div>
        </div>  

        <div class="main-content">
            <h2 class="center-text">Verifikasi Data</h2>
            <div class="table-container">
                <div class="filter-container">
                    <label for="bulkStatus">Verifikasi Instan: </label>
                    <select id="bulkStatus" onchange="setAllStatus(this.value)">
                        <option value="">-- Pilih Semua --</option>
                        <option value="Valid">✅ Valid</option>
                        <option value="Tidak_valid">❌ Tidak Valid: Harga Salah</option>
                        <option value="Harga_Tidak_Wajar">⚠️ Tidak Valid: Harga Tidak Wajar</option>
                        <option value="Data_Ganda">⚠️ Tidak Valid: Data Ganda</option>
                    </select>
                </div>
                <form id="saveForm" action="{{ route('dashboard_verifikator.save_data') }}" method="POST">
                    @csrf
                    <div class="table-wrapper">
                        <table id="verifyDataTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Komoditas</th>
                                    <th>Harga HET</th>
                                    <th>Harga Kemarin</th>
                                    <th>Harga Hari Ini</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataKomoditas as $index => $data)
                                    <tr>
                                        <td>
                                            {{ $data->tanggal }}
                                            <input type="hidden" name="data[{{ $index }}][tanggal]" value="{{ $data->tanggal }}">
                                        </td>
                                        <td>
                                            {{ $data->nama_komoditas }}
                                            <input type="hidden" name="data[{{ $index }}][nama_komoditas]" value="{{ $data->nama_komoditas }}">
                                        </td>
                                        <td>
                                            Rp.{{ number_format($data->harga_het, 0, ',', '.') }}
                                            <input type="hidden" name="data[{{ $index }}][harga_het]" value="{{ $data->harga_het }}">
                                        </td>
                                        <td>
                                            Rp.{{ number_format($data->harga_kemarin, 0, ',', '.') }}
                                            <input type="hidden" name="data[{{ $index }}][harga_kemarin]" value="{{ $data->harga_kemarin }}">
                                        </td>
                                        <td>
                                            Rp.{{ number_format($data->harga_hari_ini, 0, ',', '.') }}
                                            <input type="hidden" name="data[{{ $index }}][harga_hari_ini]" value="{{ $data->harga_hari_ini }}" required>
                                        </td>
                                        <td>
                                            <select name="data[{{ $index }}][status_verifikasi]" required>
                                                <option value="">Pilih Status</option>
                                                <option value="Valid" {{ old('data.' . $index . '.status_verifikasi') == 'Valid' ? 'selected' : '' }}>✅ Valid</option>
                                                <option value="Tidak_valid" {{ old('data.' . $index . '.status_verifikasi') == 'Tidak_valid' ? 'selected' : '' }}>❌ Tidak Valid: Harga Salah</option>
                                                <option value="Harga_Tidak_Wajar" {{ old('data.' . $index . '.status_verifikasi') == 'Harga_Tidak_Wajar' ? 'selected' : '' }}>⚠️ Tidak Valid: Harga Tidak Wajar</option>
                                                <option value="Data_Ganda" {{ old('data.' . $index . '.status_verifikasi') == 'Data_Ganda' ? 'selected' : '' }}>⚠️ Tidak Valid: Data Ganda</option>
                                            </select>
                                        </td>                                                                             
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="button-container">
                        <button type="button" id="btnSave" class="btn btn-add">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>  
        </div>
    </div>

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
        function showAlert(action) {
            if (action === 'return') {
                alert("Data telah dikembalikan!");
            } else if (action === 'save') {
                alert("Data berhasil disimpan dan dikembalikan untuk yang tidak valid");
            }
        }
    </script>
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

        document.getElementById('saveForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Mencegah pengiriman form default
        this.submit(); // Kirim form secara manual setelah validasi atau proses lain
        });

        // Saat halaman dimuat, periksa localStorage dan atur fokus serta posisi kursor
        window.onload = function() {
            if (localStorage.getItem('focus') === 'true') {
                const searchInput = document.getElementById('searchInput');
                searchInput.focus();

                // Setel posisi kursor di akhir teks
                const query = localStorage.getItem('query');
                const cursorPosition = localStorage.getItem('cursorPosition');
                if (query !== null) {
                    searchInput.value = query;
                }
                if (cursorPosition !== null) {
                    searchInput.setSelectionRange(cursorPosition, cursorPosition);
                }

                // Hapus item dari localStorage setelah mengatur fokus
                localStorage.removeItem('focus');
                localStorage.removeItem('query');
                localStorage.removeItem('cursorPosition');
            }
        }

        // Saat halaman dimuat, periksa localStorage dan atur fokus serta posisi kursor
        window.onload = function() {
            if (localStorage.getItem('focus') === 'true') {
                const searchInput = document.getElementById('searchInput');
                searchInput.focus();

                // Setel posisi kursor di akhir teks
                const query = localStorage.getItem('query');
                const cursorPosition = localStorage.getItem('cursorPosition');
                if (query !== null) {
                    searchInput.value = query;
                }
                if (cursorPosition !== null) {
                    searchInput.setSelectionRange(cursorPosition, cursorPosition);
                }

                // Hapus item dari localStorage setelah mengatur fokus
                localStorage.removeItem('focus');
                localStorage.removeItem('query');
                localStorage.removeItem('cursorPosition');
            }
        }

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
        function setAllStatus(status) {
            if (!status) return; // kalau kosong, jangan lakukan apa-apa

            const selects = document.querySelectorAll('select[name^="data"][name$="[status_verifikasi]"]');
            selects.forEach(select => {
                select.value = status;
            });
        }
    </script>
        <script>
        document.getElementById('btnSave').addEventListener('click', function(event) {
            Swal.fire({
                title: 'Konfirmasi Simpan',
                text: 'Pastikan semua data sudah benar. Apakah Anda yakin ingin menyimpan?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // jika user klik "Ya, Simpan"
                    document.getElementById('saveForm').submit();
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // user klik Batal atau tutup dialog
                    Swal.fire(
                        'Dibatalkan',
                        'Data belum disimpan.',
                        'info'
                    );
                }
            });
        });
    </script>
</body>
</html>