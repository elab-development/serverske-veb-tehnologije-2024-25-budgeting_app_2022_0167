<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('settlements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $t->decimal('amount', 10, 2);
            $t->timestamp('paid_at');
            $t->string('note')->nullable();
            $t->timestamps();

            $t->index(['from_user_id','to_user_id','paid_at']);
            $t->check('from_user_id <> to_user_id'); // MySQL 8+; ako ne podržavaš, ukloni
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settlements');
    }
};
