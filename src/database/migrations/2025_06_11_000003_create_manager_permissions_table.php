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
        Schema::create('manager_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('manager_id')->constrained("managers");
            $table->foreignId('permission_id')->constrained("permissions");

            $table->unique(['manager_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_permissions');
    }
};
