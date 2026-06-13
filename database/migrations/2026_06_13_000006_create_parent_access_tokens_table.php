<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // parent_access_tokens — 家長 portal 無登入存取 token（可撤銷後重發）
        Schema::create('parent_access_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('profiles')->nullOnDelete();
            $table->timestamps();

            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_access_tokens');
    }
};
