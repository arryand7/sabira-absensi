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
        if (!Schema::hasColumn('absensi_lokasis', 'radius')) {
            Schema::table('absensi_lokasis', function (Blueprint $table) {
                $table->decimal('radius', 8, 2)->default(0.2); // dalam kilometer
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('absensi_lokasis', 'radius')) {
            Schema::table('absensi_lokasis', function (Blueprint $table) {
                $table->dropColumn('radius');
            });
        }
    }

};
