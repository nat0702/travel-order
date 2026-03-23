<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;


//#[Fillable(['name', 'email', 'password', 'role'])] //protected
//#[Hidden(['password', 'remember_token'])] //protected
/**
 * @property \Illuminate\Database\Eloquent\Collection $notifications
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany notifications()
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function travelOrders(): HasMany
    {
        return $this->hasMany(TravelOrder::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return []; //depois posso inserir dados extras no token 
    }
}
