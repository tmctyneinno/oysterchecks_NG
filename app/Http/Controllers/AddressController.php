<?php

namespace App\Http\Controllers;
 
use App\Events\AddressVerificationCreated;
use Illuminate\Http\Request; 
use App\Models\AddressVerification;
use App\Models\AddressVerificationDetail;
use App\Models\Verification;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Client;
use App\Traits\generateHeaderReports;
use Illuminate\Support\Str;
use App\Traits\GenerateRef;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\FieldInput;
use App\Models\Lga;
use App\Models\States;
use App\Traits\sandbox;
use App\Models\Wallet;
use App\Services\Base;
use App\Services\verifyMeAddress;
use Illuminate\Support\Facades\File;
use Vinkla\Hashids\Facades\Hashids;

class AddressController extends Controller
{
  use GenerateRef;
  use generateHeaderReports;
  use sandbox;
private $token;
    public function __construct(
      public readonly verifyMeAddress $verifyMeAddress,
      public readonly Base $base
    )
    {
      $this->token = "eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIzaVgtaEFrS3RmNUlsYWhRcElrNWwwbFBRVlNmVnpBdG9WVWQ4UXZ1OHJFIn0.eyJleHAiOjE3NDE4ODQ1MjgsImlhdCI6MTc0MTg3NzMyOCwianRpIjoiNjcwZWQ0MTYtM2RkOS00NWM2LWE5ZGUtZWJhNDcyZjc1NTYwIiwiaXNzIjoiaHR0cHM6Ly9hdXRoLnFvcmVpZC5jb20vYXV0aC9yZWFsbXMvcW9yZWlkIiwiYXVkIjpbInFvcmVpZGFwaSIsImFjY291bnQiXSwic3ViIjoiNWFiNTZhZjAtNzM2OC00MzMzLTk0ZDUtMzVkNzNlZTE0YTA5IiwidHlwIjoiQmVhcmVyIiwiYXpwIjoiNDFLQ0xXN09VTDkxQk41MDJWT0wiLCJhY3IiOiIxIiwicmVhbG1fYWNjZXNzIjp7InJvbGVzIjpbIm9mZmxpbmVfYWNjZXNzIiwidW1hX2F1dGhvcml6YXRpb24iLCJkZWZhdWx0LXJvbGVzLXFvcmVpZCJdfSwicmVzb3VyY2VfYWNjZXNzIjp7InFvcmVpZGFwaSI6eyJyb2xlcyI6WyJ2ZXJpZnlfdmVyaWZpbmRfNGRfc3ViIiwidmVyaWZ5X2J1c2luZXNzX2FkZHJlc3Nfc3ViIiwidmVyaWZ5X25pbl9zdWIiLCJ2ZXJpZnlfaW5kaXZpZHVhbF9hZGRyZXNzX3N1YiIsInZlcmlmeV9ndWFyYW50b3Jfc3ViIl19LCJhY2NvdW50Ijp7InJvbGVzIjpbIm1hbmFnZS1hY2NvdW50IiwibWFuYWdlLWFjY291bnQtbGlua3MiLCJ2aWV3LXByb2ZpbGUiXX19LCJzY29wZSI6InByb2ZpbGUgZW1haWwiLCJlbnZpcm9ubWVudCI6InNhbmRib3giLCJjbGllbnRIb3N0IjoiMTkyLjE2OC4yMTYuMTQxIiwiY2xpZW50SWQiOiI0MUtDTFc3T1VMOTFCTjUwMlZPTCIsIm9yZ2FuaXNhdGlvbklkIjoxMDIzNjksImVtYWlsX3ZlcmlmaWVkIjpmYWxzZSwicHJlZmVycmVkX3VzZXJuYW1lIjoic2VydmljZS1hY2NvdW50LTQxa2NsdzdvdWw5MWJuNTAydm9sIiwiYXBwbGljYXRpb25JZCI6MjM4NDAsImNsaWVudEFkZHJlc3MiOiIxOTIuMTY4LjIxNi4xNDEifQ.ldXlz54yCzc6fIa3nE3V_ls76WHsXUfTIKNHuiOKo3_2c3Zt8QGH3ykxWNHoD3g5WYzdRr-odydKJJ70HyJ2yEYq39dnQpykVlOU96Yb05N9zlhJ1EazIT4eY3N4JPh94-MS1kYzQ5jo6vtBWrFp153AiCwXUcuUOln9Xup6qBjlMJvJHLPLg7Grkyg8GKAkmaJ7ui-gJPYfx87jZkcp_WAp9rAd7_74H38HJZbUmdpKfywh0QcDkSLxgBsl7xfGhU5xAdBp3LhNKtro0ZR6XJadnjHzKjbYAJpIpWKtkLeYj1qishgFAhx_UWehymrg6yT39juFzxW09IhcaUuASw";
      return $this->middleware('clients');
    }
  

  public function AddressIndex($slug)
  {
    
    
   $this->storeStates();
    $data = $this->generateAddressReport($slug);
    return view('users.address.index', $data); 
  }

  public function showCreateCandidate($slug)
  {

   
    $data = $this->generateCreateCandidateData($slug);

    return view('users.address.createAddressCandidate', $data);
  }

