<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caixa_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caixa_id')->nullable();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('acao', ['criado', 'editado', 'excluido']);
            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('patrimonios', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('club_id')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caixa_audit_logs');

        Schema::table('patrimonios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
