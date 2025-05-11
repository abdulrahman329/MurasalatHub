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
        Schema::create('correspondence_logs', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('correspondence_id'); // Foreign key linking to 'correspondences'
            $table->unsignedBigInteger('user_id'); // Foreign key linking to 'users'
            // Define foreign key constraints
            $table->foreign('correspondence_id')->references('id')->on('correspondences')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('action'); // Action performed (e.g., created, updated)
            $table->text('note')->nullable(); // Optional note about the action
            $table->timestamp('created_at')->useCurrent(); // Timestamp of the action
            
            
        });
    }

    /**
     * Reverse the migrations.
     * This method drops the 'correspondence_logs' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondence_logs');
    }
};  
