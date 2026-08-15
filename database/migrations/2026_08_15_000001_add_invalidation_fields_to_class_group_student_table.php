<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand the enum before any row can be written with entered_in_error.
        // Keeping this as a separate DDL step also makes the following nullable
        // audit columns backward-compatible with the currently running release.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE class_group_student MODIFY status ENUM('active','inactive','completed','transferred','entered_in_error') NOT NULL DEFAULT 'active'");
        }

        Schema::table('class_group_student', function (Blueprint $table) {
            $table->timestamp('invalidated_at')->nullable()->after('enrollment_source');
            $table->foreignId('invalidated_by')->nullable()->after('invalidated_at')
                ->constrained('users')->nullOnDelete();
            $table->text('invalidation_reason')->nullable()->after('invalidated_by');
            $table->index(['status', 'class_group_id'], 'cgs_status_class_index');
        });
    }

    public function down(): void
    {
        Schema::table('class_group_student', function (Blueprint $table) {
            $table->dropIndex('cgs_status_class_index');
            $table->dropConstrainedForeignId('invalidated_by');
            $table->dropColumn(['invalidated_at', 'invalidation_reason']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('class_group_student')->where('status', 'entered_in_error')->update(['status' => 'inactive']);
            DB::statement("ALTER TABLE class_group_student MODIFY status ENUM('active','inactive','completed','transferred') NOT NULL DEFAULT 'active'");
        }
    }
};
