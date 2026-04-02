<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('report_process', function (Blueprint $table) {
            $table->id('rp_id');
            $table->uuid('rp_pid');
            $table->timestamp('rp_start_datetime')->useCurrent();
            $table->float('rp_exec_time')->nullable();
            $table->integer('ps_id');
            $table->string('rp_file_save_path')->nullable();

            $table->foreign('ps_id')->references('ps_id')->on('process_status');
        });
    }

    public function down(): void {
        Schema::dropIfExists('report_process');
    }
};
