<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Organization extends Model
{    use HasFactory, SoftDeletes,HasUlids;
    protected $dates = ['deleted_at'];
    protected $withCount = ['branches', 'devices'];

    protected $fillable = [
        'code',
        'name',
        'kra_pin',
        'email',
        'phone',
        'phone_2',
        'logo_url',
        'location',
        'system_login_trail_id',
        'description'

    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }
    protected static function booted()
    {
        static::creating(function ($model) {
          $system_login_trail_id =  SystemTrailLogin::where('session_id',session()->getId())->value('id');
            if (Auth::check()) {
                $model->system_login_trail_id =  $system_login_trail_id;
            }
        });

    }

    public function sessionDetails()
    {
        return $this->belongsTo(SystemTrailLogin::class, 'system_login_trail_id');
    }
    public function branches()
    {
        return $this->hasMany(OrganizationBranch::class, 'organization_id');
    }
        public function devices()
    {

        return $this->hasMany(DeviceList::class, 'organization_id');

    }

}
