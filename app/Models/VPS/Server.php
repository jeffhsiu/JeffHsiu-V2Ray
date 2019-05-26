<?php

namespace App\Models\VPS;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Server extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'server';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    // VPS提供商 Google Cloud
    const PROVIDER_GOOGLE = 1;
    // VPS提供商 Bandwagon
    const PROVIDER_BANDWAGON = 2;

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
    public function getSshPwdAttribute($value)
    {
        return decrypt($value);
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setSshPwdAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['ssh_pwd'] = encrypt($this->ssh_pwd);
        } else {
            $this->attributes['ssh_pwd'] = encrypt($value);
        }
    }
}
