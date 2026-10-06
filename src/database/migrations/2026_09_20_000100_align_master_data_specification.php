<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fakultas')) {
            Schema::create('fakultas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 20);
                $table->string('name', 150);
                $table->string('short_name', 50);
                $table->text('address')->nullable();
                $table->string('email', 150)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('logo_path')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['tenant_id', 'code']);
                $table->unique(['tenant_id', 'short_name']);
            });
        }

        if (! Schema::hasTable('tahun_akademiks')) {
            Schema::create('tahun_akademiks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 20);
                $table->unsignedSmallInteger('start_year');
                $table->unsignedSmallInteger('end_year');
                $table->string('status')->default('planned');
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('semesters')) {
            Schema::create('semesters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tahun_akademik_id')->constrained('tahun_akademiks')->cascadeOnDelete();
                $table->string('name', 20);
                $table->string('code', 20);
                $table->date('starts_on');
                $table->date('ends_on');
                $table->string('status')->default('planned');
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['tenant_id', 'code']);
            });
        }

        $this->table('program_studis', function (Blueprint $table) {
            $this->foreignNullable($table, 'fakultas_id', 'tenant_id', fn (Blueprint $table) => $table->foreignId('fakultas_id')->nullable()->after('tenant_id')->constrained('fakultas')->nullOnDelete());
            $this->foreignNullable($table, 'kaprodi_dosen_id', 'degree', fn (Blueprint $table) => $table->foreignId('kaprodi_dosen_id')->nullable()->after('degree')->constrained('dosens')->nullOnDelete());
            $this->softDeletes($table);
        });

        $this->table('mahasiswas', function (Blueprint $table) {
            $this->stringNullable($table, 'email', 'name', 150);
            $this->stringNullable($table, 'phone', 'email', 30);
            $this->softDeletes($table);
        });

        $this->table('dosens', function (Blueprint $table) {
            $this->stringNullable($table, 'email', 'name', 150);
            $this->stringNullable($table, 'last_education', 'academic_position', 10);
            $this->softDeletes($table);
        });

        $this->table('mata_kuliahs', function (Blueprint $table) {
            $this->stringDefault($table, 'type', 'semester', 'wajib', 20);
            $this->softDeletes($table);
        });

        $this->table('kurikulums', function (Blueprint $table) {
            $this->stringNullable($table, 'code', 'program_studi_id', 50);
            $this->unsignedNullable($table, 'start_year', 'year');
            $this->unsignedNullable($table, 'end_year', 'start_year');
            $this->unsignedDefault($table, 'total_sks', 'end_year', 144);
            $this->stringDefault($table, 'status', 'is_active', 'draft', 20);
            $this->softDeletes($table);
        });

        $this->table('ruangans', function (Blueprint $table) {
            $this->stringNullable($table, 'building', 'location', 100);
            $this->stringNullable($table, 'floor', 'building', 20);
            $this->stringDefault($table, 'status', 'type', 'active', 20);
            $this->softDeletes($table);
        });

        $this->table('kbks', function (Blueprint $table) {
            $this->stringNullable($table, 'code', 'tenant_id', 30);
            $this->textNullable($table, 'description', 'field');
            $this->foreignNullable($table, 'ketua_dosen_id', 'description', fn (Blueprint $table) => $table->foreignId('ketua_dosen_id')->nullable()->after('description')->constrained('dosens')->nullOnDelete());
            $this->stringDefault($table, 'status', 'ketua_dosen_id', 'active', 20);
            $this->softDeletes($table);
        });

        $this->table('rumpun_ilmus', function (Blueprint $table) {
            $this->stringNullable($table, 'code', 'tenant_id', 30);
            $this->stringDefault($table, 'status', 'description', 'active', 20);
            $this->softDeletes($table);
        });

        if (! Schema::hasTable('mata_kuliah_prasyarats')) {
            Schema::create('mata_kuliah_prasyarats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
                $table->foreignId('prasyarat_mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
                $table->string('minimum_grade', 5)->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'mata_kuliah_id', 'prasyarat_mata_kuliah_id'], 'mk_prasyarat_unique');
            });
        }

        if (! Schema::hasTable('kurikulum_mata_kuliah')) {
            Schema::create('kurikulum_mata_kuliah', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('kurikulum_id')->constrained('kurikulums')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
                $table->unsignedTinyInteger('semester')->default(1);
                $table->boolean('is_required')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'kurikulum_id', 'mata_kuliah_id'], 'kurikulum_mk_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kurikulum_mata_kuliah');
        Schema::dropIfExists('mata_kuliah_prasyarats');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('tahun_akademiks');
        Schema::dropIfExists('fakultas');
    }

    private function table(string $table, callable $callback): void
    {
        if (Schema::hasTable($table)) {
            Schema::table($table, $callback);
        }
    }

    private function stringNullable(Blueprint $table, string $column, string $after, int $length): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->string($column, $length)->nullable()->after($after);
        }
    }

    private function textNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->text($column)->nullable()->after($after);
        }
    }

    private function stringDefault(Blueprint $table, string $column, string $after, string $default, int $length): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->string($column, $length)->default($default)->after($after);
        }
    }

    private function unsignedNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->unsignedSmallInteger($column)->nullable()->after($after);
        }
    }

    private function unsignedDefault(Blueprint $table, string $column, string $after, int $default): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->unsignedSmallInteger($column)->default($default)->after($after);
        }
    }

    private function foreignNullable(Blueprint $table, string $column, string $after, callable $definition): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $definition($table);
        }
    }

    private function softDeletes(Blueprint $table): void
    {
        if (! Schema::hasColumn($table->getTable(), 'deleted_at')) {
            $table->softDeletes();
        }
    }
};
