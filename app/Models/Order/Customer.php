<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Customer extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'customer';
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
    public function getDistributorLink() {
        $distributor = Distributor::find($this->distributor_id);
        $url = backpack_url('order/distributor/'.$distributor->id);
        return '<a href="'.$url.'">'.$distributor->name.'</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function orders()
    {
        return $this->hasMany('App\Models\Order\Order');
    }

    public function distributor()
    {
        return $this->belongsTo('App\Models\Order\Distributor');
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

    public function setFacebookIdAttribute($value)
    {
        $this->attributes['facebook_id'] = empty($value) ? '' : $value;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = empty($value) ? '' : $value;
    }

    public function setMobileAttribute($value)
    {
        $this->attributes['mobile'] = empty($value) ? '' : $value;
    }

    public function setRemarkAttribute($value)
    {
        $this->attributes['remark'] = empty($value) ? '' : $value;
    }
}
