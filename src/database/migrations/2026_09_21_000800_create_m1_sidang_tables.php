<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sidang_types')) {
            Schema::create('sidang_types', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 40);
                $table->string('name', 120);
                $table->text('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('sidang_requirements')) {
            Schema::create('sidang_requirements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_type_id')->constrained('sidang_types')->cascadeOnDelete();
                $table->string('code', 80);
                $table->string('name', 160);
                $table->string('requirement_type', 40)->default('system');
                $table->boolean('required')->default(true);
                $table->string('source_module', 40)->default('M1');
                $table->json('validation_rule')->nullable();
                $table->unsignedSmallInteger('sequence')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_type_id', 'code'], 'sidang_requirement_unique_code');
            });
        }

        if (! Schema::hasTable('sidang_registrations')) {
            Schema::create('sidang_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_type_id')->constrained('sidang_types')->cascadeOnDelete();
                $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tugas_akhir_id')->nullable()->constrained('tugas_akhirs')->nullOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademiks')->nullOnDelete();
                $table->string('registration_number', 80)->nullable();
                $table->string('status', 40)->default('draft');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'registration_number']);
                $table->index(['tenant_id', 'mahasiswa_id', 'status']);
            });
        }

        if (! Schema::hasTable('sidang_requirement_results')) {
            Schema::create('sidang_requirement_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('sidang_requirement_id')->constrained('sidang_requirements')->cascadeOnDelete();
                $table->string('status', 30)->default('pending');
                $table->string('source_reference_type')->nullable();
                $table->string('source_reference_id')->nullable();
                $table->text('note')->nullable();
                $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('checked_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_registration_id', 'sidang_requirement_id'], 'sidang_req_result_unique');
            });
        }

        if (! Schema::hasTable('sidang_files')) {
            Schema::create('sidang_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('sidang_requirement_id')->nullable()->constrained('sidang_requirements')->nullOnDelete();
                $table->foreignId('stored_file_id')->constrained('stored_files')->cascadeOnDelete();
                $table->string('file_type', 60)->default('administrative');
                $table->string('status', 30)->default('uploaded');
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sidang_verifications')) {
            Schema::create('sidang_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('verification_type', 60)->default('admin');
                $table->string('status', 40)->default('verified');
                $table->text('note')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sidang_assignments')) {
            Schema::create('sidang_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('dosen_id')->constrained('dosens')->cascadeOnDelete();
                $table->string('role', 40);
                $table->unsignedBigInteger('recommendation_id')->nullable();
                $table->string('status', 40)->default('assigned');
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('assigned_at')->nullable();
                $table->text('justification')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_registration_id', 'role'], 'sidang_assignment_unique_role');
                $table->unique(['tenant_id', 'sidang_registration_id', 'dosen_id'], 'sidang_assignment_unique_dosen');
            });
        }

        if (! Schema::hasTable('sidang_schedules')) {
            Schema::create('sidang_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->date('tanggal');
                $table->time('jam_mulai');
                $table->time('jam_selesai');
                $table->foreignId('ruangan_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->string('meeting_url')->nullable();
                $table->string('mode', 40)->default('onsite');
                $table->string('status', 40)->default('draft');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('started_at')->nullable();
                $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('conflict_payload')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'tanggal', 'jam_mulai', 'jam_selesai']);
            });
        }

        if (! Schema::hasTable('sidang_schedule_histories')) {
            Schema::create('sidang_schedule_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_schedule_id')->constrained('sidang_schedules')->cascadeOnDelete();
                $table->date('old_date')->nullable();
                $table->time('old_start')->nullable();
                $table->time('old_end')->nullable();
                $table->foreignId('old_room_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->date('new_date')->nullable();
                $table->time('new_start')->nullable();
                $table->time('new_end')->nullable();
                $table->foreignId('new_room_id')->nullable()->constrained('ruangans')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sidang_rubrics')) {
            Schema::create('sidang_rubrics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_type_id')->constrained('sidang_types')->cascadeOnDelete();
                $table->string('code', 80);
                $table->string('name', 160);
                $table->decimal('weight', 5, 2)->default(0);
                $table->decimal('min_score', 5, 2)->default(0);
                $table->decimal('max_score', 5, 2)->default(100);
                $table->unsignedSmallInteger('sequence')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_type_id', 'code'], 'sidang_rubric_unique_code');
            });
        }

        if (! Schema::hasTable('sidang_scores')) {
            Schema::create('sidang_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('examiner_id')->constrained('dosens')->cascadeOnDelete();
                $table->foreignId('sidang_rubric_id')->constrained('sidang_rubrics')->cascadeOnDelete();
                $table->decimal('score', 5, 2)->default(0);
                $table->text('note')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_registration_id', 'examiner_id', 'sidang_rubric_id'], 'sidang_score_unique_rubric');
            });
        }

        if (! Schema::hasTable('sidang_examiner_summaries')) {
            Schema::create('sidang_examiner_summaries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('examiner_id')->constrained('dosens')->cascadeOnDelete();
                $table->decimal('total_score', 5, 2)->default(0);
                $table->string('recommendation', 60)->nullable();
                $table->text('general_note')->nullable();
                $table->timestamp('finalized_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_registration_id', 'examiner_id'], 'sidang_summary_unique_examiner');
            });
        }

        if (! Schema::hasTable('sidang_results')) {
            Schema::create('sidang_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->decimal('final_score', 5, 2)->default(0);
                $table->string('final_grade', 10)->nullable();
                $table->string('decision', 60)->default('ditunda');
                $table->text('decision_note')->nullable();
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('decided_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_registration_id']);
            });
        }

        if (! Schema::hasTable('sidang_revisions')) {
            Schema::create('sidang_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('examiner_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->text('description');
                $table->string('category', 80)->nullable();
                $table->date('deadline')->nullable();
                $table->string('status', 40)->default('open');
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sidang_minutes')) {
            Schema::create('sidang_minutes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sidang_registration_id')->constrained('sidang_registrations')->cascadeOnDelete();
                $table->foreignId('document_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
                $table->timestamp('generated_at')->nullable();
                $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 40)->default('draft');
                $table->timestamps();
                $table->unique(['tenant_id', 'sidang_registration_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sidang_minutes');
        Schema::dropIfExists('sidang_revisions');
        Schema::dropIfExists('sidang_results');
        Schema::dropIfExists('sidang_examiner_summaries');
        Schema::dropIfExists('sidang_scores');
        Schema::dropIfExists('sidang_rubrics');
        Schema::dropIfExists('sidang_schedule_histories');
        Schema::dropIfExists('sidang_schedules');
        Schema::dropIfExists('sidang_assignments');
        Schema::dropIfExists('sidang_verifications');
        Schema::dropIfExists('sidang_files');
        Schema::dropIfExists('sidang_requirement_results');
        Schema::dropIfExists('sidang_registrations');
        Schema::dropIfExists('sidang_requirements');
        Schema::dropIfExists('sidang_types');
    }
};
