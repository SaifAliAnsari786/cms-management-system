<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {

            $table->string('cover_image')->nullable()->after('content');

            $table->timestamp('published_at')->nullable()->after('status');

            $table->foreignId('updated_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {

            $table->dropForeign(['updated_by']);

            $table->dropColumn([
                'cover_image',
                'published_at',
                'updated_by',
            ]);

            $table->dropSoftDeletes();

        });
    }
};