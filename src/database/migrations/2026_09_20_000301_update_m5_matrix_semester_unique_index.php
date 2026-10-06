<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('matriks_kesesuaians')) {
            return;
        }

        if ($this->hasIndex('matriks_kesesuaians', 'matriks_unique_dosen_mk')) {
            return;
        }

        if (! $this->hasIndex('matriks_kesesuaians', 'matriks_unique_dosen_mk_semester')) {
            Schema::table('matriks_kesesuaians', function (Blueprint $table) {
                $table->unique(['tenant_id', 'dosen_id', 'mata_kuliah_id', 'semester_id'], 'matriks_unique_dosen_mk_semester');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('matriks_kesesuaians')) {
            return;
        }

        if ($this->hasIndex('matriks_kesesuaians', 'matriks_unique_dosen_mk_semester')) {
            Schema::table('matriks_kesesuaians', function (Blueprint $table) {
                $table->dropUnique('matriks_unique_dosen_mk_semester');
            });
        }

        if (! $this->hasIndex('matriks_kesesuaians', 'matriks_unique_dosen_mk')) {
            Schema::table('matriks_kesesuaians', function (Blueprint $table) {
                $table->unique(['tenant_id', 'dosen_id', 'mata_kuliah_id'], 'matriks_unique_dosen_mk');
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
