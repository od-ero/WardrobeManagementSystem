<?php

namespace App\Http\Controllers;

use App\Models\DeviceBranch;
use App\Models\DeviceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Activitylog\Facades\LogBatch;

class DeviceListController extends Controller
{
    public function listDevices (){
       $list_devices = DeviceList::withoutTrashed()
           -> where('organization_id', session('organization_id', Auth::user()->organization_id))
     ->withCount('branches')
           ->get();
       return response()->json($list_devices);
    }

    public function listInactiveDevices (){

       $list_devices = DeviceList::onlyTrashed()->where('organization_id', session('organization_id', Auth::user()->organization_id))
           ->withCount('branches')
           ->get();
       return response()->json($list_devices);
    }

    public function store (Request $request){
        $validated_details = $request->all();
        $validated_details['organization_id'] = session('organization_id', Auth::user()->organization_id);
        DB::beginTransaction();
            try {
                LogBatch::startBatch();
                $validator = Validator::make($validated_details, [
                    'device_name' => ['required', 'string','unique:device_lists,device_name', 'max:255'],
                    'device_code' => ['required', 'string','unique:device_lists,device_code', 'max:255'],
                    'device_mac' => ['nullable', 'string', 'unique:device_lists,device_mac'],
                    'organization_id' => ['required'],
                    'branch_id' => ['required'],
                    'description' => ['nullable', 'string', ],
                    'selectedBranches_id' => ['required', 'array', 'min:1'],
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors(),
                    ]);
                }
                LogBatch::startBatch();

          /*   $device = DeviceList::Create( $validated_details);
                $org_branches = $validated_details['selectedBranches_id'];
                $device_id = $device->id;
                $organization_id =  $device->organization_id;
                foreach ($org_branches as $branch) {
                    DeviceBranch::create([
                        'device_list_id' =>  $device_id,
                        'organization_branch_id' => $branch,
                        'organization_id' => $organization_id
                    ]);
                }*/

                $device = DeviceList::create($validated_details);
                $org_branches = $validated_details['selectedBranches_id'];

                $device->branches()->sync($org_branches);

                LogBatch::endBatch();
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => $device['device_name'] . ' - Added Successful',
                    'device_id' => $device['id']

                ],200);
            }
            catch (\Exception $e) {
                DB::rollBack();
                Log::debug($e);
                return response()->json([
                    'status' => 'error',
                    'message' => 'An Error Occured. Contact Adminstrator',
                    'errors' => [
                        'alert_error' => [
                            'An Error Occured. Contact Adminstrator'
                        ]
                    ]
                ], 422);
            }
    }

    public function show($id){
        $device = DeviceList::with(['creator', 'updater', 'branches', 'organization'])->withCount('branches')->withTrashed()->findOrFail($id);
        return response()->json($device);
    }

    public function update (Request $request){
        $validated_details = $request->all();
        $organization_id = session('organization_id', Auth::user()->organization_id);

        $validated_details['organization_id'] = $organization_id;
        $device_id = $validated_details['device_id'];
        DB::beginTransaction();
        try {

            $validator = Validator::make($validated_details, [
                'device_name' => ['required', 'string','unique:device_lists,device_name,'. $device_id, 'max:255'],
                'device_code' => ['required', 'string','unique:device_lists,device_code,'. $device_id, 'max:255'],
                'device_mac' => ['nullable', 'string', 'unique:device_lists,device_mac,'. $device_id],
                'organization_id' => ['required'],
                'branch_id' => ['required'],
                'description' => ['nullable', 'string', ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }
            LogBatch::startBatch();
          /*  $device = DeviceList::withTrashed()->findOrFail($device_id);
            $device->update($validated_details);

            $current_device_branches = $validated_details['selectedBranches_id'];
            $pre_device_branches = DeviceBranch::withTrashed()->where('device_id', $device_id)->get();


            $previous_branch_ids = $pre_device_branches->pluck('branch_id')->toArray();


           DeviceBranch::where('device_id', $device_id)
                ->whereNotIn('branch_id', $current_device_branches)
                ->delete();

            DeviceBranch::onlyTrashed()
                ->where('device_id', $device_id)
                ->whereIn('branch_id', $current_device_branches)
                ->restore();


            $missing_branches = array_diff($current_device_branches, $previous_branch_ids);
            foreach ($missing_branches as $branch_id) {
                DeviceBranch::create([
                    'device_id' => $device_id,
                    'branch_id' => $branch_id,
                    'organization_id' => $organization_id
                ]);
            }*/

            $device = DeviceList::withTrashed()->findOrFail($device_id);
            $device->update($validated_details);

            $current_device_branches = $validated_details['selectedBranches_id'];

// Sync branches (will automatically add new ones and remove old ones)
            $device->branches()->sync($current_device_branches);

            LogBatch::endBatch();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' =>
                    $device['device_name'] . ' - Updated Successful',
                'device_id' => $device['id']

            ],200);
        }
        catch (\Exception $e) {
            Log::debug($e);
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'An Error Occured. Contact Adminstrator',
                'errors' => [
                    'alert_error' => [
                        'An Error Occured. Contact Adminstrator'
                    ]
                ]
            ], 422);
        }
    }
    public function destroy($device_id)
    {
        try {
            $device = DeviceList::withoutTrashed()->find($device_id);

            if (!$device) {

                return response()->json(['status' => 'error', 'message' =>  'Device already deactivated']);
            }
            $device->delete();

            return response()->json(['status' => 'success', 'message' =>  $device->device_name.' deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }

    public function restore($device_id){

        try {
            $device = DeviceList::onlyTrashed()->find($device_id);

            if (!$device) {

                return response()->json(['status' => 'error', 'message' =>  'Device already active']);
            }
            $device->restore();

            return response()->json(['status' => 'success', 'message' =>  $device['device_name'].'  restored successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }
}
