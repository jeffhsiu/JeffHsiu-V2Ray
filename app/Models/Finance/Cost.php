<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use Illuminate\Support\Str;

class Cost extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */
    protected $table = 'cost';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    // 未結算
    const STATUS_UNSETTLED = 1;
    // 已結算
    const STATUS_SETTLED = 2;

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public static function boot()
    {
        parent::boot();
        static::deleting(function($obj) {
            $disk = config('backpack.base.root_disk_name');
            $path = ($disk == 'root') ? 'public/'.$obj->image : $obj->image;
            \Storage::disk($disk)->delete($path);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function settlement()
    {
        return $this->belongsTo('App\Models\Finance\Settlement');
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
    public function getDateNotimeAttribute()
    {
        return explode(' ', $this->date)[0];
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function setImageAttribute($value)
    {
        $attribute_name = 'image';
        $disk = config('backpack.base.root_disk_name'); // or use your own disk, defined in config/filesystems.php
        $destination_path = 'public/uploads/cost/images'; // path relative to the disk above
        $image_path = ($disk == 'root') ? 'public/'.$this->{$attribute_name} : $this->{$attribute_name};

        // if the image was erased
        if (empty($value) || $value == null) {
            // delete the image from disk
            \Storage::disk($disk)->delete($image_path);

            // set null in the database column
            $this->attributes[$attribute_name] = '';
        }

        // if a base64 was sent, store it in the db
        if (Str::startsWith($value, 'data:image')) {
            // delete the image from disk
            \Storage::disk($disk)->delete($image_path);

            // 0. Make the image
            $image = \Image::make($value)->encode('jpg', 90);
            // 1. Generate a filename.
            $filename = md5($value.time()).'.jpg';
            // 2. Store the image on disk.
            \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());
            // 3. Save the public path to the database
            // but first, remove "public/" from the path, since we're pointing to it from the root folder
            // that way, what gets saved in the database is the user-accesible URL
            $public_destination_path = Str::replaceFirst('public/', '', $destination_path);
            $this->attributes[$attribute_name] = $public_destination_path.'/'.$filename;
        }
    }

    public function setDescriptAttribute($value)
    {
        $this->attributes['descript'] = empty($value) ? '' : $value;
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = empty($value) ? 0 : $value;
    }
}
