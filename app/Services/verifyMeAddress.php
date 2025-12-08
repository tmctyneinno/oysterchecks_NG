<?php
namespace App\Services;

use App\Events\AddressVerificationCreated;
use App\Models\AddressVerification;
use App\Models\Client;
use App\Models\Lga;
use App\Traits\GenerateRef;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\States;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Verification;
use App\Traits\sandbox;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class verifyMeAddress 
{
    public function __construct(
        public readonly Base $base
    )
    {
    }
    use GenerateRef;
    use sandbox;

    public function createCandidate($request, $slug)
    {
        $ref = $this->GenerateRef();
        AddressVerification::create([
                  'verification_id' => $slug->id,
                  'ref' => $ref,
                  'user_id' => auth()->user()->id,
                  'status' => 'pending',
                  'slug' => $slug->slug,
                  'service_reference' => $ref,
                  'candidate_id' => $ref,
                  'first_name' => $request->first_name,
                  "middle_name" => $request->middlename??"",
                  'last_name' => $request->last_name,
                  "phone" => $request->phone,
                  "email" => $request->email,
                  "dob" => $request->dob,
                  'is_sandbox' => $this->sandbox(),
                  'expected_report_date' => Carbon::now()->addDays(4)
                ]);
        Session::flash('alert', 'success');
        Session::flash('message', 'Candidate Created Successfully');
        return back();
    }


    public function submitAddressVerify($request, $service_ref)
    {
        // if($this->sandbox() != 1){
        //     Session::flash('alert', 'error');
        //     Session::flash('message', 'This service is not available for test mode');
        //     return redirect()->back()->withInput($request->all());
        // }
        $slug = Verification::whereSlug($request->slug)->first();
        $userWallet = Wallet::where('user_id', auth()->user()->id)->first();
          $ref = $this->GenerateRef();

        if ($userWallet->avail_balance < $slug->fee) {
            Session::flash('alert', 'error');
            Session::flash('message', 'Your walllet is too low for this transaction');
            return redirect()->back()->withInput($request->all());
        }
      
        try{
        $address_verification = AddressVerification::where('service_reference', $service_ref)->first();
      
       $CandidatePhone = preg_replace('/^0/','234',$address_verification->phone);
        if ($request->slug == 'individual-address') {
            $valid = Validator::make($request->all(), [
              'street' => 'required|string',
              'state' => 'required|string',
              'city' => 'required|string',
              'landmark' => 'required|string',
              'lga' => 'nullable',
              'subject_consent' => 'required|accepted'
            ]);
            if ($valid->fails()) {
              Session::flash('alert', 'error');
              Session::flash('message', $valid->errors()->first());
              return redirect()->back()->withErrors($valid)->withInput($request->all());
            }
            $body = [
                'applicant' => [
                    'firstname' => $address_verification->first_name,
                    'lastname'=> $address_verification->last_name,
                    'phone'=> $CandidatePhone,
                    'dob'=> $address_verification->dob,
                    'gender'=> $address_verification->gender,
                           ],
                "street" => $request->street,
                "customerReference" => $ref,
                "lgaName" => $request->lga??"",
                "stateName" => $request->state??"",
                "landmark" => $request->landmark.' company: '.getCompanyName(),
                "city" => $request->city,
            ];
            $token = $this->base->generateToken()['accessToken'];
          $resp =   $this->base->baseUrl('post', 'https://api.qoreid.com/v1/addresses', $body, $token);
          $res = json_decode($resp,true);
          Log::info(['info' => $res]);
            $res['type'] = 'individual';
             $this->chargeUser($slug->fee, $res['customerReference'], $res['type']);
            // event(new AddressVerificationCreated($res, $address_verification));
          Session::flash('alert', 'success');
          Session::flash('message', 'Address successfully sent for verifications');
          return back();
        }elseif ($request->slug == 'reference-address') {
            $valid = Validator::make($request->all(), [
              'first_name' => 'required|string',
              'last_name' => 'required|string',
              'phone' => 'required',
              'landmark' => 'required|string',
              'street' => 'required|string',
              'state' => 'required|string',
              'city' => 'required|string',
              'lga' => 'required',
            ]);
            if ($valid->fails()) {
              Session::flash('alert', 'error');
              Session::flash('message', $valid->errors()->first());
              return redirect()->back()->withErrors($valid)->withInput($request->all());
            }
            $phone = preg_replace('/^0/','234',$request->phone);
            $body = [
              "description" => '',
              'applicant' => [
                'firstname' => $request->first_name,
                'lastname'=> $request->last_name,
                'phone'=> $phone??$request->phone,
                'dob'=> $request->dob,
                'gender'=> $request->gender,
                       ],
              "street" => $request->street,
              "customerReference" => $ref,
              "lgaName" => $request->lga??"",
              "stateName" => $request->state??"",
              "landmark" => 'Company: '.getCompanyName().", Guarantor for ".$address_verification->first_name. '  '.$address_verification->last_name .', Candidate phone: '.$CandidatePhone,
              "city" => $request->city
            ];

            $token = $this->base->generateToken()['accessToken'];
            $resp =   $this->base->baseUrl('post', 'https://api.qoreid.com/v1/addresses', $body, $token);
            $res = json_decode($resp,true);
            $res['type'] = 'guarantor';
             Log::info(['info' => $res]);
             $this->chargeUser($slug->fee, $res['customerReference'], $res['type']);
            event(new AddressVerificationCreated($res, $address_verification));
            Session::flash('alert', 'success');
            Session::flash('message', 'Address successfully sent for verifications');
            return back();
          }else{
            Log::info(['info' => 'Selected wrong address']);
            Session::flash('alert', 'error');
            Session::flash('message', 'This service is not available at the moment');
            return back()->withInput($request->all());
            
          }
        Session::flash('alert', 'error');
        Session::flash('message', 'Selected wrong address');
          return back();
        }catch(\Exception $e)
        {
         Log::info(['info' => $e->getMessage()]);
          Session::flash('alert', 'error');
        Session::flash('message', 'Something went wrong'.$e->getMessage());
          return back();
        }
     
    }


    public function chargeUser($amount, $ext_ref, $type)
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


}