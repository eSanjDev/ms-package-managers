<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manager_metas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('manager_id')->constrained("managers");
            $table->string('key')->index();
            $table->longText('value')->nullable();

            $table->unique(['manager_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_metas');
    }
};
