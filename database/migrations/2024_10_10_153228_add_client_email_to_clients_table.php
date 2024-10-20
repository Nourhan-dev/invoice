<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('email')->after('phone')->nullable();
        });
    }
    
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
    
};
