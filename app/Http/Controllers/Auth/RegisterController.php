<?php

namespace App\Http\Controllers\Auth;

use App\{User,Patients,PatientsAssignment,Form, FormFields, FormFieldTypes};
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


    public function register(Request $request){
        $validator = $this->registervalidator($request->all());
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $user= $this->create($request->all());

        if(Auth::Check() && Auth::user()->role_id==0){    
         $message="User Created."; 
        }else{
            Auth::login($user);
            $message='User registered and Logged In';
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
                'patient_id'=>(int)$patient->id

                ]
            ]
        );
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
                'patient_id'=>(int)$patient_id,
                'assinged_to_id'=>(int)$assigned_to_id,
                'patient_assignment_id'=>(int)$patientasignment->id
                ]
            ]
        );
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


      /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createFormvalidator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required','string', 'max:255', 'unique:form,name'],
            'fields'=>['required', 'json']
        ]);
    }

    public function createForm(Request $request){

         $validator = $this->createFormvalidator($request->all());
        if($validator->fails()){
           return  ApiResponse::validationFailed($validator->errors()->toArray());
        }

        $name = $request->get('name');

        $fields = $request->get('fields');

        $user = Auth::User();
        $user_id= $user->id;

        

        $master_form_filed_types=FormFieldTypes::pluck('id')->toArray();

        $form_fields= json_decode($fields, true);
        $form_fields_data=[];
        $invalid_form=false;
        foreach ($form_fields as $fields_arr) {
            if(isset($fields_arr['name']) && isset($fields_arr['form_fields_type_id']) && isset($fields_arr['order']) && isset($fields_arr['hidden'] ) ){

                if(in_array($fields_arr['form_fields_type_id'], $master_form_filed_types) === false ){
                    $message="invalid value for form_fields_type_id";
                    $invalid_form=true;
                    break;
                }

                if($fields_arr['order'] <1 ){
                    $message="invalid value for order";
                    $invalid_form=true;
                    break;
                }

                 if(in_array($fields_arr['hidden'], [0,1])=== false){
                    $message="invalid value for hidden";
                    $invalid_form=true;
                    break;
                }

                $form_fields_data[]=[
                    'name'=>$fields_arr['name'],
                    'form_fields_type_id'    => $fields_arr['form_fields_type_id'],
                    'order'    => $fields_arr['order'],
                    'hidden'     => $fields_arr['hidden']

                ];
            }else{
                $message="Each field should contain name, form_fields_type_id, order,hidden values";
                $invalid_form=true;
                break;
            }
        }


        if($invalid_form === true){
            return ApiResponse::validationFailed(['fields'=>$message]);
        }

        if(sizeof($form_fields_data)>0){
            $form = Form::create([
            'name'=>$name,
            'type'=>'',
            'created_by_id'=>$user_id
        ]);
              foreach ($form_fields_data as $form_fields_arr) {
                $form_fields_arr['form_id']=$form->id;
            FormFields::create($form_fields_arr);
            }
        }


         return ApiResponse::success([
            'message' => "Form Created Successfully.",
            'status' => true,
            'form'=>[
                'form_id'=>(int)$form->id
                ]
            ]
        );

    }
}
