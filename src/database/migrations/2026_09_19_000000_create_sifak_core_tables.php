<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code', 32)->unique();
            $table->string('slug')->unique();
            $table->string('subdomain')->unique();
            $table->string('status')->default('active');
            $table->json('enabled_modules')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('status')->default('active')->after('password');
            $table->index(['tenant_id', 'email']);
        });

        Schema::create('program_studis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->string('degree')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('rumpun_ilmus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('kbks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('field')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('kurikulums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->year('year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'program_studi_id', 'year']);
        });

        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_studi_id')->constrained()->restrictOnDelete();
            $table->string('nim', 32);
            $table->string('name');
            $table->unsignedSmallInteger('angkatan');
            $table->unsignedTinyInteger('semester')->default(1);
            $table->decimal('ipk', 3, 2)->default(0);
            $table->unsignedSmallInteger('sks_lulus')->default(0);
            $table->string('status')->default('active');
            $table->json('interests')->nullable();
            $table->json('profile_payload')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'nim']);
        });

        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rumpun_ilmu_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('kbk_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nidn', 32);
            $table->string('name');
            $table->string('academic_position')->nullable();
            $table->json('education')->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('publications')->nullable();
            $table->json('industry_experience')->nullable();
            $table->unsignedSmallInteger('teaching_load_sks')->default(0);
            $table->unsignedSmallInteger('guidance_load')->default(0);
            $table->unsignedSmallInteger('examiner_load')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'nidn']);
        });

        Schema::create('mata_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_studi_id')->constrained()->restrictOnDelete();
            $table->foreignId('rumpun_ilmu_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->unsignedTinyInteger('sks');
            $table->unsignedTinyInteger('semester');
            $table->json('prerequisite_course_ids')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('ruangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->string('type')->default('kelas');
            $table->string('location')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('krs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained()->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('dosens')->nullOnDelete();
            $table->string('academic_year', 16);
            $table->string('term', 16);
            $table->string('status')->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->json('validation_notes')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'mahasiswa_id', 'mata_kuliah_id', 'academic_year', 'term'], 'krs_unique_course_per_term');
        });

        Schema::create('jadwal_kuliahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained()->restrictOnDelete();
            $table->foreignId('ruangan_id')->constrained()->restrictOnDelete();
            $table->string('academic_year', 16);
            $table->string('term', 16);
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('status')->default('planned');
            $table->timestamps();
            $table->index(['tenant_id', 'day_of_week', 'starts_at', 'ends_at']);
        });

        Schema::create('matriks_kesesuaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rumpun_score')->default(0);
            $table->unsignedTinyInteger('history_score')->default(0);
            $table->unsignedTinyInteger('publication_score')->default(0);
            $table->unsignedTinyInteger('certification_score')->default(0);
            $table->unsignedTinyInteger('preference_score')->default(0);
            $table->unsignedTinyInteger('overload_penalty')->default(0);
            $table->decimal('final_score', 5, 2)->default(0);
            $table->text('justification')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'dosen_id', 'mata_kuliah_id'], 'matriks_unique_dosen_mk');
        });

        Schema::create('jadwal_konsultasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('room')->nullable();
            $table->string('type')->default('offline');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dosen_lokasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('presence_status')->default('available');
            $table->timestamp('recorded_at');
            $table->timestamps();
        });

        Schema::create('pendaftaran_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pembimbing_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('penguji_1_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('penguji_2_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->foreignId('ruangan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('status')->default('submitted');
            $table->date('scheduled_date')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('result')->nullable();
            $table->text('revision_notes')->nullable();
            $table->string('minutes_file_path')->nullable();
            $table->json('validation_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('jenis_surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->json('approval_flow')->nullable();
            $table->json('merge_fields')->nullable();
            $table->text('template_body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->string('number')->nullable();
            $table->string('subject');
            $table->string('status')->default('submitted');
            $table->json('payload')->nullable();
            $table->json('attachments')->nullable();
            $table->string('generated_file_path')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'number']);
        });

        Schema::create('surat_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role_name');
            $table->unsignedTinyInteger('sequence');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('severity')->default('medium');
            $table->string('status')->default('open');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('cpls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kurikulum_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->text('description');
            $table->timestamps();
            $table->unique(['tenant_id', 'kurikulum_id', 'code']);
        });

        Schema::create('plos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kurikulum_id')->constrained()->cascadeOnDelete();
            $table->string('code', 32);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'kurikulum_id', 'code']);
        });

        Schema::create('pemetaan_mk_cpls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(1);
            $table->timestamps();
            $table->unique(['tenant_id', 'mata_kuliah_id', 'cpl_id']);
        });

        Schema::create('pemetaan_cpl_plos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plo_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(1);
            $table->timestamps();
            $table->unique(['tenant_id', 'cpl_id', 'plo_id']);
        });

        Schema::create('skor_cpl_mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->string('semester', 16);
            $table->timestamps();
            $table->unique(['tenant_id', 'mahasiswa_id', 'cpl_id', 'semester'], 'skor_cpl_unique_semester');
        });

        Schema::create('rekomendasi_plos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plo_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->json('recommendations')->nullable();
            $table->date('generated_on');
            $table->timestamps();
        });

        Schema::create('template_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('version');
            $table->string('citation_style')->default('APA');
            $table->json('format_rules')->nullable();
            $table->string('file_template_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('dokumen_tas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_dokumen_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('table_of_contents')->nullable();
            $table->longText('bibliography')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bab_tas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dokumen_ta_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('chapter_number');
            $table->string('title');
            $table->longText('content')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('dosens')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'dokumen_ta_id', 'chapter_number']);
        });

        Schema::create('log_revisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dokumen_ta_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bab_ta_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dosen_id')->nullable()->constrained()->nullOnDelete();
            $table->text('comment');
            $table->string('status')->default('revision_requested');
            $table->timestamps();
        });

        Schema::create('repositori_tas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dokumen_ta_id')->constrained()->cascadeOnDelete();
            $table->string('final_file_path');
            $table->string('access_status')->default('internal');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'repositori_tas',
            'log_revisis',
            'bab_tas',
            'dokumen_tas',
            'template_dokumens',
            'rekomendasi_plos',
            'skor_cpl_mahasiswas',
            'pemetaan_cpl_plos',
            'pemetaan_mk_cpls',
            'plos',
            'cpls',
            'alerts',
            'surat_approvals',
            'surats',
            'jenis_surats',
            'pendaftaran_sidangs',
            'dosen_lokasis',
            'jadwal_konsultasis',
            'matriks_kesesuaians',
            'jadwal_kuliahs',
            'krs',
            'ruangans',
            'mata_kuliahs',
            'dosens',
            'mahasiswas',
            'kurikulums',
            'kbks',
            'rumpun_ilmus',
            'program_studis',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id', 'email']);
            $table->dropColumn(['tenant_id', 'phone', 'status']);
        });

        Schema::dropIfExists('tenant_audit_logs');
        Schema::dropIfExists('tenants');
    }
};
