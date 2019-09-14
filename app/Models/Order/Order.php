<?php

namespace App\Models\Order;

use App\Models\VPS\Server;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Order extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'order';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    // 可用狀態
    const STATUS_ENABLE = 1;
    // 不可用狀態
    const STATUS_DISABLE = 2;
    // 過期狀態
    const STATUS_EXPIRED = 3;

    // 付費
    const TYPE_PAID = 1;
    // 試用
    const TYPE_TRIAL = 2;

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function getCustomerLink() {
        $customer = Customer::find($this->customer_id);
        $url = backpack_url('order/customer/'.$customer->id);
        return '<a href="'.$url.'">'.$customer->name.'</a>';
    }

    public function getDistributorLink() {
        $distributor = Distributor::find($this->distributor_id);
        $url = backpack_url('order/distributor/'.$distributor->id);
        return '<a href="'.$url.'">'.$distributor->name.'</a>';
    }

    public function getServerIpLink() {
        $server = Server::find($this->server_id);
        $url = backpack_url('vps/server/stats/'.$server->id);
        return '<a href="'.$url.'">'.$server->ip.'</a>';
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function customer()
    {
        return $this->belongsTo('App\Models\Order\Customer');
    }

    public function distributor()
    {
        return $this->belongsTo('App\Models\Order\Distributor');
    }

    public function server()
    {
        return $this->belongsTo('App\Models\VPS\Server');
    }

    public function settlement()
    {
        return $this->belongsTo('App\Models\Finance\Settlement');
    }

    public function serverLogs()
    {
        return $this->hasMany('App\Models\VPS\ServerLog');
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
	public function getStatusStringAttribute()
	{
		switch ($this->status) {
			case self::STATUS_ENABLE:
				return 'Enable ';
			case self::STATUS_DISABLE:
				return 'Disable';
			case self::STATUS_EXPIRED:
				return 'Expired';
			default:
				return '';
		}
	}

    public function getTypeStringAttribute()
    {
        switch ($this->type) {
            case self::TYPE_PAID:
                return 'Paid ';
            case self::TYPE_TRIAL:
                return 'Trial';
            default:
                return '';
        }
    }

    public function getStartDateNotimeAttribute()
    {
        return explode(' ', $this->start_date)[0];
    }

    public function getEndDateNotimeAttribute()
    {
        return explode(' ', $this->end_date)[0];
    }

    public function getCustomerNameAttribute()
    {
        return $this->customer->name;
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setDistributorIdAttribute()
    {
        $customer = Customer::find($this->attributes['customer_id']);
        $this->attributes['distributor_id'] = $customer->distributor_id;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = empty($value) ? 0 : $value;
    }

    public function setCommissionAttribute($value)
    {
        $this->attributes['commission'] = empty($value) ? 0 : $value;
    }

    public function setProfitAttribute($value)
    {
        $this->attributes['profit'] = empty($value) ? 0 : $value;
    }

    public function setRemarkAttribute($value)
    {
        $this->attributes['remark'] = empty($value) ? '' : $value;
    }
}
