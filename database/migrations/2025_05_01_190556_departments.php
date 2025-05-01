<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This method creates the 'departments' table, which stores information about different departments in the organization.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Name of the department
        });
    }

    /**
     * Reverse the migrations.
     * This method drops the 'departments' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
