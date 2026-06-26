<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_backup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('disk', 20);
            $table->string('path');
            $table->string('filename');
            $table->string('status', 20); // success, failed
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 64)->nullable();
            $table->boolean('has_uploads')->default(false);
            $table->text('error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origin', 30)->default('manual'); // manual, scheduled
            $table->timestamps();

            $table->index(['club_id', 'status', 'created_at'], 'club_backup_logs_club_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_backup_logs');
    }
};
