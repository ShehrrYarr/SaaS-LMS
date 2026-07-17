<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // App-key-encrypted copy of the lab admin's password so the
            // superadmin can view it. Null for regular staff and for lab
            // admins whose password hasn't been set since this feature.
            $table->text('recoverable_password')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('recoverable_password');
        });
    }
};
