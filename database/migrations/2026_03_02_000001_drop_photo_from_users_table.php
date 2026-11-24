<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Foto profil tidak ditampilkan di mana pun, jadi kolomnya dihapus. */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('bank_account_name');
        });
    }
};
