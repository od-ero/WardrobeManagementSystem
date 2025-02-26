<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions'; // Specify the table name
    public $incrementing = false; // The `id` column in sessions is not auto-incrementing
    protected $keyType = 'string'; // The primary key is a string (session ID)
    public $timestamps = false;

    protected $fillable = [
        'id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity',
    ];
}
