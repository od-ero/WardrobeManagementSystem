<?php

namespace App\Http\Controllers;
    
   
    
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Validator;
    use Spatie\Activitylog\Facades\LogBatch;
    use App\Models\Wardrobe;
    
    class WardrobeController extends Controller
    {
        public function index (){

           $data = Wardrobe::withoutTrashed()->with('category')->get();
           return response()->json($data);
        }
    
        public function indexInactive (){
    
           $list_devices = Wardrobe::onlyTrashed()->with('category')->get();
           return response()->json($list_devices);
        }
    
        public function store (Request $request){
            $validated_details = $request->all();
            Log::debug($validated_details);
          
            DB::beginTransaction();
                try {
                  
                    $validator = Validator::make($validated_details, [
                        'name' => ['required', 'string','unique:wardrobes,name', 'max:255'],
                        'category_id' => ['required'],
                        'brand' => ['nullable', 'string'],
                        'size' =>  ['nullable', 'string'],
                        'color' =>  ['nullable', 'string'],
                        'pattern' => ['nullable', 'string', ],
                        'material' =>  ['nullable', 'string'],
                        'purchase_price' =>  ['nullable', 'string'],
                       // 'purchase_date' => ['nullable', 'string', ],
                        'purchase_place' =>  ['nullable', 'string'],
                        'description' => ['nullable', 'string', ],
                    ]);
    
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => 'error',
                            'errors' => $validator->errors(),
                        ]);
                    }
                 
    
    
                    $wordrobe = Wardrobe::create($validated_details);
                   
                   
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' =>  $wordrobe['name'] . ' - Added Successful',
                        'id' =>  $wordrobe['id']
    
                    ],200);
                }
                catch (\Exception $e) {
                    DB::rollBack();
                    Log::debug($e);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'An Error Occured. Contact Adminstrator',
                        'errors' => $e
                    ], 422);
                }
        }
    
        public function show($id){
            $data = Wardrobe::withTrashed()->with('category')->findOrFail($id);
            return response()->json($data);
        }
    
        public function update (Request $request){
            $validated_details = $request->all();
           $wrId = $validated_details['wrId'];
            DB::beginTransaction();
            try {
    
                $validator = Validator::make($validated_details, [
                    'name' => ['required', 'string','unique:wardrobes,name,'. $wrId, 'max:255'],
                   
                    'category_id' => ['required'],
                        'brand' => ['nullable', 'string'],
                        'size' =>  ['nullable', 'string'],
                        'color' =>  ['nullable', 'string'],
                        'pattern' => ['nullable', 'string', ],
                        'material' =>  ['nullable', 'string'],
                        'purchase_price' =>  ['nullable', 'string'],
                        'purchase_date' => ['nullable', 'string', ],
                        'purchase_place' =>  ['nullable', 'string'],
                        'description' => ['nullable', 'string', ],
                ]);
    
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $validator->errors(),
                    ]);
                }
                LogBatch::startBatch();
          
    
                $data = Wardrobe::withTrashed()->findOrFail( $wrId);
                $data->update($validated_details);
    
                LogBatch::endBatch();
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' =>
                        $data['name'] . ' - Updated Successful',
                    'device_id' => $data['id']
    
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
        public function destroy($id)
        {
            try {
                $data = Wardrobe::withoutTrashed()->find($id);
    
                if (!$data) {
    
                    return response()->json(['status' => 'error', 'message' =>  'Item already deactivated']);
                }
                $data->delete();
    
                return response()->json(['status' => 'success', 'message' =>  $data->name.' deleted successfully']);
            } catch (\Exception $e) {
                Log::debug($e);
                return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
            }
        }
    
        public function restore($id){
    
            try {
                $data = Wardrobe::onlyTrashed()->find($id);
    
                if (!$data) {
    
                    return response()->json(['status' => 'error', 'message' =>  'Item already active']);
                }
                $data->restore();
    
                return response()->json(['status' => 'success', 'message' =>  $data['name'].'  restored successfully']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'An error occurred', 'errors' => [$e->getMessage()]]);
            }
        }
    }