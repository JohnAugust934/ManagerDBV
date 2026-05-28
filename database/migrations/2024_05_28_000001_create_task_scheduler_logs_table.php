<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('task_scheduler_logs', function (Blueprint $table) {
            $table->id();
            $table->string('task_name');
            $table->enum('status', ['success', 'failed']);
            $table->text('payload')->nullable();
            $table->timestamp('executed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_scheduler_logs');
    }
};