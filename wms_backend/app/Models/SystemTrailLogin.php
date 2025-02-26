<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemTrailLogin extends Model
{
    use HasFactory, SoftDeletes,HasUlids;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'device_id',
        'user_id',
        'session_id',
        'broswer_details',
        'ip_address',
        'branch_id',
        'user_agent',
    ];
    public function device()
    {
        return $this->belongsTo(DeviceList::class, 'device_id');
    }
    public function branch()
    {
        return $this->belongsTo(OrganizationBranch::class, 'branch_id');
    }
    /**
     * Get the user who last updated the device.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    /**
     * Get the user who last updated the device.
     */

}
