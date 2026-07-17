<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('superadmins', function (Blueprint $table) {
            // App-key-encrypted copy of the superadmin's password so it can be
            // viewed on the settings page. Captured on the next password change.
            $table->text('recoverable_password')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('superadmins', function (Blueprint $table) {
            $table->dropColumn('recoverable_password');
        });
    }
};
