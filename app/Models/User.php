<?php

namespace App\Models;

//use Hyn\Tenancy\Traits\UsesTenantConnection; //Importando clase de Tenancy (UsesTenantConnection)
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable; //Utilizando la Clase modelo

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function company()
    {
        return $this->hasOne(Company::class);
    }
}