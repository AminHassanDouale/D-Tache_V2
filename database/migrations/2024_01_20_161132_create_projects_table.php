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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('status_id');
            $table->integer('priority');
            $table->boolean('privacy')->default(false);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->text('remark')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('department_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
