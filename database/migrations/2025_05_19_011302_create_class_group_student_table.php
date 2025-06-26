<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassGroupStudentTable extends Migration
{
    public function up()
    {
        Schema::create('class_group_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')
                ->constrained('academic_years');
            $table->timestamps();
            $table->unique(['class_group_id', 'student_id']); // Optional: biar kombinasi ini unik
        });
    }

    public function down()
    {
        Schema::table('class_group_student', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
}
