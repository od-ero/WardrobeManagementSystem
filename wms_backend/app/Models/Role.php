<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Models\Role as SpatieRole;
//use Illuminate\Database\Eloquent\Model;

class Role extends SpatieRole
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $primaryKey = 'id';

   /* protected $fillable = [
        'description'
    ];*/

    protected $appends = ['grouped_permissions'];

    public function getGroupedPermissionsAttribute()
    {
        return $this->permissions->groupBy('module');
    }
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('name');
        });
    }

    public function branch()
    {
        return $this->belongsTo(OrganizationBranch::class, 'team_id');
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
