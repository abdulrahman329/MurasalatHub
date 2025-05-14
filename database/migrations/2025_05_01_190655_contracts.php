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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('title'); // Title of the contract
            $table->date('start_date'); // Start date of the contract
            $table->date('end_date'); // End date of the contract
            $table->string('contract_type'); // Type of the contract (e.g., service, employment)
            $table->string('file')->nullable(); // Optional file attachment
            $table->foreignId('responsible_user_id') // Foreign key linking to the 'users' table
                  ->constrained('users')
                  ->onDelete('cascade'); // Cascade delete if the user is deleted
        });
    }

    /**
     * Reverse the migrations.
     * This method drops the 'contracts' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
