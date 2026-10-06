<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('dosen_profils')) {
            Schema::create('dosen_profils', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->text('profile_summary')->nullable();
                $table->text('expertise_focus')->nullable();
                $table->text('industry_experience_summary')->nullable();
                $table->string('profile_status', 30)->default('draft');
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'dosen_id']);
            });
        }

        if (! Schema::hasTable('keahlians')) {
            Schema::create('keahlians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 40);
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->foreignId('rumpun_ilmu_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
                $table->unique(['tenant_id', 'name']);
            });
        }

        if (! Schema::hasTable('dosen_keahlian')) {
            Schema::create('dosen_keahlian', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->foreignId('keahlian_id')->constrained()->cascadeOnDelete();
                $table->string('level', 30)->default('intermediate');
                $table->string('evidence_source')->nullable();
                $table->string('validation_status', 30)->default('pending');
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'dosen_id', 'keahlian_id']);
            });
        }

        if (! Schema::hasTable('dosen_pendidikans')) {
            Schema::create('dosen_pendidikans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->string('degree', 10);
                $table->string('institution', 150);
                $table->string('study_program', 150)->nullable();
                $table->unsignedSmallInteger('graduation_year')->nullable();
                $table->string('field', 150)->nullable();
                $table->string('document_path')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dosen_sertifikasis')) {
            Schema::create('dosen_sertifikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('issuer', 150)->nullable();
                $table->string('field', 150)->nullable();
                $table->string('certificate_number', 120)->nullable();
                $table->date('issued_on')->nullable();
                $table->date('expires_on')->nullable();
                $table->string('file_path')->nullable();
                $table->string('validation_status', 30)->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dosen_publikasis')) {
            Schema::create('dosen_publikasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->unsignedSmallInteger('year')->nullable();
                $table->string('type', 60)->nullable();
                $table->string('publisher', 180)->nullable();
                $table->string('doi_url')->nullable();
                $table->string('field', 150)->nullable();
                $table->json('keywords')->nullable();
                $table->string('source', 60)->default('manual');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dosen_pengalaman_industris')) {
            Schema::create('dosen_pengalaman_industris', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->string('institution', 150);
                $table->string('position', 150)->nullable();
                $table->string('field', 150)->nullable();
                $table->date('starts_on')->nullable();
                $table->date('ends_on')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('riwayat_mengajars')) {
            Schema::create('riwayat_mengajars', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademiks')->nullOnDelete();
                $table->unsignedTinyInteger('sks')->default(0);
                $table->string('class_name', 40)->nullable();
                $table->decimal('average_evaluation', 4, 2)->nullable();
                $table->unsignedSmallInteger('student_count')->default(0);
                $table->string('status', 30)->default('completed');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('dosen_preferensi_mks')) {
            Schema::create('dosen_preferensi_mks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedTinyInteger('preference_level')->default(2);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'dosen_id', 'mata_kuliah_id', 'semester_id'], 'dosen_preferensi_unique');
            });
        }

        if (! Schema::hasTable('beban_dosens')) {
            Schema::create('beban_dosens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedSmallInteger('teaching_sks')->default(0);
                $table->unsignedSmallInteger('guidance_count')->default(0);
                $table->unsignedSmallInteger('examiner_count')->default(0);
                $table->unsignedSmallInteger('research_load')->default(0);
                $table->decimal('workload_score', 5, 2)->default(0);
                $table->string('workload_status', 30)->default('low');
                $table->timestamp('calculated_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'dosen_id', 'semester_id'], 'beban_dosen_unique');
            });
        }

        if (! Schema::hasTable('rekomendasi_pengampus')) {
            Schema::create('rekomendasi_pengampus', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
                $table->unsignedTinyInteger('ranking');
                $table->decimal('score', 5, 2)->default(0);
                $table->json('score_breakdown')->nullable();
                $table->text('summary_reason')->nullable();
                $table->string('status', 30)->default('generated');
                $table->text('justification')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'mata_kuliah_id', 'semester_id'], 'rekomendasi_mk_semester_index');
            });
        }

        $this->table('matriks_kesesuaians', function (Blueprint $table) {
            $this->foreignNullable($table, 'semester_id', 'mata_kuliah_id', fn (Blueprint $table) => $table->foreignId('semester_id')->nullable()->after('mata_kuliah_id')->constrained()->nullOnDelete());
            $this->jsonNullable($table, 'score_breakdown', 'final_score');
            $this->timestampNullable($table, 'generated_at', 'score_breakdown');
        });

        $this->table('dosen_lokasis', function (Blueprint $table) {
            $this->decimalNullable($table, 'accuracy', 'longitude');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_pengampus');
        Schema::dropIfExists('beban_dosens');
        Schema::dropIfExists('dosen_preferensi_mks');
        Schema::dropIfExists('riwayat_mengajars');
        Schema::dropIfExists('dosen_pengalaman_industris');
        Schema::dropIfExists('dosen_publikasis');
        Schema::dropIfExists('dosen_sertifikasis');
        Schema::dropIfExists('dosen_pendidikans');
        Schema::dropIfExists('dosen_keahlian');
        Schema::dropIfExists('keahlians');
        Schema::dropIfExists('dosen_profils');
    }

    private function table(string $table, callable $callback): void
    {
        if (Schema::hasTable($table)) {
            Schema::table($table, $callback);
        }
    }

    private function foreignNullable(Blueprint $table, string $column, string $after, callable $definition): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $definition($table);
        }
    }

    private function jsonNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->json($column)->nullable()->after($after);
        }
    }

    private function timestampNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->timestamp($column)->nullable()->after($after);
        }
    }

    private function decimalNullable(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn($table->getTable(), $column)) {
            $table->decimal($column, 8, 2)->nullable()->after($after);
        }
    }
};