  public function createCandidate(Request $request, $slug)
  {
    $slug = Verification::where('slug', $slug)->first();
    $valid = Validator::make($request->all(), [
      'first_name' => 'required|string',
      'middle_name' => 'nullable|string',
      'last_name' => 'required|string',
      'phone' => 'required|min:11|max:11',
      'email' => 'nullable|email',
      'dob' => 'nullable|date_format:"Y-m-d"',
      // 'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
    ]);
    if ($valid->fails()) {
      Session::flash('alert', 'error');
      Session::flash('message', $valid->errors()->first());
      return redirect()->back()->withErrors($valid)->withInput($request->all());
    }
    try {
      return $this->verifyMeAddress->createCandidate($request, $slug);
    } catch (\Exception $e) {
      DB::rollBack();
      Session::flash('alert', 'error');
      Session::flash('message', 'Something went wrong, Pleae try again');
      return back()->with(['errors' => $e->getMessage()])->withInput($request->all());
    }
  }

  public function showVerificationDetailsForm(Request $req, $slug, $service_ref)
  {
    if($slug == ' '){
      $slug = $req->slug;
    }
    //  dd($slug);
    // $token = $this->base->generateToken();
    // dd($token);

    $data = $this->generateAddressReportVerify($slug);

    $data['service_ref'] = $service_ref;
    $data['states'] = States::get();
    $data['address'] = Verification::where('report_type', '=', 'address')->get();
    return view('users.address.verifyAddress', $data);
  }

  public function submitAddressVerify(Request $request, $service_ref)
  {
    if (!isset($service_ref)) {
      Session::flash('alert', 'error');
      Session::flash('message', 'Bad request, refresh page');
      return redirect()->back()->withInput($request->all());
    }

      return $this->verifyMeAddress->submitAddressVerify($request,$service_ref);

  }


  public function ViewCandidateAddresses($verification_id){


    $id = Hashids::connection('verify')->decode($verification_id)[0];
    $verification = AddressVerificationDetail::where('address_verification_id', $id)->get();
    foreach($verification as $verify)
    {
      $verify->hashid = encodeId($verify->id);
    }
    // dd(decrypt($verification_id));
    $data['candidate'] =  $verification[0]->addressVerification->first_name . $verification[0]->addressVerification->last_name;
    $data['verified'] = AddressVerificationDetail::where(['address_verification_id' => $id, 'status' => 'completed'])->get();
    $data['not_verified']  = AddressVerificationDetail::where(['address_verification_id' => $id, 'status' => 'pending'])->get();
    $data['verification'] = $verification;

    return view('users.address.verifications', $data);
  }
  public function verificationReport($slug, $addressId)
  {

    dd($addressId);
    $slug = explode('-', $slug)['0'];
  
    if($slug == 'guarantor')
    {
      $slug = 'reference-address';
    }else{
      $slug = $slug.'-address';
    }


    $slug = Verification::where('slug', $slug)->first();
    
   $addressId = decodeId($addressId);
    $address_verification = AddressVerificationDetail::where(['id' => $addressId])->first();
    $address_verification->candidate = json_decode($address_verification->candidate,true);
    if ($address_verification->business != null) $address_verification->business = json_decode($address_verification->business);
    if ($address_verification->guarantor != null) $address_verification->guarantor = json_decode($address_verification->guarantor, true);
  
// dd(  $address_verification->candidate);
    $address_verification->agent = json_decode($address_verification->agent);
    $address_verification->address = json_decode($address_verification->address);
    $address_verification->notes = json_decode($address_verification->notes);
    $address_verification->images = json_decode($address_verification->images);
    $address_verification->links = json_decode($address_verification->links);

    $address_verification->created_at;



    return view('users.address.addressReport', ['slug' => $slug, 'address_verification' => $address_verification]);
  }

  public function chargeUser($amount, $ext_ref, $type, $acount)
  {
      $user = User::where('id', auth()->user()->id)->first();
      $wallet = Wallet::where('user_id', $user->id)->first();
      $newWallet = $user->wallet->avail_balance - $amount;
      $update = Wallet::where('user_id', $user->id)
          ->update([
              'book_balance' => $wallet->avail_balance,
              'avail_balance' => $newWallet,
          ]);
      $refs = $this->GenerateRef();
      Transaction::create([
          'ref' => $refs,
          'user_id' => $user->id,
          'external_ref' => $ext_ref,
          'purpose' => $type,
          'service_type' => $type,
          'total_amount_payable' => $amount,
          'payment_method' => 'Wallet Payment',
          'type'  => 'DEBIT',
          'amount' => $amount,
          'prev_balance' => $wallet->avail_balance,
          'avail_balance' => $newWallet
      ]);
      return $update;
  }

  public function getLga(Request $req)
  {
    $lga = Lga::where('state_id', $req->states)->pluck('name');
    return response()->json(['data' => $lga]);
  }

  public function storeStates()
  {
      $states = File::get(base_path('app/Services/states.json'));
      $states = json_decode($states, true);
      $getState = States::get();
      if(count($getState) > 5) return $states;
      foreach($states as $state)
      {
         $ss = States::create([
              'name' => $state['state']
          ]);
          if($ss)
          foreach($state['lgas'] as $lgs)
          {
             $ssd = Lga::create([
                  'state_id' => $ss->id,
                  'name' => $lgs
              ]);
          }
      }
      return $states;

  }
}
