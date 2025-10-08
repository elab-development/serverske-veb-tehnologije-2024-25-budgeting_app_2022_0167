<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
       
        Schema::table('categories', function (Blueprint $t) {
            if (!Schema::hasColumn('categories', 'name')) {
                $t->string('name');
            }
            // dodaj unique samo ako ne postoji
            $t->unique('name', 'categories_name_unique');
        });

        
        Schema::table('settlements', function (Blueprint $t) {
            // ako kolone nisu unsigned big int, prilagodi; ovde pretpostavimo da već postoje
            if (!Schema::hasColumn('settlements', 'from_user_id')) {
                $t->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            } else {
                // dodaj FK ako nedostaje
                $t->foreign('from_user_id', 'settlements_from_user_fk')
                    ->references('id')->on('users')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('settlements', 'to_user_id')) {
                $t->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            } else {
                $t->foreign('to_user_id', 'settlements_to_user_fk')
                    ->references('id')->on('users')->cascadeOnDelete();
            }

            // kombinovani indeks za brže upite
            $t->index(['from_user_id','to_user_id','paid_at'], 'settlements_from_to_paid_idx');

             
        });
    }

    public function down(): void {
        // uklanjanje ograničenja/indeksa
        Schema::table('settlements', function (Blueprint $t) {
            if (Schema::hasColumn('settlements', 'from_user_id')) {
                // imena moraju da se poklope sa up():
                $t->dropForeign('settlements_from_user_fk');
            }
            if (Schema::hasColumn('settlements', 'to_user_id')) {
                $t->dropForeign('settlements_to_user_fk');
            }
            // drop index
            $t->dropIndex('settlements_from_to_paid_idx');
        });

        Schema::table('categories', function (Blueprint $t) {
            $t->dropUnique('categories_name_unique');
        });
    }
};
