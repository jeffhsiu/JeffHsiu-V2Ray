<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Distributor extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'distributor';
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
    public function orders()
    {
        return $this->hasMany('App\Models\Order\Order');
    }

    public function customers()
    {
        return $this->hasMany('App\Models\Order\Customer');
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

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setWechatIdAttribute($value)
    {
        $this->attributes['wechat_id'] = empty($value) ? '' : $value;
    }

    public function setAlipayIdAttribute($value)
    {
        $this->attributes['alipay_id'] = empty($value) ? '' : $value;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = empty($value) ? '' : $value;
    }

    public function setMobileAttribute($value)
    {
        $this->attributes['mobile'] = empty($value) ? '' : $value;
    }
}
