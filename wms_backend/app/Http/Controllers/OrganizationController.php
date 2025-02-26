<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class OrganizationController extends Controller
{
    public function listActive (){
        $data = Organization::with(['sessionDetails'])
            ->withCount(['branches','devices'])
            ->withoutTrashed()
            ->get();
        return response()->json($data);
    }
    public function listActiveNames (){
        $data = Organization::withoutTrashed()->get(['name','id']);
        return response()->json($data);
    }
    public function listInactive (){
        $data = Organization::with(['sessionDetails'])
            ->withCount(['branches','devices'])
            ->onlyTrashed()->get();
        return response()->json($data);
    }

    private function validDateRequestDate($issued_loan_id, $issued_date)
    {
        return RepaidLoan::where('issued_loan_id', $issued_loan_id)
            ->where('repaid_amount', '>', 0)
            ->where('repayment_date', '>', Carbon::parse($issued_date))
            ->get();
    }

    public function store (Request $request){
        $validated_details = $request->all();
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string'],
                'phone_2' => ['nullable', 'string'],
                'email' => ['nullable', 'string', 'email'],
                'kra_pin' => ['nullable', 'string'],
                'location' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ];


                $rules['name'][] = Rule::unique('organizations', 'name');
                $rules['code'][] = Rule::unique('organizations', 'code');
                $rules['phone'][] = Rule::unique('organizations', 'phone');
                $rules['phone_2'][] = Rule::unique('organizations', 'phone_2');
                $rules['email'][] = Rule::unique('organizations', 'email');
                $rules['kra_pin'][] = Rule::unique('organizations', 'kra_pin');


            $validator = Validator::make($validated_details, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }




            $org = Organization::Create($validated_details);

            return response()->json([
                'status' => 'success',
                'message' => $org['name'] . ' -Added Successfully',
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
        $data = Organization::with(['sessionDetails','branches', 'devices'])->withCount(['branches','devices'])->withTrashed()->findOrFail($id);
        return response()->json($data);
    }

    public function update (Request $request){
        $validated_details = $request->all();
        $org_id = $validated_details['org_id'];
        $success_message = '';
        try {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:255','regex:/^[a-zA-Z0-9_-]+$/'],
                'phone' => ['required', 'string','min:7', 'max:15', 'regex:/^[0-9+\-() ]+$/'],
                'phone_2' => ['nullable', 'string','min:7', 'max:15', 'regex:/^[0-9+\-() ]+$/'],
                'email' => ['nullable', 'string', 'email'],
                'kra_pin' => ['nullable', 'string', 'regex:/^[A-Z]\d{9}[A-Z]$/'],
                'location' => ['nullable', 'string'],
                'description' => ['nullable', 'string'],
            ];



                $rules['name'][] = Rule::unique('organizations', 'name')->ignore($org_id);
                $rules['code'][] = Rule::unique('organizations', 'code')->ignore($org_id);
                $rules['phone'][] = Rule::unique('organizations', 'phone')->ignore($org_id);
                $rules['phone_2'][] = Rule::unique('organizations', 'phone_2')->ignore($org_id);
                $rules['email'][] = Rule::unique('organizations', 'email')->ignore($org_id);
                $rules['kra_pin'][] = Rule::unique('organizations', 'kra_pin')->ignore($org_id);



            $validator = Validator::make($validated_details, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors(),
                ]);
            }

            $org = Organization::withTrashed()->findOrFail($org_id);
            $org->update($validated_details);
            return response()->json([
                'status' => 'success',
                'message' => $org['name'] . ' - Updated Successfully',
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
    public function destroy($org_id)
    {
        try {
            $org = Organization::withoutTrashed()->find($org_id);

            if (!$org) {

                return response()->json(['status' => 'error', 'message' =>  'Organization already deactivated']);
            }
            $org->delete();

            return response()->json(['status' => 'success', 'message' =>  $org->name.' deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
        }
    }

    public function restore($org_id){

        try {
            $org = Organization::onlyTrashed()->find($org_id);

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

