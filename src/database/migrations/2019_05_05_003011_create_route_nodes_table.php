<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRouteNodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('route_nodes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable()->default(null);

            // Name of this node.
            $table->string('name', 255);

            // An associative array with the languages as keys and the path-segments to be used for this node as values.
            $table->jsonb('segments')->nullable()->default(null);

            // Should the path-segment of this node be inherited to it's children?
            $table->boolean('inherit_path')->default(true);

            // The namespace, controllers should be registered with.
            $table->string('namespace', 255)->nullable();

            // Array of middlewares, actions of this node should be registered with.
            $table->jsonb('middleware')->nullable()->default(null);

            // Array of custom-data associated with this node.
            $table->jsonb('data')->nullable()->default(null);

            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('route_nodes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('route_nodes');
    }
}
