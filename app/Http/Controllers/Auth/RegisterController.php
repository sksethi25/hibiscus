<?php

namespace App\Http\Controllers\Auth;

use App\{User,Patients,PatientsAssignment};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use App\ApiResponse;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    //use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth')->except('register', 'showRegistrationForm', 'update', 'login');
    }


    public function register(Request $request){
        $validator = $this->registervalidator($request->all());
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $user= $this->create($request->all());

        $message="User Created.";
        if(Auth::user()->role_id!=0){
            Auth::login($user);
            $message='Logged In';
        }
        

        $profile=[
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email,
            'phone'=>$user->phone,
            'role_id'=>$user->role_id,
            'address'=>$user->address,
            'degree'=>$user->degree,
            'department'=>$user->department
        ];
        return ApiResponse::success([
            'message' => $message,
            'status' => true,
            'user'=>$profile
            ]
        );
    }

    public function login(Request $request){

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
           $user=Auth::user();
            $message='User Logged In.';
            $profile=[
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=>$user->email,
            'phone'=>$user->phone,
            'role_id'=>$user->role_id,
            'address'=>$user->address,
            'degree'=>$user->degree,
            'department'=>$user->department
        ];
        return ApiResponse::success([
            'message' => $message,
            'status' => true,
            'user'=>$profile
            ]
        );
        }else{
             return ApiResponse::unauthorizedError(EC_UNAUTHORIZED, 'Invalid Email or Password');
        }
    }




    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function registervalidator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required_if:phone,','nullable', 'string', 'email','unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'phone' => ['required_if:email,','nullable', 'string', 'unique:users,phone'],
            'role_id'=> ['nullable','integer'],
            'address'=> ['nullable','string', 'max:255'],
            'degree'=> ['nullable','string', 'max:255'],
            'department'=> ['nullable','string', 'max:255']
        ]);
    }

      /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function updatevalidator(array $data, $id)
    {
        return Validator::make($data, [
            'id' => ['nullable','integer'],
            'name' => ['string', 'max:255'],
            'email' => ['nullable','string', 'email','unique:users,email,'.$id],
            //'password' => ['required', 'string', 'min:8'],
            'phone' => ['nullable','string', 'unique:users,phone,'.$id],
            'role_id'=> ['integer'],
            'address'=> ['nullable','string', 'max:255'],
            'degree'=> ['nullable','string', 'max:255'],
            'department'=> ['nullable','string', 'max:255']
        ]);
    }

      /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function patientadmissionvalidator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required','string', 'max:255'],
        ]);
    }

     /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function patientassignvalidator(array $data)
    {
        return Validator::make($data, [
            'patient_id' => ['required','integer', 'exists:patients,id'],
            'assigned_to_id' => ['required','integer', 'exists:users,id'],
        ]);
    }


     /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function patientunassignvalidator(array $data)
    {
        return Validator::make($data, [
            'patient_assignment_id' => ['required','integer', 'exists:patients_assignment,id'],
        ]);
    }



    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $role = $data['role_id'] ? ($data['role_id'] == 0 ? 1 : $data['role_id']) : 1;
        $degree ="";
        $department ="";
        if($role == 2 || $role == 3){
            $degree = $data['degree'] ?? '';
            $department = $data['department'] ?? '';
        }   

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'password' => Hash::make($data['password']),
            'role_id'=> $role,
            'address'=> $data['address'] ?? '',
            'degree'=> $degree,
            'department'=> $department
        ]);
    }

    public function update(Request $request){
        $logged_in_user = Auth::User();
        $logged_in_user_id= $logged_in_user->id;
        if($logged_in_user->role_id==0 && $request->has('id') && !empty($request->get('id'))){
            $id=$request->get('id');
            $user= User::find($id);
            if(!$user){
                return ApiResponse::validationFailed(['id'=>'Invalid User id']);
            }
            $user_id=$id;
        }else{
            $user=$logged_in_user;
            $user_id=$logged_in_user_id;
        }

        $validator = $this->updatevalidator($request->all(), $user_id);
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $data = $request->only('id','name', 'email', 'phone', 'role_id','address', 'degree', 'department');


         if(($request->has('email') && empty($data['email']) )){

            if(($user->phone=="" && empty($data['phone']))){
               return ApiResponse::validationFailed(['email'=>'Email can not be empty when phone is not present or empty']);
            }
            $data['email']="";
         }
         if(($request->has('phone') && empty($data['phone']) )){

            if(($user->email=="" && empty($data['email']))){
                return ApiResponse::validationFailed(['phone'=>'Phone can not be empty when email is not present or empty']);
            }
            $data['phone']="";
         }

         $user->update([
            'name'=> !empty($data['name']) ? $data['name']: $user->name,
            'email' => isset($data['email']) && !is_null($data['email']) ? $data['email']: $user->email,
            'phone' => isset($data['phone']) && !is_null($data['phone'])  ? $data['phone']: $user->phone,
            'role_id'=> isset($data['role_id']) && !is_null($data['role_id']) && $data['role_id']>0 ? $data['role_id'] : $user->role_id,
            'address'=> isset($data['address']) && !is_null($data['address'])  ? $data['address']:'',
            'degree'=> isset($data['degree']) && !is_null($data['degree'])  ? $data['degree']: '',
            'department'=> isset($data['department']) && !is_null($data['department']) ? $data['phone']: '',
         ]);

        $message='User Updated.';
        $profile=[
        'id'=>$user->id,
        'name'=>$user->name,
        'email'=>$user->email,
        'phone'=>$user->phone,
        'role_id'=>$user->role_id,
        'address'=>$user->address,
        'degree'=>$user->degree,
        'department'=>$user->department
        ];
        return ApiResponse::success([
            'message' => $message,
            'status' => true,
            'user'=>$profile
            ]
        );
    }


    public function admitPatient(Request $request){
        $validator = $this->patientadmissionvalidator($request->all());
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $user = Auth::User();
        $user_id= $user->id;
        $name=$request->get('name');
        $patient = Patients::create([
            'name'=>$name,
            'admitted_by_id'=>$user_id
        ]);

        return ApiResponse::success([
            'message' => "Patient Admitted",
            'status' => true,
            'patient'=>[
                'name'=>$patient->name,
                'patient_id'=>$patient->id

                ]
            ]
        );
    }


    public function assignToPatient(Request $request){
        $validator = $this->patientassignvalidator($request->all());
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $patient_id=$request->get('patient_id');
        $assigned_to_id=$request->get('assigned_to_id');

        $user = Auth::User();
        $user_id= $user->id;


       // dd($assinged_to_id);
        
        $patientasignment = PatientsAssignment::create([
            'patient_id'=>$patient_id,
            'assigned_to_id'=>$assigned_to_id,
            'assigned_by_id'=>$user_id,
            'unassigned_by_id'=>null
        ]);

        return ApiResponse::success([
            'message' => "Successfully Assigned to Patient.",
            'status' => true,
            'assignment'=>[
                'patient_id'=>$patient_id,
                'assinged_to_id'=>$assigned_to_id,
                'patient_assignment_id'=>$patientasignment->id
                ]
            ]
        );
    }


     public function unassignToPatient(Request $request){
        $validator = $this->patientunassignvalidator($request->all());
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $patient_assignment_id=$request->get('patient_assignment_id');
        $user = Auth::User();
        $user_id= $user->id;
        
        $patientasignment = PatientsAssignment::find($patient_assignment_id);

        if($patientasignment){
            $patientasignment->deleted_at=now();
            $patientasignment->unassigned_by_id=$user_id;
            $patientasignment->save();
        }

        return ApiResponse::success([
            'message' => "Successfully Unassigned.",
            'status' => true
        ]);
    }
}
