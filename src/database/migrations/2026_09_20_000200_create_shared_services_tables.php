<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('role')->nullable();
                $table->string('module', 40)->nullable()->index();
                $table->string('action', 80)->index();
                $table->string('resource_type')->nullable()->index();
                $table->string('resource_id')->nullable()->index();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('request_id', 80)->nullable()->index();
                $table->timestamps();
                $table->index(['tenant_id', 'action']);
                $table->index(['tenant_id', 'resource_type', 'resource_id'], 'audit_resource_index');
            });
        }

        if (! Schema::hasTable('sifak_notifications')) {
            Schema::create('sifak_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('channel', 20)->default('in_app');
                $table->string('type', 80)->index();
                $table->string('title');
                $table->text('message');
                $table->string('reference_type')->nullable()->index();
                $table->string('reference_id')->nullable()->index();
                $table->string('status', 20)->default('pending')->index();
                $table->timestamp('queued_at')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->text('failed_reason')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'user_id', 'status']);
            });
        }

        if (! Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('code', 80);
                $table->string('title');
                $table->text('body');
                $table->json('available_channels')->nullable();
                $table->string('status', 20)->default('active');
                $table->timestamps();
                $table->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('stored_files')) {
            Schema::create('stored_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('module', 40)->index();
                $table->string('owner_type')->nullable()->index();
                $table->string('owner_id')->nullable()->index();
                $table->string('original_name');
                $table->string('stored_name');
                $table->string('disk', 40)->default('local');
                $table->string('path');
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->string('checksum', 128)->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->string('status', 20)->default('temp')->index();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['tenant_id', 'module']);
                $table->index(['tenant_id', 'owner_type', 'owner_id'], 'stored_files_owner_index');
            });
        }

        if (! Schema::hasTable('file_versions')) {
            Schema::create('file_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('stored_file_id')->constrained('stored_files')->cascadeOnDelete();
                $table->unsignedInteger('version');
                $table->string('path');
                $table->string('checksum', 128)->nullable();
                $table->unsignedBigInteger('size')->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->unique(['tenant_id', 'stored_file_id', 'version'], 'file_versions_unique');
            });
        }

        if (! Schema::hasTable('workflow_histories')) {
            Schema::create('workflow_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('workflow_type', 40)->index();
                $table->string('resource_type')->nullable();
                $table->string('resource_id');
                $table->string('from_state', 40)->nullable();
                $table->string('to_state', 40);
                $table->string('action', 80);
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'workflow_type', 'resource_id'], 'workflow_resource_index');
            });
        }

        if (! Schema::hasTable('scheduled_task_logs')) {
            Schema::create('scheduled_task_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
                $table->string('task_name', 120)->index();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->string('status', 20)->default('pending')->index();
                $table->text('message')->nullable();
                $table->json('context')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'task_name', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_logs');
        Schema::dropIfExists('workflow_histories');
        Schema::dropIfExists('file_versions');
        Schema::dropIfExists('stored_files');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('sifak_notifications');
        Schema::dropIfExists('audit_logs');
    }
};
