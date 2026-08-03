<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('education_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. 'formal', 'muadalah'
            $table->string('name');
            $table->time('default_start_time')->default('07:15:00');
            $table->time('default_end_time')->default('13:05:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default programs
        DB::table('education_programs')->insert([
            [
                'code' => 'formal',
                'name' => 'Program Formal',
                'default_start_time' => '07:15:00',
                'default_end_time' => '13:05:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'muadalah',
                'name' => 'Program Muadalah',
                'default_start_time' => '16:00:00',
                'default_end_time' => '20:00:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('education_programs');
    }
};
