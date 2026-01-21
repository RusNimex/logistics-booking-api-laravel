<?php

use App\Enums\HoldStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('remaining');
        });

        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slots_id');
            $table->enum('status', HoldStatus::values());
            $table->timestamp('expires_at')->nullable()->index();

            $table->foreign('slots_id')
                ->references('id')
                ->on('slots')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holds');
        Schema::dropIfExists('slots');
    }
};
