<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class OrganizationBranch extends Model
{
    use HasFactory, SoftDeletes, HasUlids, LogsActivity;

    protected $table = "organization_branches";
    protected $dates = ['deleted_at'];



    protected $fillable = [
        'code',
        'organization_code',
        'organization_id',
        'name',
        'session_id',
        'kra_pin',
        'email',
        'phone',
        'phone_2',
        'logo_url',
        'location',
        'description'
    ];

    protected $withCount = ['devices'];
   protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name');
        });
        /* make super admin for a given branch*/
        /*    self::created(function ($model) {
                // temporary: get session team_id for restore at end
                $session_team_id = getPermissionsTeamId();
                // set actual new team_id to package instance
                setPermissionsTeamId($model);
                // get the admin user and assign roles/permissions on new team model
               // User::find('your_user_id')->assignRole('super-admin');

                User::where('special_access', 1)->each(function ($user) use ($branch) {
                    $user->assignRole('super-admin', $branch);
                });
                // restore session team_id to package instance using temporary value stored above
                setPermissionsTeamId($session_team_id);
            });*/

        self::created(function ($model) {

            $session_team_id = getPermissionsTeamId();

            setPermissionsTeamId($model);


            User::where('special_access', 1)->each(function ($user) {
                $user->assignRole('super-admin');
            });

            setPermissionsTeamId($session_team_id);
        });
       }


    protected static function booted()
    {
        static::creating(function ($model) {
            $system_login_trail_id = SystemTrailLogin::where('session_id', session()->getId())->value('id');
            if (Auth::check()) {
                $model->system_login_trail_id = $system_login_trail_id;
            }
        });
    }

    public function sessionDetails()
    {
        return $this->belongsTo(SystemTrailLogin::class, 'session_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function roles()
    {
        return $this->hasMany(Role::class, 'team_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_organization_branches')
            ->withPivot('id', 'role_id', 'description', 'organization_id')
            ->withTimestamps();
    }

    public function devices()
    {
        return $this->belongsToMany(DeviceList::class, 'device_branches')
         ->withPivot( 'organization_id')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename($this))
            ->dontSubmitEmptyLogs()
            ->logFillable()
            ->logOnlyDirty();
    }


}


