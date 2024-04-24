<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('filament-page-manager.table_name'), function (Blueprint $table) {
            $table->id();

            NestedSet::columns($table);

            $table->string('name')->index();
            $table->string('slug')->index();
            $table->string('template');

            $table->json('content')->nullable();
            $table->json('meta')->nullable();

            $table->timestamp('activate_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('filament-page-manager.table_name'));
    }
};
