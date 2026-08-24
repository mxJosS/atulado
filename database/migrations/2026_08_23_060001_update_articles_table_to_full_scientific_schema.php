<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'topic_area_id')) {
                $table->foreignId('topic_area_id')->nullable()->after('user_id')->constrained('topic_areas')->nullOnDelete();
            }
            if (!Schema::hasColumn('articles', 'author_credentials')) {
                $table->string('author_credentials', 255)->default('Psicólogo Clínico · Terapeuta DBT')->after('author_name');
            }
            if (!Schema::hasColumn('articles', 'visual_theme')) {
                $table->string('visual_theme', 50)->default('salvia')->after('author_credentials');
            }
            if (!Schema::hasColumn('articles', 'publication_type')) {
                $table->enum('publication_type', ['divulgacion', 'revision', 'caso_estudio', 'guia'])->default('divulgacion')->after('visual_theme');
            }
            if (!Schema::hasColumn('articles', 'target_audience')) {
                $table->enum('target_audience', ['general', 'estudiantes', 'profesionales'])->default('general')->after('publication_type');
            }
            if (!Schema::hasColumn('articles', 'summary')) {
                $table->text('summary')->nullable()->after('target_audience');
            }
            if (!Schema::hasColumn('articles', 'cover_image_path')) {
                $table->string('cover_image_path', 2048)->nullable()->after('content');
            }
            if (!Schema::hasColumn('articles', 'references')) {
                $table->text('references')->nullable()->after('cover_image_path');
            }
            if (!Schema::hasColumn('articles', 'discussion_prompt')) {
                $table->string('discussion_prompt', 500)->nullable()->after('references');
            }
            if (!Schema::hasColumn('articles', 'reading_time_min')) {
                $table->unsignedSmallInteger('reading_time_min')->default(1)->after('discussion_prompt');
            }
            if (!Schema::hasColumn('articles', 'allow_comments')) {
                $table->boolean('allow_comments')->default(true)->after('reading_time_min');
            }
            if (!Schema::hasColumn('articles', 'is_disclaimer_accepted')) {
                $table->boolean('is_disclaimer_accepted')->default(true)->after('allow_comments');
            }
            if (!Schema::hasColumn('articles', 'status')) {
                $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('published')->after('is_disclaimer_accepted');
            }
        });

        // Migrate data from existing columns if available
        if (Schema::hasColumn('articles', 'excerpt') && Schema::hasColumn('articles', 'summary')) {
            DB::statement("UPDATE articles SET summary = excerpt WHERE summary IS NULL OR summary = ''");
        }
        if (Schema::hasColumn('articles', 'author_role') && Schema::hasColumn('articles', 'author_credentials')) {
            DB::statement("UPDATE articles SET author_credentials = author_role WHERE author_credentials IS NULL OR author_credentials = ''");
        }
        if (Schema::hasColumn('articles', 'references_list') && Schema::hasColumn('articles', 'references')) {
            DB::statement("UPDATE articles SET \"references\" = references_list WHERE \"references\" IS NULL");
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_area_id');
            $table->dropColumn([
                'author_credentials',
                'visual_theme',
                'publication_type',
                'target_audience',
                'summary',
                'cover_image_path',
                'references',
                'discussion_prompt',
                'reading_time_min',
                'allow_comments',
                'is_disclaimer_accepted',
                'status',
            ]);
        });
    }
};
