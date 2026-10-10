<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('domain', 60);
            $table->text('description')->nullable();
            $table->string('source_module', 30)->nullable();
            $table->string('metric_key', 100);
            $table->string('operator', 30)->default('>=');
            $table->decimal('threshold_value', 10, 2)->nullable();
            $table->decimal('warning_value', 10, 2)->nullable();
            $table->string('severity', 20)->default('medium');
            $table->unsignedSmallInteger('priority')->default(50);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'domain', 'active']);
        });

        Schema::create('monitoring_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->string('overall_status', 30)->default('green');
            $table->unsignedSmallInteger('risk_score')->default(0);
            $table->unsignedSmallInteger('total_green')->default(0);
            $table->unsignedSmallInteger('total_yellow')->default(0);
            $table->unsignedSmallInteger('total_red')->default(0);
            $table->json('summary_payload')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'overall_status']);
            $table->index(['mahasiswa_id', 'evaluated_at']);
        });

        Schema::create('monitoring_indicator_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('snapshot_id')->constrained('monitoring_snapshots')->cascadeOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('monitoring_rules')->nullOnDelete();
            $table->string('indicator', 100);
            $table->decimal('metric_value', 10, 2)->nullable();
            $table->string('status', 30)->default('green');
            $table->decimal('threshold_value', 10, 2)->nullable();
            $table->text('explanation')->nullable();
            $table->string('source_reference_type')->nullable();
            $table->unsignedBigInteger('source_reference_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['source_reference_type', 'source_reference_id'], 'mir_source_reference_index');
        });

        Schema::table('alerts', function (Blueprint $table) {
            if (! Schema::hasColumn('alerts', 'monitoring_snapshot_id')) {
                $table->foreignId('monitoring_snapshot_id')->nullable()->after('mahasiswa_id')->constrained('monitoring_snapshots')->nullOnDelete();
            }

            if (! Schema::hasColumn('alerts', 'rule_id')) {
                $table->foreignId('rule_id')->nullable()->after('monitoring_snapshot_id')->constrained('monitoring_rules')->nullOnDelete();
            }

            if (! Schema::hasColumn('alerts', 'indicator_result_id')) {
                $table->foreignId('indicator_result_id')->nullable()->after('rule_id')->constrained('monitoring_indicator_results')->nullOnDelete();
            }

            if (! Schema::hasColumn('alerts', 'alert_type')) {
                $table->string('alert_type', 100)->nullable()->after('type');
            }

            if (! Schema::hasColumn('alerts', 'risk_status')) {
                $table->string('risk_status', 30)->nullable()->after('severity');
            }

            if (! Schema::hasColumn('alerts', 'acknowledged_at')) {
                $table->timestamp('acknowledged_at')->nullable()->after('assigned_to');
            }

            if (! Schema::hasColumn('alerts', 'acknowledged_by')) {
                $table->foreignId('acknowledged_by')->nullable()->after('acknowledged_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('alerts', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('acknowledged_by');
            }

            if (! Schema::hasColumn('alerts', 'resolved_by')) {
                $table->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('alerts', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('resolved_by');
            }

            if (! Schema::hasColumn('alerts', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('alerts', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('closed_by');
            }
        });

        Schema::create('alert_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alert_id')->constrained('alerts')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 80)->default('follow_up');
            $table->text('note');
            $table->date('next_action_date')->nullable();
            $table->string('status', 30)->default('open');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('alert_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alert_id')->constrained('alerts')->cascadeOnDelete();
            $table->string('from_role', 80)->nullable();
            $table->string('to_role', 80);
            $table->text('reason');
            $table->foreignId('escalated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'to_role']);
        });

        Schema::create('monitoring_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('snapshot_id')->nullable()->constrained('monitoring_snapshots')->nullOnDelete();
            $table->string('original_status', 30);
            $table->string('override_status', 30);
            $table->text('reason');
            $table->date('valid_until')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'override_status']);
            $table->index(['mahasiswa_id', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_overrides');
        Schema::dropIfExists('alert_escalations');
        Schema::dropIfExists('alert_followups');

        Schema::table('alerts', function (Blueprint $table) {
            foreach ([
                'monitoring_snapshot_id',
                'rule_id',
                'indicator_result_id',
                'acknowledged_by',
                'resolved_by',
                'closed_by',
            ] as $column) {
                if (Schema::hasColumn('alerts', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            foreach ([
                'alert_type',
                'risk_status',
                'acknowledged_at',
                'resolved_at',
                'closed_at',
                'due_at',
            ] as $column) {
                if (Schema::hasColumn('alerts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('monitoring_indicator_results');
        Schema::dropIfExists('monitoring_snapshots');
        Schema::dropIfExists('monitoring_rules');
    }
};
