<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dataKomoditas', function (Blueprint $table) {
            $table->integer('harga_hethap')->after('harga_hari_ini'); // Menambahkan kolom harga_hethap
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dataKomoditas', function (Blueprint $table) {
            $table->dropColumn('harga_hethap'); // Menghapus kolom harga_hethap jika migrasi di-rollback
        });
    }
};
