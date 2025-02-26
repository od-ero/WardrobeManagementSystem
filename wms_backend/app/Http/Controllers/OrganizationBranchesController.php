<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrganizationBranchesController extends Controller
{

    public function listActive ($id){
        $data = OrganizationBranch::with(['sessionDetails'])->withoutTrashed()
            ->where('organization_id', $id)
            ->get();

        return response()->json($data);
    }

    public function listInactive ($id){
        $data = OrganizationBranch::with(['sessionDetails'])->onlyTrashed()
            ->where('organization_id', $id)
            ->get();
        return response()->json($data);
    }
    public function listActiveNames (){
        $data = OrganizationBranch::withoutTrashed()
            ->where('organization_id', session('organization_id', Auth::user()->organization_id))
            ->get(['name as label','id as value'])->toArray();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated_details = $request->all();

        if ($request->filled('org_id')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }
        $organization_id = $validated_details['organization_id'];


        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($organization_id) {
                        $exists = OrganizationBranch::withTrashed()
                            ->where('name', $value)
                            ->where('organization_id', $organization_id)
                            ->exists();
                        if ($exists) {
                            $fail('The name has already been taken for this organization.');
                        }
                    }
                ],
                'organization_id' => ['required', 'string'],
                'code' => ['required', 'string', 'unique:organization_branches,code', 'max:255'],
                'phone' => ['required', 'string', 'unique:organization_branches,phone'],
                'phone_2' => ['nullable', 'string', 'unique:organization_branches,phone_2'],
                'email' => ['nullable', 'string', 'unique:organization_branches,email'],
                'kra_pin' => ['nullable', 'string', 'unique:organization_branches,kra_pin'],
                'location' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }


            $org = OrganizationBranch::Create($validated_details);

            return response()->json([
                'status' => 'success',
                'message' => $org['name'] . ' - Added Successfully',
                'id' => $org['id']
            ], 200);
        }
        catch (\Exception $e) {
            Log::debug($e);
            return response()->json([
                'status' => 'error',
                'message' => 'An Error Occurred. Contact Administrator',
                'errors' => [
                    'alert_error' => [
                        'An Error Occurred. Contact Administrator'
                    ]
                ]
            ], 422);
        }
    }

    public function store_ (Request $request){
        $validated_details = $request->all();
        $organization_id = $validated_details['organization_id'];
        $organization_branch_id = $validated_details['organization_branch_id'];
        $success_message = '';
        try {

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255',  function ($organization_id, $value, $fail) {
                    $exists = OrganizationBranch::withTrashed()
                        ->where('name', $value)
                        ->where('organization_id', $organization_id)
                        ->exists();

                    if ($exists) {
                        $fail('The name has already been taken for this organization.');
                    }
                }],
                'organization_id' => ['required', 'string'],
                'code' => ['required', 'string', 'unique:organization_branches,code,'. $organization_branch_id, 'max:255'],
                'phone' => ['required', 'string', 'unique:organization_branches,phone,'. $organization_branch_id,],
                'phone_2' => ['nullable', 'string', 'unique:organization_branches,phone_2,'. $organization_branch_id,],
                'email' => ['nullable', 'string', 'unique:organization_branches,email,'. $organization_branch_id,],
                'kra_pin' => ['nullable', 'string', 'unique:organization_branches,kra_pin,'. $organization_branch_id,],
                'location' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ]);
            if(!$organization_branch_id) {
                $success_message = 'Added Successful';
            }
            else{
                $delete_org = OrganizationBranch::destroy($organization_branch_id);
                $success_message = 'Updated Successful';
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }

            $org = OrganizationBranch::Create($validated_details);

            return response()->json([
                'status' => 'success',
                'message' => $org['name'] . ' - '. $success_message,
                'id' => $org['id']

            ],200);
        }
        catch (\Exception $e) {
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
        $data = OrganizationBranch::with(['sessionDetails', 'organization'])->withTrashed()->findOrFail($id);
        return response()->json($data);
    }

    public function update (Request $request){

        $validated_details = $request->all();

        $organization_id = $validated_details['organization_id'];
        $branch_id = $validated_details['organization_branch_id'];

        try {
         /*   $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($organization_id) {
                        $exists = OrganizationBranch::withTrashed()
                            ->where('name', $value)
                            ->where('organization_id', $organization_id)
                            ->exists();
                        if ($exists) {
                            $fail('The name has already been taken for this organization.');
                        }
                    }
                ],
                'organization_id' => ['required', 'string'],
                'code' => ['required', 'string', 'unique:organization_branches,code', 'max:255'],
                'phone' => ['required', 'string', 'unique:organization_branches,phone'],
                'phone_2' => ['nullable', 'string', 'unique:organization_branches,phone_2'],
                'email' => ['nullable', 'string', 'unique:organization_branches,email'],
                'kra_pin' => ['nullable', 'string', 'unique:organization_branches,kra_pin'],
                'location' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ]);*/
            $rules = [
                'name' => ['required', 'string', 'max:255',
                    function ($attribute, $value, $fail) use ($organization_id, $branch_id) {
                        $exists = OrganizationBranch::withTrashed()
                            ->where('name', $value)
                            ->where('organization_id', $organization_id)
                            ->whereNot('id', $branch_id)
                            ->exists();
                        if ($exists) {
                            $fail('The name has already been taken for this organization.');
                        }
                    }],
                'organization_id' => ['required', 'string'],
                'code' => ['required', 'string'],
                'phone' => ['required', 'string'],
                'phone_2' => ['nullable', 'string'],
                'email' => ['nullable', 'string', 'email'],
                'kra_pin' => ['nullable', 'string'],
                'location' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ];
            $rules['name'][] = Rule::unique('organization_branches', 'name')->ignore($branch_id);
            $rules['code'][] = Rule::unique('organization_branches', 'code')->ignore($branch_id);
            $rules['phone'][] = Rule::unique('organization_branches', 'phone')->ignore($branch_id);
            $rules['phone_2'][] = Rule::unique('organization_branches', 'phone_2')->ignore($branch_id);
            $rules['email'][] = Rule::unique('organization_branches', 'email')->ignore($branch_id);
            $rules['kra_pin'][] = Rule::unique('organization_branches', 'kra_pin')->ignore($branch_id);
            $validator = Validator::make($validated_details, $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }

            $org_branch = OrganizationBranch::withTrashed()->findOrFail($branch_id);
            $org_branch->update($validated_details);

            return response()->json([
                'status' => 'success',
                'message' => $org_branch['name'] . ' -Updated Successfully',
                'id' => $org_branch['id']
            ], 200);
        }
        catch (\Exception $e) {
            Log::debug($e);
            return response()->json([
                'status' => 'error',
                'message' => 'An Error Occurred. Contact Administrator',
                'errors' => [
                    'alert_error' => [
                        'An Error Occurred. Contact Administrator'
                    ]
                ]
            ], 422);
        }
    }
    public function destroy($organization_branch_id)
    {
        try {
            $org = OrganizationBranch::withoutTrashed()->find($organization_branch_id);

            if (!$org) {

                return response()->json(['status' => 'error', 'message' =>  'Organization already deactivated']);
            }
            $org->delete();

            return response()->json(['status' => 'success', 'message' =>  $org->name.' deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }

    public function restore($organization_branch_id){

        try {
            $org = OrganizationBranch::onlyTrashed()->find($organization_branch_id);

            if (!$org) {
                return response()->json(['status' => 'error', 'message' =>  'Organization already active']);
            }
            $org->restore();

            return response()->json(['status' => 'success', 'message' =>  $org['name'].'  restored successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }


}
