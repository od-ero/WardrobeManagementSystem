<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Permission\Models\Permission as SpatiePermission;



class Permission extends SpatiePermission
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'id';
    /*protected $fillable = [
        'module',
        'display_name'
    ];*/
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
           // $builder->orderBy('module');
            $builder->orderBy('display_name');
        });
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
