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
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('paid_by')->constrained('users')->cascadeOnDelete();
            $t->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $t->timestamp('paid_at');
            $t->decimal('amount', 10, 2);
            $t->string('note')->nullable();
            $t->timestamps();

            $t->index(['paid_by', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
