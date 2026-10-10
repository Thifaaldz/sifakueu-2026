<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('approval_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approval_flow_id')->constrained('approval_flows')->cascadeOnDelete();
            $table->unsignedSmallInteger('step_order');
            $table->string('role_code', 80);
            $table->string('approval_type', 30)->default('APPROVAL');
            $table->boolean('required')->default(true);
            $table->boolean('can_reject')->default(true);
            $table->boolean('can_request_revision')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'approval_flow_id', 'step_order'], 'approval_flow_step_order_unique');
        });

        Schema::table('jenis_surats', function (Blueprint $table) {
            if (! Schema::hasColumn('jenis_surats', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('jenis_surats', 'requester_type')) {
                $table->string('requester_type', 30)->default('MULTI')->after('description');
            }

            if (! Schema::hasColumn('jenis_surats', 'requires_attachment')) {
                $table->boolean('requires_attachment')->default(false)->after('requester_type');
            }

            if (! Schema::hasColumn('jenis_surats', 'requires_number')) {
                $table->boolean('requires_number')->default(true)->after('requires_attachment');
            }

            if (! Schema::hasColumn('jenis_surats', 'number_pattern')) {
                $table->string('number_pattern')->default('{sequence}/{kode_surat}/{kode_fakultas}/{bulan_romawi}/{tahun}')->after('requires_number');
            }

            if (! Schema::hasColumn('jenis_surats', 'approval_flow_id')) {
                $table->foreignId('approval_flow_id')->nullable()->after('number_pattern')->constrained('approval_flows')->nullOnDelete();
            }
        });

        Schema::table('surats', function (Blueprint $table) {
            if (! Schema::hasColumn('surats', 'requester_type')) {
                $table->string('requester_type', 30)->default('MULTI')->after('requester_id');
            }

            if (! Schema::hasColumn('surats', 'requester_reference_id')) {
                $table->unsignedBigInteger('requester_reference_id')->nullable()->after('requester_type');
            }

            if (! Schema::hasColumn('surats', 'program_studi_id')) {
                $table->foreignId('program_studi_id')->nullable()->after('requester_reference_id')->constrained('program_studis')->nullOnDelete();
            }

            if (! Schema::hasColumn('surats', 'request_number')) {
                $table->string('request_number', 80)->nullable()->after('program_studi_id');
            }

            if (! Schema::hasColumn('surats', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('generated_file_path');
            }

            if (! Schema::hasColumn('surats', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('submitted_at');
            }

            if (! Schema::hasColumn('surats', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('verified_at');
            }

            if (! Schema::hasColumn('surats', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('surats', 'distributed_at')) {
                $table->timestamp('distributed_at')->nullable()->after('completed_at');
            }

            if (! Schema::hasColumn('surats', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('distributed_at');
            }

            if (! Schema::hasColumn('surats', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('archived_at');
            }

            if (! Schema::hasColumn('surats', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }

            if (! Schema::hasColumn('surats', 'qr_public_token')) {
                $table->string('qr_public_token', 80)->nullable()->unique()->after('rejection_reason');
            }

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'request_number']);
        });

        Schema::create('letter_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->cascadeOnDelete();
            $table->string('field_key', 80);
            $table->string('label');
            $table->string('field_type', 30)->default('TEXT');
            $table->boolean('required')->default(false);
            $table->string('validation_rule')->nullable();
            $table->json('options_json')->nullable();
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'jenis_surat_id', 'field_key'], 'letter_field_key_unique');
        });

        Schema::create('letter_request_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('field_id')->nullable()->constrained('letter_form_fields')->nullOnDelete();
            $table->text('value_text')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'surat_id', 'field_id'], 'letter_request_value_unique');
        });

        Schema::create('letter_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('stored_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('attachment_type', 60)->default('SUPPORTING');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('letter_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('PENDING');
            $table->text('note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::table('surat_approvals', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_approvals', 'approval_step_id')) {
                $table->foreignId('approval_step_id')->nullable()->after('surat_id')->constrained('approval_flow_steps')->nullOnDelete();
            }

            if (! Schema::hasColumn('surat_approvals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('acted_at');
            }

            if (! Schema::hasColumn('surat_approvals', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });

        Schema::create('letter_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->nullable()->constrained('jenis_surats')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month')->default(0);
            $table->unsignedInteger('current_sequence')->default(0);
            $table->string('reset_policy', 20)->default('YEARLY');
            $table->timestamps();

            $table->unique(['tenant_id', 'jenis_surat_id', 'year', 'month', 'reset_policy'], 'letter_sequence_unique');
        });

        Schema::create('letter_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->restrictOnDelete();
            $table->unsignedInteger('sequence_number');
            $table->string('formatted_number');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'surat_id']);
            $table->unique(['tenant_id', 'formatted_number']);
        });

        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jenis_surat_id')->nullable()->constrained('jenis_surats')->nullOnDelete();
            $table->string('code', 80);
            $table->string('name');
            $table->string('template_format', 20)->default('HTML');
            $table->longText('content')->nullable();
            $table->foreignId('file_template_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('version', 30)->default('1.0');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code', 'version']);
        });

        Schema::create('generated_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('surat_id')->constrained('surats')->cascadeOnDelete();
            $table->foreignId('letter_number_id')->nullable()->constrained('letter_numbers')->nullOnDelete();
            $table->foreignId('letter_template_id')->nullable()->constrained('letter_templates')->nullOnDelete();
            $table->string('template_version', 30)->nullable();
            $table->foreignId('docx_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->foreignId('pdf_file_id')->nullable()->constrained('stored_files')->nullOnDelete();
            $table->string('html_path')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'generated_at']);
        });

        Schema::create('letter_verification_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_letter_id')->constrained('generated_letters')->cascadeOnDelete();
            $table->string('public_token', 100);
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'public_token']);
        });

        Schema::create('letter_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_letter_id')->constrained('generated_letters')->cascadeOnDelete();
            $table->string('channel', 30)->default('DOWNLOAD');
            $table->string('recipient')->nullable();
            $table->string('status', 30)->default('PENDING');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('letter_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_letter_id')->constrained('generated_letters')->cascadeOnDelete();
            $table->string('archive_code', 100);
            $table->string('classification', 40)->default('PERMANENT');
            $table->date('retention_until')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'archive_code']);
        });
    }

    public function down(): void
    {
        Schema::table('surat_approvals', function (Blueprint $table) {
            if (Schema::hasColumn('surat_approvals', 'approval_step_id')) {
                $table->dropConstrainedForeignId('approval_step_id');
            }

            if (Schema::hasColumn('surat_approvals', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('surat_approvals', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
        });

        Schema::table('jenis_surats', function (Blueprint $table) {
            if (Schema::hasColumn('jenis_surats', 'approval_flow_id')) {
                $table->dropConstrainedForeignId('approval_flow_id');
            }

            foreach (['description', 'requester_type', 'requires_attachment', 'requires_number', 'number_pattern'] as $column) {
                if (Schema::hasColumn('jenis_surats', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('letter_archives');
        Schema::dropIfExists('letter_distributions');
        Schema::dropIfExists('letter_verification_tokens');
        Schema::dropIfExists('generated_letters');
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('letter_numbers');
        Schema::dropIfExists('letter_number_sequences');
        Schema::dropIfExists('letter_verifications');
        Schema::dropIfExists('letter_attachments');
        Schema::dropIfExists('letter_request_values');
        Schema::dropIfExists('letter_form_fields');
        Schema::dropIfExists('approval_flow_steps');
        Schema::dropIfExists('approval_flows');
    }
};
