<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Data;

class ApiController extends Controller {
    public function getKomoditasData(){
        // Ambil list komoditas dari tabel sumber_data
        $komoditasList = DB::table('sumber_data')->pluck('nama_komoditas');

        // Gabungkan data menjadi array per komoditas
        $result = [];

        foreach ($komoditasList as $komoditas) {
            $result[] = [
                'nama_komoditas' => $komoditas,
            ];
        }

        return response()->json($result);
    }

    public function insertFromMobile(Request $request){
        try {
            // Validasi data
            $validator = Validator::make($request->all(), [
                'tanggal' => 'required|date',
                'data' => 'required|array',
                'data.*.nama_komoditas' => 'required|string',
                'data.*.harga_hari_ini' => 'required|numeric|min:0',
            ]);
    
            if ($validator->fails()) {
                Log::warning('Validasi gagal:', $validator->errors()->toArray());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            DB::beginTransaction();
    
            $lastHet = DB::table('het')->orderByDesc('tanggal')->skip(1)->first();
            $rekapKemarin = DB::table('rekap')->orderByDesc('tanggal')->skip(1)->first();
    
            if (!$lastHet || !$rekapKemarin) {
                Log::error('Data Het atau Rekap tidak ditemukan.');
                throw new \Exception("Data Het atau Rekap tidak ditemukan.");
            }
    
            foreach ($request->data as $item) {
                $komoditas = $item['nama_komoditas'];
    
                $hargaHet = $lastHet->$komoditas ?? null;
                $hargaKemarin = $rekapKemarin->$komoditas ?? null;
                $hargaHariIni = $item['harga_hari_ini'];
    
                Log::info("Memproses: $komoditas", [
                    'harga_het' => $hargaHet,
                    'harga_kemarin' => $hargaKemarin,
                    'harga_hari_ini' => $hargaHariIni,
                ]);
    
                if ($hargaHet === null || $hargaKemarin === null) {
                    Log::warning("Data NULL untuk komoditas: $komoditas", [
                        'harga_het' => $hargaHet,
                        'harga_kemarin' => $hargaKemarin,
                    ]);
                }
    
                \App\Models\Data::create([
                    'tanggal' => $request->tanggal,
                    'nama_komoditas' => $komoditas,
                    'harga_het' => $hargaHet ?? 0,
                    'harga_kemarin' => $hargaKemarin ?? 0,
                    'harga_hari_ini' => $hargaHariIni,
                    'status_verifikasi' => 'Belum_Diverifikasi',
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil disimpan!'
            ], 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Insert Data Error: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);
    
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
          
    public function getDataKomoditas(){
        $komoditasData = Data::select('id', 'tanggal', 'nama_komoditas', 'harga_het', 'harga_kemarin', 'harga_hari_ini', 'status_verifikasi')->get();
        return response()->json($komoditasData);
    }

    public function updateHargaHariIni(Request $request, $id){
        // Validasi input
        $validated = $request->validate([
            'harga_hari_ini' => 'required|numeric',
        ]);

        // Mencari data komoditas berdasarkan ID
        $komoditas = Data::find($id);

        if ($komoditas) {
            // Update harga hari ini
            $komoditas->harga_hari_ini = $validated['harga_hari_ini'];
            $komoditas->save();

            return response()->json(['message' => 'Harga hari ini berhasil diperbarui']);
        }

        return response()->json(['message' => 'Komoditas tidak ditemukan'], 404);
    }

    public function send(Request $request)
    {
        $token = $request->input('token');
        $title = $request->input('title');
        $body = $request->input('body');

        $response = Http::withHeaders([
            'Authorization' => 'key=' . env('FCM_SERVER_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', [
            'to' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'priority' => 'high',
        ]);

        return response()->json([
            'success' => true,
            'response' => $response->json()
        ]);
    }

}