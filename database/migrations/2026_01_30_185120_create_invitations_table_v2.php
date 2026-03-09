<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('invitations');

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique(); // Obrigatório e único
            $table->string('token')->unique();
            $table->string('role');
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onDelete('cascade');
            $table->json('extra_permissions')->nullable();

            $table->date('expires_at')->nullable(); // Data limite de expiração (opcional)

            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
