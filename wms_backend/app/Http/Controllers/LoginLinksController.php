<?php

namespace App\Http\Controllers;

use App\Models\DeviceList;
use App\Models\OrganizationBranch;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LoginLinksController extends Controller
{
    //
    public function listBranchLoginLinks($branch_id){
      $branch = OrganizationBranch::find($branch_id);
      $branch_code = $branch->code;
        $devices = $branch->devices;

        $frontend_url = config('app.frontend_url');
        $links = $devices->map(function ($device) use ($branch_code, $frontend_url) {

            return [
                'name' => $device->device_name,
                'url' => $frontend_url.'/login/'.$branch_code.'/'. $device->device_code,

            ];
        });
return response()->json(['module'=>['code'=>$branch_code, 'name'=>$branch->name],'urls'=>$links]);
    }

    public function listDeviceLoginLinks($device_id){
      //$branch = OrganizationBranch::find($branch_id);
      $device = DeviceList::find($device_id);
      $device_code =  $device->device_code;

        $branches = $device->branches;
        $frontend_url = config('app.frontend_url');
        $links = $branches->map(function ($branch) use ($device_code, $frontend_url) {

            return [
                'name' => $branch->name,
                'url' => $frontend_url.'/login/'.$branch->code.'/'. $device_code,

            ];
        });
return response()->json(['module'=>['code'=>$device_code, 'name'=>$device->device_name],'urls'=>$links]);
    }
}
