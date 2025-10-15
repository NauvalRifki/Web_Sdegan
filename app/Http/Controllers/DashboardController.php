<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\sumber_data;
use App\Models\rekap;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller{ 
    public function getKomoditas(){
        $komoditas = sumber_data::all(['nama_komoditas']);
        return response()->json($komoditas);
    }

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

        return view('dashboard_umum', [
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

    private function calculateAverageIndicators($sevenDayAverage, $sumber_data) {
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
    
    public function filter(Request $request){
        $commodity = $request->input('commodity');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        if (!$startDate || !$endDate) {
            Log::info("Filter Error: Tanggal mulai dan akhir harus diisi.", ['commodity' => $commodity, 'startDate' => $startDate, 'endDate' => $endDate]);
            return response()->json(['error' => 'Tanggal mulai dan akhir harus diisi.'], 400);
        }
    
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
    
        Log::info("Filter Request: Retrieving records.", ['commodity' => $commodity, 'startDate' => $startDate, 'endDate' => $endDate]);
    
        $records = Rekap::whereBetween('tanggal', [$startDate, $endDate])
            ->get([$commodity, 'tanggal']);
    
        if ($records->isEmpty()) {
            Log::info("Filter Info: Data tidak ditemukan.", ['commodity' => $commodity, 'startDate' => $startDate, 'endDate' => $endDate]);
            return response()->json(['message' => 'Data tidak ditemukan.'], 404);
        }
    
        $averagePrice = ceil($records->avg($commodity));
        $latestRecord = $records->sortByDesc('tanggal')->first();
        $highestRecord = $records->sortByDesc($commodity)->first();
        $lowestRecord = $records->sortBy($commodity)->first();
        $totalRecords = $records->count();
    
        $sumberData = sumber_data::where('nama_komoditas', $commodity)->first();
    
        if (!$sumberData) {
            Log::info("Filter Error: Data SumberData tidak ditemukan.", ['commodity' => $commodity]);
            return response()->json(['error' => 'Data SumberData tidak ditemukan.'], 404);
        }
    
        $hargaIntervensi = $sumberData->harga_intervensi;
        $hargaWaspada = $sumberData->harga_waspada;
    
        $statusAverage = $averagePrice > $hargaIntervensi
            ? "<span style='color:red;'>Intervensi</span>"
            : ($averagePrice > $hargaWaspada
                ? "<span style='color:orange;'>Waspada</span>"
                : "<span style='color:green;'>Aman</span>");
    
        $previousRecord = $records->sortByDesc('tanggal')->skip(1)->first();
    
        if ($previousRecord) {
            $statusLatest = $latestRecord->{$commodity} > $hargaIntervensi
                ? "<span style='color:red;'>Intervensi</span>"
                : ($latestRecord->{$commodity} > $hargaWaspada
                    ? "<span style='color:orange;'>Waspada</span>"
                    : "<span style='color:green;'>Aman</span>");
        } else {
            $statusLatest = 'Data Sebelumnya Tidak Ada';
        }

        $hargaArray = $records->pluck($commodity)->toArray();
        $mean = $averagePrice;

        $jumlah = count($hargaArray);
        $varians = array_reduce($hargaArray, function ($carry, $value) use ($mean) {
            return $carry + pow($value - $mean, 2);
        }, 0) / $jumlah;

        $standarDeviasi = sqrt($varians);
        $cvAktual = ($mean > 0) ? round(($standarDeviasi / $mean) * 100, 2) : 0;

        $batasCV = $sumberData->cv ?? 0;
        $statusCV = $cvAktual > $batasCV
            ? "<span style='color:red;'>Fluktuatif</span>"
            : "<span style='color:green;'>Stabil</span>";
    
        $latestDate = Carbon::parse($latestRecord->tanggal)->format('Y-m-d');
        $highestDate = Carbon::parse($highestRecord->tanggal)->format('Y-m-d');
        $lowestDate = Carbon::parse($lowestRecord->tanggal)->format('Y-m-d');
    
        $data = [
            'averagePrice' => number_format($averagePrice, 0, ',', '.'),
            'latestPrice' => number_format($latestRecord->{$commodity}, 0, ',', '.'),
            'statusAverage' => $statusAverage,
            'latestDate' => $latestDate,
            'statusLatest' => $statusLatest,
            'highestPrice' => number_format($highestRecord->{$commodity}, 0, ',', '.'),
            'highestDate' => $highestDate,
            'lowestPrice' => number_format($lowestRecord->{$commodity}, 0, ',', '.'),
            'lowestDate' => $lowestDate,
            'dataCount' => $totalRecords,
            'statusCV' => $statusCV,
        ];
    
        Log::info("Filter Response: Returning filtered data.", $data);
    
        return response()->json($data);
    }    
    
    public function AllData(Request $request){
        $commodity = $request->input('commodity');
        Log::info("Commodity received: " . $commodity);

        $totalRecordsAll = Rekap::select($commodity, 'tanggal')->get();

        Log::info("Total Records Count: " . $totalRecordsAll->count());

        if ($totalRecordsAll->isEmpty()) {
            Log::info("No records found for commodity: " . $commodity);
            return response()->json([
                'totalAveragePrice' => null,
                'totalHighestPrice' => null,
                'totalHighestDate' => null,
                'totalLowestPrice' => null,
                'totalLowestDate' => null,
                'totalDataCount' => 0,
                'statusAverage' => null,
                'latestPrice' => null,
                'latestDate' => null,
                'statusLatest' => null,
                'statusCV' => null
            ]);
        }

        $totalAveragePrice = $totalRecordsAll->avg($commodity);
        $totalHighestRecord = $totalRecordsAll->sortByDesc($commodity)->first();
        $totalLowestRecord = $totalRecordsAll->sortBy($commodity)->first();
        $totalDataCount = $totalRecordsAll->count();
        $latestRecord = $totalRecordsAll->sortByDesc('tanggal')->first();

        // Ambil data sumber_data
        $sumberData = sumber_data::where('nama_komoditas', $commodity)->first();
        if (!$sumberData) {
            Log::info("Filter Error: Data SumberData tidak ditemukan.", ['commodity' => $commodity]);
            return response()->json(['error' => 'Data SumberData tidak ditemukan.'], 404);
        }

        // Perhitungan CV berdasarkan seluruh data
        $hargaArray = $totalRecordsAll->pluck($commodity)->filter(function ($value) {
            return is_numeric($value);
        })->toArray();

        $mean = $totalAveragePrice;
        $jumlah = count($hargaArray);
        $varians = array_reduce($hargaArray, function ($carry, $value) use ($mean) {
            return $carry + pow($value - $mean, 2);
        }, 0) / $jumlah;

        $standarDeviasi = sqrt($varians);
        $cvAktual = ($mean > 0) ? round(($standarDeviasi / $mean) * 100, 2) : 0;

        $batasCV = $sumberData->cv ?? 0;
        $statusCV = $cvAktual > $batasCV
            ? "<span style='color:red;'>Fluktuatif</span>"
            : "<span style='color:green;'>Stabil</span>";

        // Logika status berdasarkan HET
        $hargaIntervensi = $sumberData->harga_intervensi;
        $hargaWaspada = $sumberData->harga_waspada;

        $statusAverage = $totalAveragePrice > $hargaIntervensi
            ? "<span style='color:red;'>Intervensi</span>"
            : ($totalAveragePrice > $hargaWaspada
                ? "<span style='color:orange;'>Waspada</span>"
                : "<span style='color:green;'>Aman</span>");

        $statusLatest = $latestRecord->{$commodity} > $hargaIntervensi
            ? "<span style='color:red;'>Intervensi</span>"
            : ($latestRecord->{$commodity} > $hargaWaspada
                ? "<span style='color:orange;'>Waspada</span>"
                : "<span style='color:green;'>Aman</span>");

        $latestDate = Carbon::parse($latestRecord->tanggal)->format('Y-m-d');
        $totalHighestDate = $totalHighestRecord ? Carbon::parse($totalHighestRecord->tanggal)->format('Y-m-d') : null;
        $totalLowestDate = $totalLowestRecord ? Carbon::parse($totalLowestRecord->tanggal)->format('Y-m-d') : null;

        return response()->json([
            'totalAveragePrice' => number_format($totalAveragePrice, 0, ',', '.'),
            'totalHighestPrice' => $totalHighestRecord ? number_format($totalHighestRecord->{$commodity}, 0, ',', '.') : null,
            'totalHighestDate' => $totalHighestDate,
            'totalLowestPrice' => $totalLowestRecord ? number_format($totalLowestRecord->{$commodity}, 0, ',', '.') : null,
            'totalLowestDate' => $totalLowestDate,
            'totalDataCount' => $totalDataCount,
            'statusAverage' => $statusAverage,
            'latestPrice' => number_format($latestRecord->{$commodity}, 0, ',', '.'),
            'latestDate' => $latestDate,
            'statusLatest' => $statusLatest,
            'statusCV' => $statusCV // hanya status CV yang dikirim, bukan nilai persen
        ]);
    }

    public function grafik(Request $request){
        $commodity = $request->input('commodity');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $request->validate([
            'commodity' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Ambil data rekap
        $rekapData = DB::table('rekap')
            ->select('tanggal as date', DB::raw("`$commodity` as price"))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        // Ambil data het
        $hetData = DB::table('het')
            ->select('tanggal as date', DB::raw("`$commodity` as hethap"))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        // Ambil harga_intervensi dari sumber_data
        $hargaIntervensi = DB::table('sumber_data')
            ->where('nama_komoditas', $commodity)
            ->value('harga_intervensi');

        // Gabungkan data
        $mergedData = $rekapData->map(function ($item) use ($hetData, $hargaIntervensi) {
            $hetItem = $hetData->firstWhere('date', $item->date);
            $item->hethap = $hetItem ? $hetItem->hethap : null;
            $item->harga_intervensi = $hargaIntervensi; // tambahin di setiap data
            return $item;
        });

        return response()->json($mergedData);
    }

    public function grafikAll(Request $request) {
        // Ambil tanggal dari request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Validasi input tanggal
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // 🔥 Ambil semua kolom dari tabel rekap
        $allColumns = Schema::getColumnListing('rekap');

        // Buang kolom non-komoditas
        $commodities = array_filter($allColumns, function ($col) {
            return !in_array($col, ['id', 'tanggal']);
        });

        $result = [];

        foreach ($commodities as $commodity) {
            // Ambil data harga dari rekap
            $rekap = DB::table('rekap')
                ->select('tanggal as date', DB::raw("`$commodity` as price"))
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal')
                ->get();

            $result[$commodity] = $rekap;
        }

        return response()->json($result);
    }    

    public function generatePDFumum(Request $request){
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
}
