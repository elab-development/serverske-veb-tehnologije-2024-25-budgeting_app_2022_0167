<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
     use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    // troškovi koje je korisnik platio
    public function expensesPaid(): HasMany
    {
        return $this->hasMany(Expense::class, 'paid_by');
    }

    // učešća korisnika u podeli (koliko duguje po trošku)
    public function splits(): HasMany
    {
        return $this->hasMany(Split::class);
    }

    // izmirenja koja je poslao / primio
    public function settlementsSent(): HasMany
    {
        return $this->hasMany(Settlement::class, 'from_user_id');
    }
    public function settlementsReceived(): HasMany
    {
        return $this->hasMany(Settlement::class, 'to_user_id');
    }
}
