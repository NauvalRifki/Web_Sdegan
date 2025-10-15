<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Data</title>
    <link rel="stylesheet" href="{{ asset('css/verify_data_a.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">

        <a href="{{ route('dashboard_admin.sub_menu.data_pangan') }}" class="btn-back"><i class="fas fa-chevron-left"></i> Kembali</a>
        <h2 class="center-text">Verifikasi Data</h2>
        <div class="main-content">
            <!-- Alert Sukses -->
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif           

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
                <form id="saveForm" action="{{ route('dashboard_admin.sub_menu.save_data') }}" method="POST">
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
                                @forelse ($dataKomoditas as $index => $data)
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
                    <div class="button-container">
                        <button type="submit" name="action" value="save" class="btn btn-add">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>            
        </div>
    </div>
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

        document.getElementById('saveForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Mencegah pengiriman form default
        this.submit(); // Kirim form secara manual setelah validasi atau proses lain
        });

        function submitForm(event) {
            // Simpan teks pencarian dan posisi kursor di localStorage
            const searchInput = document.getElementById('searchInput');
            localStorage.setItem('focus', 'true');
            localStorage.setItem('query', searchInput.value);
            localStorage.setItem('cursorPosition', searchInput.selectionStart);

            // Hanya kirim form pencarian jika event berasal dari tombol submit atau Enter ditekan
            if (event.type === 'submit' || (event.type === 'keydown' && event.key === 'Enter')) {
                document.getElementById('searchForm').submit();
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

        // Tambahkan event listener untuk mendeteksi tombol Enter pada input search
        document.getElementById('searchInput').addEventListener('keydown', submitForm);


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

        // Tambahkan event listener untuk mendeteksi tombol Enter pada input search
        document.getElementById('searchInput').addEventListener('keydown', submitForm);
        
        setTimeout(function() {
        const alert = document.querySelector('.alert');
        if(alert) {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
        }, 3000);
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
</body>
</html>