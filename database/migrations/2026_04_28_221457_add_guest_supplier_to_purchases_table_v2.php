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
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable()->change();
            if (!Schema::hasColumn('purchases', 'guest_supplier')) {
                $table->string('guest_supplier')->nullable()->after('supplier_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id')->nullable(false)->change();
            $table->dropColumn('guest_supplier');
        });
    }
};
