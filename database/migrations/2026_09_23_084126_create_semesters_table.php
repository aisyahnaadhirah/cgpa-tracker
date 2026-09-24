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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('academic_year', 9);
            $table->unsignedSmallInteger('semester_number');
            $table->timestamps();

            //elakkan student create semester sama dua kali dalam sesi akademik yang sama
            $table->unique(
                ['user_id', 'academic_year', 'semester_number'],
                'semesters_user_year_num_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
