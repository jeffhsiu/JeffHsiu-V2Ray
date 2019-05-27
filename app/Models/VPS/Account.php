<?php

namespace App\Models\VPS;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Account extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'account';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function servers()
    {
        return $this->hasMany('App\Models\VPS\Server');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */
    public function getPasswordAttribute($value)
    {
        return decrypt($value);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setPasswordAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['password'] = encrypt($this->password);
        } else {
            $this->attributes['password'] = encrypt($value);
        }
    }
}
