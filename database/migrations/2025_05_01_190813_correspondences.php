<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('correspondences', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('subject'); // Subject of the correspondence
            $table->enum('type', ['email', 'fix','letter']); // Type of correspondence
            $table->string('number'); // Unique number for the correspondence
            $table->foreignId('sender_department_id')->constrained('departments')->onDelete('cascade'); // Foreign key linking to sender department with cascading delete
            $table->foreignId('receiver_department_id')->constrained('departments')->onDelete('cascade'); // Foreign key linking to receiver department with cascading delete
            $table->string('file')->nullable(); // Optional file attachment
            $table->text('notes')->nullable(); // Optional notes
            $table->string('status')->default('pending'); // Status of the correspondence
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Foreign key linking to the user who created it with cascading delete
        });
    }

    /**
     * Reverse the migrations.
     * This method drops the 'correspondences' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondences');
    }
};
