<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Rekening tujuan transfer milik petani, ditampilkan ke pembeli saat checkout. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_name', 50)->nullable()->after('address');
            $table->string('bank_account_number', 30)->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_name']);
        });
    }
};
