<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('password');
            // App-key-encrypted copy so the main lab can view the branch password
            $table->text('recoverable_password')->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['tenant_id', 'email']);
            // Phone doubles as a login identifier, so it must be unique per lab
            $table->unique(['tenant_id', 'phone']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_branches')->default(0)->after('max_patients');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('tenant_id')
                  ->constrained('branches')->nullOnDelete();
        });

        Schema::table('test_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('created_by')
                  ->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_branches');
        });
        Schema::dropIfExists('branches');
    }
};
