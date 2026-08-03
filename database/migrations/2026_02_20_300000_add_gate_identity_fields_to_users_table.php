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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gate_user_uuid')) {
                $table->uuid('gate_user_uuid')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('email');
            }
            if (! Schema::hasColumn('users', 'type')) {
                $table->string('type')->nullable()->after('username');
            }
            if (! Schema::hasColumn('users', 'application_role')) {
                $table->string('application_role')->nullable()->after('type');
            }
            if (! Schema::hasColumn('users', 'auth_source')) {
                $table->enum('auth_source', ['local', 'gate', 'hybrid'])->default('gate')->after('application_role');
            }
            if (! Schema::hasColumn('users', 'gate_photo_checksum')) {
                $table->string('gate_photo_checksum')->nullable()->after('auth_source');
            }
            if (! Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('gate_photo_checksum');
            }
            if (! Schema::hasColumn('users', 'suspension_reason')) {
                $table->string('suspension_reason')->nullable()->after('suspended_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'gate_user_uuid',
                'username',
                'type',
                'application_role',
                'auth_source',
                'gate_photo_checksum',
                'suspended_at',
                'suspension_reason',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
