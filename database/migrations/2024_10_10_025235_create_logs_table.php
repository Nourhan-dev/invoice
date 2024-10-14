<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // To store create, update, delete
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  
            $table->string('role'); // Admin or Employee
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('cascade');  

            // Setting default to current timestamp
            $table->timestamp('performed_at')->default(DB::raw('CURRENT_TIMESTAMP')); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
