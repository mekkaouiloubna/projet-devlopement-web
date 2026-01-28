<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('category_id')->constrained('resource_categories')->onDelete('cascade');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->json('specifications')->nullable(); // CPU, RAM, Stockage, etc.
            $table->enum('statut', ['disponible', 'réservé', 'maintenance', 'hors_service'])->default('disponible');
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};