<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, CausesActivity , LogsActivity, HasUlids, AuthenticationLoggable, HasRoles;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];
    //protected $primaryKey = 'id';

  /* protected function getDefaultGuardName(): string { return 'sanctum'; }*/
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'id_no',
        'staff_no',
        'phone',
        'second_phone',
        'email',
        'phy_address',
       'description',
        'role_id',
        'organization_id',
        'special_access',
        'password',
        'created_by',
        'updated_by'
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
   

   /* protected $with = ['roles'];*/
    protected $appends = ['full_name'];

    // Define the accessor for full_name
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }


/*    public function getRoleAttribute()
    {
      $yes =  $this->getRoleNames();
      //  $role = $this->roles()->first(); // Get the first role
$role = 'pppppp';
return $yes;
        /*return $role ? [
            'id' => $role->id,
            'name' => $role->name
        ] : null; // Return null if no role is assigned

    }
    */
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('first_name');
        });
    }
    protected static function booted()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

       public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename($this))
            ->dontSubmitEmptyLogs()
            ->logFillable()
            ->logOnlyDirty() ;
    }

    
}
