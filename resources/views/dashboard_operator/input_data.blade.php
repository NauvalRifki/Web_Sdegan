<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Input Data Pangan</title>
    <link rel="stylesheet" href="{{ asset('css/input_entry.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <a href="{{ route('dashboard_operator.data_pangan') }}" class="btn-back"><i class="fas fa-chevron-left"></i> Kembali</a>

    <div class="main-content">
        <h2 class="center-text">Input Data Pangan</h2>
        @if ($errors->has('tanggal'))
            <div class="alert alert-danger text-center" role="alert">
                {{ $errors->first('tanggal') }}
            </div>
        @endif
        <img src="{{ asset('images/input_pangan.png') }}" alt="Gambar Komoditas" class="form-image"> <!-- Gambar di luar -->
        <br>
        <form action="{{ route('dashboard_operator.data.insert') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
            </div>

            <div class="scrollable-table">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Komoditas</th>
                            <th>Harga HET</th>
                            <th>Harga Kemarin</th>
                            <th>Harga Hari Ini</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($komoditasList as $komoditas)
                        @php
                            $nama = $komoditas->nama_komoditas;
                            $hargaHet = $komoditas->hethap ?? 0;
                            $hargaKemarin = $rekapData[$nama] ?? 0;
                        @endphp
                        <tr>
                            <td>
                                {{ $nama }}
                                <input type="hidden" name="nama_komoditas[]" value="{{ $nama }}">
                            </td>
                            <td>
                                <input type="number" name="harga_het[]" value="{{ $hargaHet }}" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="number" name="harga_kemarin[]" value="{{ $hargaKemarin }}" class="form-control" readonly>
                            </td>
                            <td>
                                <input type="number" name="harga_hari_ini[]" class="form-control" required>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kirim Data
                </button>
            </div>
        </form>
    </div>  

    <!-- Toast Alert (Tampil jika tanggal sudah ada) -->
    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080">
        <div id="tanggalAlert" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Data untuk tanggal ini sudah ada. Silakan pilih tanggal lain.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Script untuk memunculkan toast -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const existingRekapDates = @json($existingRekapDates);
            const existingHetDates = @json($existingHetDates);
            const tanggalInput = document.getElementById('tanggal');

            tanggalInput.addEventListener('change', function () {
                const selectedDate = this.value;

                if (existingRekapDates.includes(selectedDate) || existingHetDates.includes(selectedDate)) {
                    const toastEl = document.getElementById('tanggalAlert');
                    const toast = new bootstrap.Toast(toastEl, {
                        delay: 2000,      
                        autohide: true   
                    });
                    toast.show();
                    this.value = '';
                }
            });
        });
    </script> 

</body>
</html>
