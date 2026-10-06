<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('periode_krs')) {
            Schema::create('periode_krs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->nullOnDelete();
                $table->dateTime('tanggal_mulai');
                $table->dateTime('tanggal_selesai');
                $table->dateTime('tanggal_revisi_mulai')->nullable();
                $table->dateTime('tanggal_revisi_selesai')->nullable();
                $table->string('status', 30)->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['tenant_id', 'semester_id', 'status']);
            });
        }

        if (! Schema::hasTable('penawaran_mata_kuliah')) {
            Schema::create('penawaran_mata_kuliah', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_studi_id')->constrained('program_studis')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
                $table->foreignId('kurikulum_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedSmallInteger('kuota_default')->default(35);
                $table->unsignedSmallInteger('minimal_peserta')->default(10);
                $table->unsignedSmallInteger('maksimal_peserta')->default(35);
                $table->unsignedSmallInteger('target_jumlah_kelas')->default(1);
                $table->unsignedSmallInteger('jumlah_peminat')->default(0);
                $table->string('status', 30)->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['tenant_id', 'semester_id', 'program_studi_id', 'mata_kuliah_id'], 'penawaran_mk_unique');
            });
        }

        $this->alignKrsTable();

        if (! Schema::hasTable('kelas_kuliah')) {
            Schema::create('kelas_kuliah', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('penawaran_mata_kuliah_id')->constrained('penawaran_mata_kuliah')->cascadeOnDelete();
                $table->string('kode_kelas', 60);
                $table->unsignedSmallInteger('kapasitas')->default(35);
                $table->unsignedSmallInteger('jumlah_peserta')->default(0);
                $table->string('status', 30)->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['tenant_id', 'penawaran_mata_kuliah_id', 'kode_kelas'], 'kelas_kuliah_unique');
            });
        }

        if (! Schema::hasTable('krs_details')) {
            Schema::create('krs_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('krs_id')->constrained('krs')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained()->restrictOnDelete();
                $table->foreignId('penawaran_mata_kuliah_id')->nullable()->constrained('penawaran_mata_kuliah')->nullOnDelete();
                $table->foreignId('kelas_kuliah_id')->nullable()->constrained('kelas_kuliah')->nullOnDelete();
                $table->unsignedTinyInteger('sks')->default(0);
                $table->string('status', 30)->default('selected');
                $table->string('validation_status', 30)->default('valid');
                $table->text('validation_note')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'krs_id', 'mata_kuliah_id'], 'krs_detail_unique_course');
            });
        }

        if (! Schema::hasTable('krs_validation_results')) {
            Schema::create('krs_validation_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('krs_id')->constrained('krs')->cascadeOnDelete();
                $table->foreignId('krs_detail_id')->nullable()->constrained('krs_details')->cascadeOnDelete();
                $table->string('validation_code', 80);
                $table->string('severity', 20)->default('info');
                $table->boolean('passed')->default(true);
                $table->text('message');
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['tenant_id', 'krs_id', 'passed', 'severity']);
            });
        }

        if (! Schema::hasTable('plotting_dosen')) {
            Schema::create('plotting_dosen', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->restrictOnDelete();
                $table->foreignId('rekomendasi_pengampu_id')->nullable()->constrained('rekomendasi_pengampus')->nullOnDelete();
                $table->string('role_pengampu', 40)->default('utama');
                $table->unsignedTinyInteger('sks_beban')->default(0);
                $table->string('status', 30)->default('draft');
                $table->foreignId('selected_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('justification')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'kelas_kuliah_id', 'status']);
            });
        }

        $this->alignJadwalKuliahTable();

        if (! Schema::hasTable('jadwal_histories')) {
            Schema::create('jadwal_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('jadwal_kuliah_id')->constrained('jadwal_kuliahs')->cascadeOnDelete();
                $table->unsignedTinyInteger('old_day_of_week')->nullable();
                $table->time('old_starts_at')->nullable();
                $table->time('old_ends_at')->nullable();
                $table->foreignId('old_ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->unsignedTinyInteger('new_day_of_week')->nullable();
                $table->time('new_starts_at')->nullable();
                $table->time('new_ends_at')->nullable();
                $table->foreignId('new_ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('jadwal_conflicts')) {
            Schema::create('jadwal_conflicts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('jadwal_kuliah_id')->constrained('jadwal_kuliahs')->cascadeOnDelete();
                $table->string('conflict_type', 60);
                $table->unsignedBigInteger('conflict_with_id')->nullable();
                $table->string('severity', 20)->default('error');
                $table->text('message');
                $table->boolean('resolved')->default(false);
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'resolved', 'severity']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_conflicts');
        Schema::dropIfExists('jadwal_histories');
        Schema::dropIfExists('plotting_dosen');
        Schema::dropIfExists('krs_validation_results');
        Schema::dropIfExists('krs_details');
        Schema::dropIfExists('kelas_kuliah');
        Schema::dropIfExists('penawaran_mata_kuliah');
        Schema::dropIfExists('periode_krs');
    }

    private function alignKrsTable(): void
    {
        if (! Schema::hasTable('krs')) {
            return;
        }

        if (Schema::hasColumn('krs', 'mata_kuliah_id')) {
            DB::statement('ALTER TABLE krs MODIFY mata_kuliah_id BIGINT UNSIGNED NULL');
        }

        Schema::table('krs', function (Blueprint $table) {
            $this->foreignNullable($table, 'semester_id', 'mahasiswa_id', fn (Blueprint $table) => $table->foreignId('semester_id')->nullable()->after('mahasiswa_id')->constrained()->nullOnDelete());
            $this->foreignNullable($table, 'tahun_akademik_id', 'semester_id', fn (Blueprint $table) => $table->foreignId('tahun_akademik_id')->nullable()->after('semester_id')->constrained('tahun_akademiks')->nullOnDelete());
            $this->foreignNullable($table, 'dosen_pa_id', 'tahun_akademik_id', fn (Blueprint $table) => $table->foreignId('dosen_pa_id')->nullable()->after('tahun_akademik_id')->constrained('dosens')->nullOnDelete());
            $this->unsignedSmallInteger($table, 'total_sks', 'term');
            $this->timestampNullable($table, 'submitted_at', 'status');
            $this->timestampNullable($table, 'finalized_at', 'approved_at');
            $this->foreignNullable($table, 'finalized_by', 'finalized_at', fn (Blueprint $table) => $table->foreignId('finalized_by')->nullable()->after('finalized_at')->constrained('users')->nullOnDelete());
            $this->textNullable($table, 'note', 'validation_notes');
        });
    }

    private function alignJadwalKuliahTable(): void
    {
        if (! Schema::hasTable('jadwal_kuliahs')) {
            return;
        }

        if (Schema::hasColumn('jadwal_kuliahs', 'ruangan_id')) {
            DB::statement('ALTER TABLE jadwal_kuliahs MODIFY ruangan_id BIGINT UNSIGNED NULL');
        }

        Schema::table('jadwal_kuliahs', function (Blueprint $table) {
            $this->foreignNullable($table, 'semester_id', 'tenant_id', fn (Blueprint $table) => $table->foreignId('semester_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete());
            $this->foreignNullable($table, 'kelas_kuliah_id', 'semester_id', fn (Blueprint $table) => $table->foreignId('kelas_kuliah_id')->nullable()->after('semester_id')->constrained('kelas_kuliah')->nullOnDelete());
            $this->stringNullable($table, 'mode', 'term', 'onsite');
            $this->unsignedTinyInteger($table, 'minggu_mulai', 'ends_at');
            $this->unsignedTinyInteger($table, 'minggu_selesai', 'minggu_mulai');
            $this->foreignNullable($table, 'created_by', 'status', fn (Blueprint $table) => $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete());
            $this->foreignNullable($table, 'updated_by', 'created_by', fn (Blueprint $table) => $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete());
            $this->timestampNullable($table, 'finalized_at', 'updated_by');
            $this->stringNullable($table, 'conflict_status', 'finalized_at', 'unchecked');
        });
    }

    private function foreignNullable(Blueprint $table, string $column, string $after, callable $definition): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $definition($table);
        }
    }

    private function unsignedSmallInteger(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->unsignedSmallInteger($column)->default(0)->after($after);
        }
    }

    private function unsignedTinyInteger(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->unsignedTinyInteger($column)->nullable()->after($after);
        }
    }

    private function timestampNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->timestamp($column)->nullable()->after($after);
        }
    }

    private function textNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->text($column)->nullable()->after($after);
        }
    }

    private function stringNullable(Blueprint $table, string $column, string $after, ?string $default = null): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $definition = $table->string($column, 30)->nullable()->after($after);

            if ($default !== null) {
                $definition->default($default);
            }
        }
    }

};
