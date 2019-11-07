<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouteActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route_actions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('node_id');

            // Name of this action.
            $table->enum('name', [
                'index',
                'create',
                'store',
                'show',
                'edit',
                'update',
                'destroy',
                'get',
                'post'
            ]);

            // Type of this action.
            $table->enum('type', [
                'uses',
                'view',
                'closure',
                'redirect'
            ]);

            // Value of this action.
            $table->text('value');

            // Array of middleware, this action should be registered with.
            $table->jsonb('middleware')->nullable()->default(null);

            $table->timestamps();

            $table->foreign('node_id')->references('id')->on('route_nodes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('route_actions');
    }
}
