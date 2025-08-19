<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->unique(['nama_kelas', 'academic_year_id'], 'unique_class_per_year');
        });
    }

    public function down()
    {
        Schema::table('class_groups', function (Blueprint $table) {
            $table->dropUnique('unique_class_per_year');
        });
    }

};
