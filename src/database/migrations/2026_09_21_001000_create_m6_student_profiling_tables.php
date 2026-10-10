<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpls', function (Blueprint $table) {
            if (! Schema::hasColumn('cpls', 'name')) {
                $table->string('name')->nullable()->after('code');
            }

            if (! Schema::hasColumn('cpls', 'category')) {
                $table->string('category', 80)->nullable()->after('description');
            }

            if (! Schema::hasColumn('cpls', 'active')) {
                $table->boolean('active')->default(true)->after('category');
            }
        });

        Schema::table('plos', function (Blueprint $table) {
            if (! Schema::hasColumn('plos', 'active')) {
                $table->boolean('active')->default(true)->after('description');
            }
        });

        Schema::table('krs_details', function (Blueprint $table) {
            if (! Schema::hasColumn('krs_details', 'final_score')) {
                $table->decimal('final_score', 5, 2)->nullable()->after('validation_note');
            }

            if (! Schema::hasColumn('krs_details', 'final_grade')) {
                $table->string('final_grade', 10)->nullable()->after('final_score');
            }

            if (! Schema::hasColumn('krs_details', 'passed_at')) {
                $table->timestamp('passed_at')->nullable()->after('final_grade');
            }
        });

        Schema::table('skor_cpl_mahasiswas', function (Blueprint $table) {
            if (! Schema::hasColumn('skor_cpl_mahasiswas', 'semester_id')) {
                $table->foreignId('semester_id')->nullable()->after('cpl_id')->constrained('semesters')->nullOnDelete();
            }

            if (! Schema::hasColumn('skor_cpl_mahasiswas', 'source')) {
                $table->string('source', 40)->default('M6')->after('score');
            }

            if (! Schema::hasColumn('skor_cpl_mahasiswas', 'calculated_at')) {
                $table->timestamp('calculated_at')->nullable()->after('source');
            }

            if (! Schema::hasColumn('skor_cpl_mahasiswas', 'payload')) {
                $table->json('payload')->nullable()->after('calculated_at');
            }
        });

        Schema::create('mahasiswa_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->decimal('academic_score', 5, 2)->default(0);
            $table->decimal('competency_score', 5, 2)->default(0);
            $table->string('profile_status', 30)->default('draft');
            $table->json('strengths')->nullable();
            $table->json('gaps')->nullable();
            $table->json('summary_payload')->nullable();
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'mahasiswa_id'], 'mahasiswa_profile_unique');
            $table->index(['tenant_id', 'profile_status']);
        });

        Schema::create('mahasiswa_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('interest_area');
            $table->string('level', 20)->default('medium');
            $table->string('source', 40)->default('self_reported');
            $table->boolean('verified')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'mahasiswa_id', 'interest_area'], 'mahasiswa_interest_unique');
        });

        Schema::create('mahasiswa_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->string('field')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expired_at')->nullable();
            $table->foreignId('file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->boolean('verified')->default(false);
            $table->timestamps();
        });

        Schema::create('mahasiswa_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 80)->default('project');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->json('skills_json')->nullable();
            $table->timestamps();
        });

        Schema::create('mahasiswa_organizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('organization_name');
            $table->string('role')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('mahasiswa_mbkms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('program_type', 80);
            $table->string('institution')->nullable();
            $table->string('role')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('field')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('mahasiswa_plo_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('plo_id')->constrained('plos')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedSmallInteger('rank_order')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'mahasiswa_id', 'plo_id'], 'mahasiswa_plo_score_unique');
        });

        Schema::create('graduate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studis')->nullOnDelete();
            $table->string('code', 60);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('graduate_profile_plo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('graduate_profile_id')->constrained('graduate_profiles')->cascadeOnDelete();
            $table->foreignId('plo_id')->constrained('plos')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(1);
            $table->timestamps();

            $table->unique(['tenant_id', 'graduate_profile_id', 'plo_id'], 'graduate_profile_plo_unique');
        });

        Schema::create('mahasiswa_graduate_profile_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('graduate_profile_id')->constrained('graduate_profiles')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedSmallInteger('rank_order')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'mahasiswa_id', 'graduate_profile_id'], 'mahasiswa_gp_score_unique');
        });

        Schema::create('competency_gaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('competency_type', 40);
            $table->unsignedBigInteger('competency_reference_id');
            $table->decimal('current_score', 5, 2)->default(0);
            $table->decimal('target_score', 5, 2)->default(80);
            $table->decimal('gap_score', 5, 2)->default(0);
            $table->string('severity', 20)->default('low');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'competency_type', 'severity'], 'competency_gap_type_severity_index');
            $table->unique(['tenant_id', 'mahasiswa_id', 'competency_type', 'competency_reference_id'], 'competency_gap_unique');
        });

        Schema::create('student_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('recommendation_type', 40);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->unsignedSmallInteger('rank_order')->nullable();
            $table->text('reason_summary')->nullable();
            $table->string('status', 40)->default('generated');
            $table->json('payload')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'mahasiswa_id', 'recommendation_type'], 'student_recommendation_type_index');
        });

        Schema::create('recommendation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('recommendation_type', 40);
            $table->json('payload_json')->nullable();
            $table->string('model_version', 40)->default('M6-RULE-V1');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_histories');
        Schema::dropIfExists('student_recommendations');
        Schema::dropIfExists('competency_gaps');
        Schema::dropIfExists('mahasiswa_graduate_profile_scores');
        Schema::dropIfExists('graduate_profile_plo');
        Schema::dropIfExists('graduate_profiles');
        Schema::dropIfExists('mahasiswa_plo_scores');
        Schema::dropIfExists('mahasiswa_mbkms');
        Schema::dropIfExists('mahasiswa_organizations');
        Schema::dropIfExists('mahasiswa_portfolios');
        Schema::dropIfExists('mahasiswa_certifications');
        Schema::dropIfExists('mahasiswa_interests');
        Schema::dropIfExists('mahasiswa_profiles');
    }
};
