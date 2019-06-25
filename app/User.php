<?php

namespace App;

use Backpack\CRUD\CrudTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use CrudTrait;
    use HasRoles;

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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | RELATIONS
     |--------------------------------------------------------------------------
    */
    public function distributor()
    {
        return $this->hasOne('App\Models\Order\Distributor', 'email', 'email');
    }

    public function settlement()
    {
        return $this->hasMany('App\Models\Finance\Settlement');
    }

    public function serverLogs()
    {
        return $this->hasMany('App\Models\VPS\ServerLog');
    }
}
