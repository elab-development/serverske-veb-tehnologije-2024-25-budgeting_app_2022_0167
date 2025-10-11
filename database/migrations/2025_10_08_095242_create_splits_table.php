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
         Schema::create('splits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('expense_id')->constrained('expenses')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // ko duguje
            $t->decimal('amount', 10, 2);                                     // koliko duguje
            $t->timestamp('settled_at')->nullable();                           // kad je izmireno
            $t->timestamps();

            $t->unique(['expense_id','user_id']);
            $t->index(['user_id','settled_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('splits');
    }
};
