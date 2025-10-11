<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /** Helpers */
    private function indexExists(string $table, string $index): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = ? AND index_name = ?
            LIMIT 1
        ", [$db, $table, $index]);

        return (bool) $row;
    }

    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne("
            SELECT 1
            FROM information_schema.table_constraints
            WHERE constraint_schema = ? AND table_name = ? AND constraint_name = ? AND constraint_type = 'FOREIGN KEY'
            LIMIT 1
        ", [$db, $table, $fkName]);

        return (bool) $row;
    }

    public function up(): void
    {
        /** 1) categories: unique index nad name samo ako ne postoji */
        Schema::table('categories', function (Blueprint $t) {
            if (!Schema::hasColumn('categories', 'name')) {
                $t->string('name');
            }
        });

        if (!$this->indexExists('categories', 'categories_name_unique')) {
            Schema::table('categories', function (Blueprint $t) {
                $t->unique('name', 'categories_name_unique');
            });
        }

        /** 2) settlements: dodaj FK + kombinovani indeks samo ako ne postoje */
        Schema::table('settlements', function (Blueprint $t) {
            if (!Schema::hasColumn('settlements', 'from_user_id')) {
                $t->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('settlements', 'to_user_id')) {
                $t->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            }
        });

        if (!$this->foreignKeyExists('settlements', 'settlements_from_user_fk')) {
            Schema::table('settlements', function (Blueprint $t) {
                // ako kolona postoji ali nije vezana FK-om sa željenim imenom, dodaj je
                $t->foreign('from_user_id', 'settlements_from_user_fk')
                  ->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (!$this->foreignKeyExists('settlements', 'settlements_to_user_fk')) {
            Schema::table('settlements', function (Blueprint $t) {
                $t->foreign('to_user_id', 'settlements_to_user_fk')
                  ->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (!$this->indexExists('settlements', 'settlements_from_to_paid_idx')) {
            Schema::table('settlements', function (Blueprint $t) {
                $t->index(['from_user_id','to_user_id','paid_at'], 'settlements_from_to_paid_idx');
            });
        }
    }

    public function down(): void
    {
        // settlements: skini FK i indeks samo ako postoje
        if ($this->foreignKeyExists('settlements', 'settlements_from_user_fk')) {
            Schema::table('settlements', function (Blueprint $t) {
                $t->dropForeign('settlements_from_user_fk');
            });
        }
        if ($this->foreignKeyExists('settlements', 'settlements_to_user_fk')) {
            Schema::table('settlements', function (Blueprint $t) {
                $t->dropForeign('settlements_to_user_fk');
            });
        }
        if ($this->indexExists('settlements', 'settlements_from_to_paid_idx')) {
            Schema::table('settlements', function (Blueprint $t) {
                $t->dropIndex('settlements_from_to_paid_idx');
            });
        }

        // categories: skini unique samo ako postoji
        if ($this->indexExists('categories', 'categories_name_unique')) {
            Schema::table('categories', function (Blueprint $t) {
                $t->dropUnique('categories_name_unique');
            });
        }
    }
};
