<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DeviceList extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, HasUlids;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $dates = ['deleted_at'];

 //

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'device_code',
        'device_name',
        'device_mac',
        'organization_id',
        'branch_id',
        'description',
        'created_by',
        'updated_by',
    ];

  // protected $withCount = ['branches'];
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('device_name');
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

        static::created(function ($model) {
            // Ensure `updated_by` is not set during creation
            $model->updated_by = null;
            $model->saveQuietly();
        });
    }

    /**
     * Get the user who created the device.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the device.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

  /*  public function branches()
    {
        return $this->belongsToMany(OrganizationBranch::class, 'device_branches')
            ->withPivot('organization_id','deleted_at')
            ->withTimestamps();
    }*/

    /*public function branches()
    {
        return $this->hasManyThrough(
            OrganizationBranch::class,
            DeviceBranch::class,  // Intermediate table
            'device_id',  // Foreign key on UserOrganizationBranch (links to users)
            'id',  // Foreign key on OrganizationBranch (links to itself)
            'id',  // Local key on User model
            'branch_id'  // Local key on UserOrganizationBranch (links to OrganizationBranch)
        );
    }*/

  public function branches()
    {
        return $this->belongsToMany(OrganizationBranch::class, 'device_branches')
            ->withPivot( 'organization_id', 'deleted_at')
            ->withTimestamps();
    }
   /* public function branches()
    {
        return $this->belongsToMany(OrganizationBranch::class, 'device_branches')
            //  ->withPivot('id', 'role_id', 'description', 'organization_id')
            ->withTimestamps();
    }*/
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename($this))
            ->dontSubmitEmptyLogs()
            ->logFillable()
            ->logOnlyDirty() ;
    }
}
