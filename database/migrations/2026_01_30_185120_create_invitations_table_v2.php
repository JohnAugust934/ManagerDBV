<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Se a tabela já existir de testes anteriores, vamos recriá-la
        Schema::dropIfExists('invitations');

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('token')->unique();
            $table->string('role'); // diretor, conselheiro, etc.
            $table->foreignId('club_id')->nullable()->constrained('clubs')->onDelete('cascade');
            $table->json('extra_permissions')->nullable(); // Permissões extras
            $table->timestamp('registered_at')->nullable(); // Se já foi usado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
