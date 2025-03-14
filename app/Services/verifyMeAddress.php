<?php
namespace App\Services;

use App\Events\AddressVerificationCreated;
use Illuminate\Support\Facades\DB;
use App\Models\AddressVerification;
use App\Models\Candidate;
use App\Models\Lga;
use App\Traits\GenerateRef;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\States;
use App\Models\User;
use App\Models\Verification;
use App\Traits\sandbox;
use App\Models\Wallet;
use Illuminate\Support\Facades\File;
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
                  "middle_name" => $res['data']['middleName']??"",
                  'last_name' => $request->last_name,
                  "phone" => $request->phone,
                  "email" => $request->email,
                  "dob" => $request->dob,
                  'is_sandbox' => $this->sandbox(),
                  'expected_report_date' => Carbon::now()->addDays(4)
                ]);
                Session::flash('alert', 'success');
                Session::flash('message', 'Candidate Created Successfully');
                return back()->with([
                 'states' => States::get()
                 ]);
}


    public function submitAddressVerify($request, $service_ref)
    {
        $slug = Verification::whereSlug($request->slug)->first();
        $userWallet = Wallet::where('user_id', auth()->user()->id)->first();
        // if($this->sandbox() == 1){
        if ($userWallet->avail_balance < $slug->fee) {
            Session::flash('alert', 'error');
            Session::flash('message', 'Your walllet is too low for this transaction');
            return redirect()->back()->withInput($request->all());
        }

        $address_verification = AddressVerification::where('service_reference', $service_ref)->first();
       
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
                    'phone'=> $address_verification->phone,
                    'dob'=> $address_verification->dob,
                    'gender'=> $address_verification->first_name,
                           ],
                "street" => $request->street??"",
                "customerReference" => $service_ref,
                "lgaName" => $request->lga??"",
                "stateName" => $request->state??"",
                "landmark" => $request->landmark??"",
                "city" => $request->city,
            ];
            $token = $this->base->generateToken()['accessToken'];
          $resp =   $this->base->baseUrl('post', 'https://api.qoreid.com/v1/addresses', $body, $token);


          $res = json_decode($resp,true);
          if($res['customerReference'])
          {
            $address_verification = $address_verification->id;
            event(new AddressVerificationCreated($res, $address_verification));
          }

          return $res;
        }
    // }


    }


    public function storeStates()
    {
        $states = File::get(base_path('app/services/states.json'));
        $states = json_decode($states, true);
        foreach($states as $state)
        {
           $ss = States::create([
                'name' => $state['state']
            ]);

            if($ss)
            foreach($state['lgas'] as $lgs)
            {
                Lga::create([
                    'state_id' => $ss->id,
                    'name' => $lgs
                ]);
            }
        }

    }


}