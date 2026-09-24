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
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropUnique('semesters_user_year_num_unique');

            $table->unique(
                ['user_id', 'semester_number'],
                'semesters_user_num_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropUnique('semesters_user_num_unique');

            $table->unique(
                ['user_id', 'academic_year', 'semester_number'],
                'semesters_user_year_num_unique'
            );
        });
    }
};
