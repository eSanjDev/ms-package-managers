<?php

use Esanj\Manager\Enums\ManagerRoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('esanj_id')->unique()->index();
            $table->string("name")->nullable();
            $table->enum('role', ManagerRoleEnum::toArray())->default(ManagerRoleEnum::Manager);
            $table->string('token');
            $table->boolean('api_access')->default(false);
            $table->boolean('is_active')->default(true);
            $table->longText('extra')->nullable();
            $table->timestamp('last_login')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
