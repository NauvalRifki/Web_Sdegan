<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\sumber_data;
use App\Models\rekap;
use App\Models\Data;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;



class VerifikatorController extends Controller
{
    
    public function containerbesar() {
        $sumber_data = sumber_data::all();

        // Ambil dua data rekap terakhir berdasarkan tanggal
        $rekap = rekap::orderBy('tanggal', 'desc')->take(2)->get();
        $latestRekap = $rekap->first();
        $previousRekap = $rekap->count() > 1 ? $rekap->last() : null;

        // 🔥 Ambil semua nama kolom dari tabel rekap
        $allColumns = Schema::getColumnListing('rekap');

        // Buang kolom yang bukan komoditas
        $komoditasColumns = array_filter($allColumns, function ($col) {
            return !in_array($col, ['id', 'tanggal']); 
        });

        // Hitung rata-rata 7 hari
        $sevenDayAverage = [];
        foreach ($komoditasColumns as $column) {
            $prices = rekap::orderBy('tanggal', 'desc')->limit(7)->get([$column]);

            $filteredPrices = $prices->filter(function ($price) use ($column) {
                return is_numeric($price->$column);
            });

            $sevenDayAverage[$column] = $filteredPrices->count() > 0 
                ? $filteredPrices->avg($column) 
                : null;
        }

        // Hitung indikator naik/turun
        $priceChanges = [];
        if ($previousRekap) {
            foreach ($komoditasColumns as $column) {
                $latestPrice = $latestRekap->$column;
                $previousPrice = $previousRekap->$column;

                if (is_numeric($latestPrice) && is_numeric($previousPrice)) {
                    if ($latestPrice > $previousPrice) {
                        $priceChanges[$column] = 'up';
                    } elseif ($latestPrice < $previousPrice) {
                        $priceChanges[$column] = 'down';
                    } else {
                        $priceChanges[$column] = 'same';
                    }
                } else {
                    $priceChanges[$column] = null;
                }
            }
        }

        $indicators = $this->calculateIndicators($latestRekap, $sumber_data);
        $averageIndicators = $this->calculateAverageIndicators($sevenDayAverage, $sumber_data);

        return view('dashboard_verifikator.index', [
            'sumber_data' => $sumber_data,
            'latestRekap' => $latestRekap,
            'previousRekap' => $previousRekap,
            'sevenDayAverage' => $sevenDayAverage,
            'indicators' => $indicators,
            'averageIndicators' => $averageIndicators,
            'priceChanges' => $priceChanges,
            'komoditasColumns' => $komoditasColumns,
        ]);
    }  

    //Indikator Rata rata hari H container besar
    private function calculateIndicators($latestRekap, $sumber_data){
        $indicators = [];
    
        foreach ($sumber_data as $data) {
            $namaKomoditas = $data->nama_komoditas;
            $hargaH = $latestRekap->{$namaKomoditas};
    
            Log::info("Nama Komoditas: $namaKomoditas, Harga H: $hargaH, HET/HAP: {$data->hethap}, Harga Waspada: {$data->harga_waspada}, Harga Intervensi: {$data->harga_intervensi}");
    
            if ($hargaH < $data->hethap) {
                $waspadaLimit = $data->hethap * (1 + $data->waspada / 100);
                $intervensiLimit = $data->hethap * (1 + $data->intervensi / 100);
    
                Log::info("Waspada Limit: $waspadaLimit, Intervensi Limit: $intervensiLimit");
    
                if ($hargaH > $intervensiLimit) {
                    $indicators[$namaKomoditas] = "Intervensi";
                } elseif ($hargaH > $waspadaLimit) {
                    $indicators[$namaKomoditas] = "Waspada";
                } else {
                    $indicators[$namaKomoditas] = "Aman";
                }
            } else {
                if ($hargaH > $data->harga_intervensi) {
                    $indicators[$namaKomoditas] = "Intervensi";
                } elseif ($hargaH > $data->harga_waspada) {
                    $indicators[$namaKomoditas] = "Waspada";
                } else {
                    $indicators[$namaKomoditas] = "Aman";
                }
            }
        }
    
        return $indicators;
    }       

