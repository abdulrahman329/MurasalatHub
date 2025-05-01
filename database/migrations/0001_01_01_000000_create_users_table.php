<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This method creates the 'users' table, which stores information about the users of the system.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('department_id')->nullable(); // Foreign key linking to 'departments', nullable to allow users without a department
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null'); // Set department_id to null if the department is deleted
            $table->string('name'); // Name of the user
            $table->string('email')->unique(); // Unique email address of the user
            $table->timestamp('email_verified_at')->nullable(); // Timestamp for email verification
            $table->string('password'); // Password for the user
            $table->rememberToken(); // Token for "remember me" functionality
            $table->foreignId('current_team_id')->nullable(); // Foreign key for the current team, nullable
            $table->string('profile_photo_path', 2048)->nullable(); // Path to the user's profile photo, optional
            $table->timestamps(); // Timestamps for created_at and updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     * This method drops the 'users', 'password_reset_tokens', and 'sessions' tables.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
