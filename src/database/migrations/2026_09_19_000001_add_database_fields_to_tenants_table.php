<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('database_name')->nullable()->unique()->after('subdomain');
            $table->string('database_connection')->default('tenant')->after('database_name');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['database_name']);
            $table->dropColumn(['database_name', 'database_connection']);
        });
    }
};
