<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DeviceBranch extends Model
{
    use HasFactory, SoftDeletes, HasUlids, LogsActivity;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'device_list_id',
        'organization_branch_id',
        'organization_id'
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(class_basename($this))
            ->dontSubmitEmptyLogs()
            ->logFillable()
            ->logOnlyDirty() ;
    }
    public function branch()
    {
        return $this->belongsTo(OrganizationBranch::class, 'organization_branch_id');
    }
}
