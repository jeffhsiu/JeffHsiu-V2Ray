<?php

namespace App\Models\VPS;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class ServerLog extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'server_log';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    // System User ID
    const USER_ID_SYSTEM = 0;

    // Docker 啟動
    const ACTION_DOCKER_START = 1;
    // Docker 關閉
    const ACTION_DOCKER_STOP = 2;
    // Docker Redo
    const ACTION_DOCKER_REDO = 3;
    // Docerk 重啟
    const ACTION_DOCKER_RESTART = 4;
    // Server 啟動
    const ACTION_SERVER_START = 5;
    // Server 關閉
    const ACTION_SERVER_STOP = 6;
    // Server 重啟
    const ACTION_SERVER_RESTART = 7;

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
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function server()
    {
        return $this->belongsTo('App\Models\VPS\Server');
    }

    public function order()
    {
        return $this->belongsTo('App\Models\Order\Order');
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
}
