<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->constrained()->onDelete('cascade');
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->text('justification');
            $table->enum('statut', ['en attente', 'approuvée', 'refusée', 'active', 'terminée'])->default('en attente');
            $table->text('commentaire_responsable')->nullable();
            $table->foreignId('approuve_par')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approuve_le')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};