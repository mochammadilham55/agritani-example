<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Produk yang "diturunkan" admin ditandai terpisah dari is_active (pilihan petani),
     * agar petani tidak bisa mengaktifkannya kembali sendiri.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('is_active');
            $table->string('suspend_reason')->nullable()->after('is_suspended');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_suspended', 'suspend_reason']);
        });
    }
};
