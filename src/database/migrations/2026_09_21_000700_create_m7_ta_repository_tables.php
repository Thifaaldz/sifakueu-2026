<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tugas_akhirs')) {
            Schema::create('tugas_akhirs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mahasiswa_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_studi_id')->constrained('program_studis')->cascadeOnDelete();
                $table->foreignId('pembimbing_1_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->foreignId('pembimbing_2_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->text('judul');
                $table->text('judul_en')->nullable();
                $table->text('topik')->nullable();
                $table->json('keywords')->nullable();
                $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademiks')->nullOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 40)->default('draft');
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->unsignedBigInteger('final_document_version_id')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'mahasiswa_id', 'status']);
            });
        }

        if (! Schema::hasTable('ta_sections')) {
            Schema::create('ta_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('code', 60);
                $table->string('name', 150);
                $table->unsignedSmallInteger('sequence')->default(0);
                $table->boolean('required')->default(true);
                $table->string('template_type', 60)->default('content');
                $table->string('status', 30)->default('active');
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('ta_documents')) {
            Schema::create('ta_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tugas_akhir_id')->constrained('tugas_akhirs')->cascadeOnDelete();
                $table->foreignId('ta_section_id')->constrained('ta_sections')->cascadeOnDelete();
                $table->unsignedBigInteger('current_version_id')->nullable();
                $table->string('status', 40)->default('draft');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('dosens')->nullOnDelete();
                $table->timestamps();
                $table->unique(['tenant_id', 'tugas_akhir_id', 'ta_section_id'], 'ta_document_unique_section');
            });
        }

        if (! Schema::hasTable('ta_document_versions')) {
            Schema::create('ta_document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ta_document_id')->constrained('ta_documents')->cascadeOnDelete();
                $table->unsignedInteger('version_number');
                $table->foreignId('stored_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->text('change_summary')->nullable();
                $table->string('status', 40)->default('draft');
                $table->string('checksum')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'ta_document_id', 'version_number'], 'ta_version_unique_number');
            });
        }

        if (! Schema::hasTable('ta_reviews')) {
            Schema::create('ta_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ta_document_version_id')->constrained('ta_document_versions')->cascadeOnDelete();
                $table->foreignId('reviewer_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->string('review_status', 40)->default('pending');
                $table->text('summary')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ta_comments')) {
            Schema::create('ta_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ta_document_version_id')->constrained('ta_document_versions')->cascadeOnDelete();
                $table->foreignId('reviewer_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->text('comment');
                $table->string('page_reference')->nullable();
                $table->string('section_reference')->nullable();
                $table->string('status', 30)->default('open');
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ta_approvals')) {
            Schema::create('ta_approvals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('ta_document_id')->constrained('ta_documents')->cascadeOnDelete();
                $table->foreignId('approver_id')->nullable()->constrained('dosens')->nullOnDelete();
                $table->string('approval_type', 40)->default('section');
                $table->string('status', 40)->default('approved');
                $table->text('note')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ta_progress_logs')) {
            Schema::create('ta_progress_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tugas_akhir_id')->constrained('tugas_akhirs')->cascadeOnDelete();
                $table->string('progress_type', 60);
                $table->string('old_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->decimal('progress_percent', 5, 2)->default(0);
                $table->text('note')->nullable();
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('repository_items')) {
            Schema::create('repository_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tugas_akhir_id')->constrained('tugas_akhirs')->cascadeOnDelete();
                $table->foreignId('final_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
                $table->text('title');
                $table->longText('abstract_id')->nullable();
                $table->longText('abstract_en')->nullable();
                $table->json('keywords')->nullable();
                $table->string('author_name');
                $table->string('nim', 50);
                $table->foreignId('program_studi_id')->constrained('program_studis')->cascadeOnDelete();
                $table->json('supervisor_names')->nullable();
                $table->unsignedSmallInteger('year')->nullable();
                $table->string('access_level', 40)->default('internal');
                $table->string('status', 40)->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'tugas_akhir_id']);
            });
        }

        if (! Schema::hasTable('revision_cycles')) {
            Schema::create('revision_cycles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('tugas_akhir_id')->constrained('tugas_akhirs')->cascadeOnDelete();
                $table->string('source', 40)->default('supervisor');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('deadline')->nullable();
                $table->string('status', 40)->default('open');
                $table->timestamp('closed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_cycles');
        Schema::dropIfExists('repository_items');
        Schema::dropIfExists('ta_progress_logs');
        Schema::dropIfExists('ta_approvals');
        Schema::dropIfExists('ta_comments');
        Schema::dropIfExists('ta_reviews');
        Schema::dropIfExists('ta_document_versions');
        Schema::dropIfExists('ta_documents');
        Schema::dropIfExists('ta_sections');
        Schema::dropIfExists('tugas_akhirs');
    }
};
