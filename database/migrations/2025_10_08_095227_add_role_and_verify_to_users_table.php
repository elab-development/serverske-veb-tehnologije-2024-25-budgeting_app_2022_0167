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
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                $t->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $t->enum('role', ['admin','user'])->default('user')->after('password');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'role')) $t->dropColumn('role');
            if (Schema::hasColumn('users', 'email_verified_at')) $t->dropColumn('email_verified_at');
        });
    }
};
