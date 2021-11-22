<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_reminders';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;
    
    protected $fillable = [
        'type', 'email', 'token'
    ];


    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }
}