    //Indikator Rata rata 7 hari container besar
    private function calculateAverageIndicators($sevenDayAverage, $sumber_data){
        $averageIndicators = [];
    
        foreach ($sumber_data as $data) {
            $namaKomoditas = $data->nama_komoditas;
            $averagePrice = $sevenDayAverage[$namaKomoditas] ?? null;
    
            Log::info("Nama Komoditas: $namaKomoditas, Harga Rata-rata 7 Hari: $averagePrice, HET/HAP: {$data->hethap}, Harga Waspada: {$data->harga_waspada}, Harga Intervensi: {$data->harga_intervensi}");
    
            if ($averagePrice !== null) {
                if ($averagePrice < $data->hethap) {
                    $waspadaLimit = $data->hethap * (1 + $data->waspada / 100);
                    $intervensiLimit = $data->hethap * (1 + $data->intervensi / 100);
    
                    Log::info("Waspada Limit: $waspadaLimit, Intervensi Limit: $intervensiLimit");
    
                    if ($averagePrice > $intervensiLimit) {
                        $averageIndicators[$namaKomoditas] = "Intervensi";
                    } elseif ($averagePrice > $waspadaLimit) {
                        $averageIndicators[$namaKomoditas] = "Waspada";
                    } else {
                        $averageIndicators[$namaKomoditas] = "Aman";
                    }
                } else {
                    if ($averagePrice > $data->harga_intervensi) {
                        $averageIndicators[$namaKomoditas] = "Intervensi";
                    } elseif ($averagePrice > $data->harga_waspada) {
                        $averageIndicators[$namaKomoditas] = "Waspada";
                    } else {
                        $averageIndicators[$namaKomoditas] = "Aman";
                    }
                }
            } else {
                $averageIndicators[$namaKomoditas] = 'N/A';
            }
        }
    
        return $averageIndicators;
    }

    public function saveDataverifikator(Request $request) {
        $dataList = $request->input('data', []);
        $action = $request->input('action');
    
        if (empty($dataList)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dikirim.');
        }
    
        // Proses simpan/update data komoditas
        foreach ($dataList as $data) {
            $tanggal = $data['tanggal'];
            $namaKomoditas = $data['nama_komoditas'];
    
            Data::updateOrCreate(
                [
                    'tanggal' => $tanggal,
                    'nama_komoditas' => $namaKomoditas,
                ],
                [
                    'harga_het' => $data['harga_het'],
                    'harga_kemarin' => $data['harga_kemarin'],
                    'harga_hari_ini' => $data['harga_hari_ini'],
                    'status_verifikasi' => $data['status_verifikasi'],
                ]
            );
        }
    
        // Kalau aksi adalah "return", langsung kembalikan tanpa update rekap/het
        if ($action === 'return') {
            return redirect()->route('dashboard_verifikator.verify_data')
                ->with('success', 'Data berhasil dikembalikan ke operator!');
        }
    
        // Kalau aksi adalah "save", lanjut update ke rekap dan het
        $rekapDataArray = [];
        $hetDataArray = [];
    
        foreach ($dataList as $data) {
            if ($data['status_verifikasi'] === 'Valid') {
                $tanggal = $data['tanggal'];
                $namaKomoditas = $data['nama_komoditas'];
    
                // Isi array berdasarkan tanggal dan komoditas
                $rekapDataArray[$tanggal][$namaKomoditas] = $data['harga_hari_ini'];
                $hetDataArray[$tanggal][$namaKomoditas] = $data['harga_het'];
            }
        }
    
        // Simpan ke tabel rekap
        foreach ($rekapDataArray as $tanggal => $rekapData) {
            DB::table('rekap')
                ->updateOrInsert(
                    ['tanggal' => $tanggal],
                    $rekapData
                );
        }
    
        // Simpan ke tabel het
        foreach ($hetDataArray as $tanggal => $hetData) {
            DB::table('het')
                ->updateOrInsert(
                    ['tanggal' => $tanggal],
                    $hetData
                );
        }

        // 🔥 Hapus data dari tabel 'datakomoditas' jika statusnya Valid
        foreach ($dataList as $data) {
            if ($data['status_verifikasi'] === 'Valid') {
                Data::where('tanggal', $data['tanggal'])
                    ->where('nama_komoditas', $data['nama_komoditas'])
                    ->delete();
            }
        }
    
        return redirect()->route('dashboard_verifikator.verify_data')
            ->with('success', 'Data berhasil disimpan!');
    }    

