<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Test Catalogs (per-tenant test library) ──
        Schema::create('test_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_panel')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Panel items (tests grouped into a panel) ──
        Schema::create('test_panel_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('test_catalogs')->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('test_catalogs')->cascadeOnDelete();
        });

        // ── Appointments ──
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['scheduled', 'arrived', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Test Orders (a patient visit that generates tests) ──
        Schema::create('test_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number')->nullable();
            $table->enum('status', [
                'pending',
                'sample_collected',
                'processing',
                'results_ready',
                'finalized',
                'cancelled'
            ])->default('pending');
            $table->dateTime('sample_collected_at')->nullable();
            $table->dateTime('results_ready_at')->nullable();
            $table->dateTime('finalized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Test Order Items (individual tests within an order) ──
        Schema::create('test_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_catalog_id')->constrained()->restrictOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('result_value')->nullable();
            $table->string('result_file')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed'])->default('pending');
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();
        });

        // ── Invoices ──
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('test_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'invoice_number']);
        });

        // ── Invoice Items ──
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('test_order_items');
        Schema::dropIfExists('test_orders');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('test_panel_items');
        Schema::dropIfExists('test_catalogs');
    }
};
