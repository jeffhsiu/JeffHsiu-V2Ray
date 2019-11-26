<?php

namespace App\Models\VPS;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

/**
 * @property mixed provider
 * @property mixed ssh_pwd
 */
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
    // VPS提供商 HostWinds
    const PROVIDER_HOSTWINDS = 3;
    // VPS提供商 Linode
    const PROVIDER_LINODE = 4;
    // VPS提供商 DigitalOcean
    const PROVIDER_DIGITALOCEAN = 5;

	// 啟用
	const STATUS_ENABLE = 1;
	// 停用
	const STATUS_DISABLE = 2;

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
    public function account()
    {
        return $this->belongsTo('App\Models\VPS\Account');
    }

    public function orders()
    {
        return $this->hasMany('App\Models\Order\Order');
    }

    public function logs()
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
    public function getSshPwdAttribute($value)
    {
        return decrypt($value);
    }

    public static function getProvidersMap()
    {
        return [
            Server::PROVIDER_GOOGLE => 'Google Cloud',
            Server::PROVIDER_BANDWAGON => 'Bandwagon',
            Server::PROVIDER_HOSTWINDS => 'HostWinds',
            Server::PROVIDER_LINODE => 'Linode',
            Server::PROVIDER_DIGITALOCEAN => 'DigitalOcean',
        ];
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

	public function setRemarkAttribute($value)
	{
		$this->attributes['remark'] = empty($value) ? '' : $value;
	}

    public function setWsHostAttribute($value)
    {
        $this->attributes['ws_host'] = empty($value) ? '' : $value;
    }
}