    public function generatePDFveri(Request $request) {
        Log::info('Memulai proses generate PDF', ['input' => $request->all()]);

        try {
            $validated = $request->validate([
                'commodity' => 'required|string',
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);

            $commodity = $validated['commodity'];
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);

            Log::debug('Parameter valid', [
                'commodity' => $commodity,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString()
            ]);

            // Ambil data HET, Waspada, dan Intervensi dari sumber_data
            $sumberDataKomoditas = sumber_data::where('nama_komoditas', $commodity)->first();
            $hargaHet = $sumberDataKomoditas->hethap ?? null;
            $hargaWaspada = $sumberDataKomoditas->harga_waspada ?? null;
            $hargaIntervensi = $sumberDataKomoditas->harga_intervensi ?? null;

            Log::debug('Data sumber diperoleh', [
                'harga_het' => $hargaHet,
                'harga_waspada' => $hargaWaspada,
                'harga_intervensi' => $hargaIntervensi
            ]);

            // Ambil data dalam rentang yang lebih luas untuk perhitungan rata-rata 7 hari
            $extendedStartDate = $startDate->copy()->subDays(7); 

            $rawData = Rekap::select('tanggal', $commodity)
                ->whereBetween('tanggal', [$extendedStartDate, $endDate])
                ->orderBy('tanggal', 'asc')
                ->get();
            
            $priceMap = $rawData->keyBy(function ($item) {
                return Carbon::parse($item->tanggal)->toDateString();
            });

            $processedData = collect(); 
            
            $currentDate = $startDate->copy();
            while ($currentDate->lessThanOrEqualTo($endDate)) {
                $todayDateString = $currentDate->toDateString();
                
                if ($priceMap->has($todayDateString)) {
                    $hargaHariIni = $priceMap[$todayDateString]->$commodity;

                    $hargaKemarin = 0;
                    $yesterdayString = $currentDate->copy()->subDay()->toDateString();
                    if ($priceMap->has($yesterdayString)) {
                        $hargaKemarin = $priceMap[$yesterdayString]->$commodity;
                    }

                    $sum7Days = 0;
                    $count7Days = 0;
                    for ($i = 0; $i < 7; $i++) {
                        $pastDateString = $currentDate->copy()->subDays($i)->toDateString();
                        if ($priceMap->has($pastDateString)) {
                            $sum7Days += $priceMap[$pastDateString]->$commodity;
                            $count7Days++;
                        }
                    }
                    $hargaRataRata = ($count7Days > 0) ? $sum7Days / $count7Days : 0;

                    $processedData->push((object) [
                        'tanggal' => $todayDateString,
                        'nama_komoditas' => $commodity,
                        'harga_hari_ini' => $hargaHariIni,
                        'harga_kemarin' => $hargaKemarin,
                        'harga_rata_rata_7_hari' => $hargaRataRata,
                        'harga_het' => $hargaHet,
                        'harga_waspada' => $hargaWaspada,
                        'harga_intervensi' => $hargaIntervensi
                    ]);
                }
                
                $currentDate->addDay();
            }

            Log::debug('Data diproses', ['jumlah_record' => count($processedData)]);

            if ($processedData->isEmpty()) {
                Log::warning('Data tidak ditemukan', [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'commodity' => $commodity
                ]);
                return back()->with('error', 'Data tidak ditemukan untuk rentang tanggal yang dipilih.');
            }

            // Generate PDF
            $pdf = PDF::loadView('pdf.report', [
                'data' => $processedData,
                'startDate' => $startDate->format('d-m-Y'),
                'endDate' => $endDate->format('d-m-Y'),
                'commodity' => $commodity,
                'hargaHet' => $hargaHet, // Juga kirim sebagai variabel terpisah untuk info header
                'hargaWaspada' => $hargaWaspada, // Baru: Kirim untuk info header
                'hargaIntervensi' => $hargaIntervensi // Baru: Kirim untuk info header
            ])->setPaper('A4', 'landscape');

            Log::info('PDF berhasil digenerate', [
                'commodity' => $commodity,
                'periode' => "{$startDate->toDateString()} sampai {$endDate->toDateString()}",
                'file_size' => strlen($pdf->output())
            ]);

            return $pdf->download('commodity_report.pdf');

        } catch (\Exception $e) {
            Log::error('Gagal generate PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat membuat laporan: ' . $e->getMessage());
        }
    }    

    public function updateStatusVerifikasiVer(Request $request, $id){
        $validated = $request->validate([
            'status_verifikasi' => 'required|in:Valid,Tidak Valid,Belum Diverifikasi',
        ]);
    
        $dataKomoditas = Data::findOrFail($id);
        $dataKomoditas->status_verifikasi = $request->status_verifikasi;
        $dataKomoditas->save();
    
        return redirect()->route('dashboard_verifikator.verifikasi_data')->with('success', 'Status data berhasil diperbarui!');
    }   

    public function kembalikanDataVer(Request $request){
        $dataList = $request->input('data');
    
        foreach ($dataList as $data) {
            $dataKomoditas = Data::find($data['id']);
            if ($dataKomoditas) {
                $dataKomoditas->status_verifikasi = $data['status_verifikasi'];
                $dataKomoditas->save();
            }
        }
    
        return redirect()->route('dashboard_verifikator.verifikasi_data')->with('success', 'Data berhasil dikembalikan ke Operator!');
    }  
}
