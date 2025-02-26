<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class UserOrganizationBranch extends Model
{
    use HasFactory, SoftDeletes, HasUlids, LogsActivity;

    //protected $primaryKey = 'ulid';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'user_id',
        'organization_branch_id',
        'role_id',
        'organization_id',
        'description'
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
