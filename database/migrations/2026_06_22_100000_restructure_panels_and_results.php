<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Test catalogs: how results are captured (numeric value vs free text) ──
        Schema::table('test_catalogs', function (Blueprint $table) {
            $table->string('result_type')->default('numeric')->after('normal_range'); // numeric | text
        });

        // ── Replace the flat panel pivot with an ordered list of headers + tests ──
        Schema::dropIfExists('test_panel_items');

        Schema::create('panel_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('test_catalogs')->cascadeOnDelete();
            $table->string('type')->default('test'); // test | header
            $table->foreignId('test_id')->nullable()->constrained('test_catalogs')->cascadeOnDelete();
            $table->string('header_label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ── Order items: snapshot the panel/section structure at creation time ──
        Schema::table('test_order_items', function (Blueprint $table) {
            $table->foreignId('panel_id')->nullable()->after('test_catalog_id')->constrained('test_catalogs')->nullOnDelete();
            $table->string('panel_name')->nullable()->after('panel_id');     // snapshot
            $table->string('section_header')->nullable()->after('panel_name'); // snapshot
            $table->string('result_type')->default('numeric')->after('result_value'); // snapshot
            $table->unsignedInteger('sort_order')->default(0)->after('section_header');
        });
    }

    public function down(): void
    {
        Schema::table('test_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('panel_id');
            $table->dropColumn(['panel_name', 'section_header', 'result_type', 'sort_order']);
        });

        Schema::dropIfExists('panel_items');

        // recreate the original flat pivot
        Schema::create('test_panel_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('test_catalogs')->cascadeOnDelete();
            $table->foreignId('test_id')->constrained('test_catalogs')->cascadeOnDelete();
        });

        Schema::table('test_catalogs', function (Blueprint $table) {
            $table->dropColumn('result_type');
        });
    }
};
