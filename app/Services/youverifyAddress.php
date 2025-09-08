<?php
namespace App\Services;

use App\Events\AddressVerificationCreated;
use Illuminate\Support\Facades\DB;
use App\Models\AddressVerification;
use App\Traits\GenerateRef;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\States;
use App\Models\User;
use App\Models\Verification;
use App\Traits\sandbox;
use App\Models\Wallet;
use Illuminate\Support\Facades\Validator;

class youverifyAddress 
{

    use GenerateRef;
    use sandbox;

    public function createCandidate($request, $slug)
    {
        if ($request->file('image')) {

            $candidate_image = cloudinary()->upload($request->file('image')->getRealPath(), [
              'folder' => 'oysterchecks/candidates'
            ])->getSecurePath();
          }
      
          $ref = $this->GenerateRef();
          DB::beginTransaction();
            $curl = curl_init();
            $data = [
              "firstName" => $request->first_name,
              "middleName" => $request->middle_name != null ? $request->middle_name : "",
              "lastName" => $request->last_name,
              "mobile" => $request->phone,
              "email" => $request->email != null ? $request->email : "user@gmail.com",
              "dateOfBirth" =>$request->dob??"2000-12-15",
              "image" => $candidate_image
            ];
            $datas = json_encode($data, true);
            curl_setopt_array($curl, [
               CURLOPT_URL => $this->ReqUrl()."addresses/candidates",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 2180,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $datas,
              CURLOPT_SSL_VERIFYPEER => false,
              CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Token: ".$this->ReqToken()
              ],
            ]);
            $response = curl_exec($curl);
              $res = json_decode($response, true);
              if ($res['success'] == true && $res['statusCode'] == 201) {
                AddressVerification::create([
                  'verification_id' => $slug->id,
                  'ref' => $res['data']['id'],
                  'user_id' => auth()->user()->id,
                  'status' => 'pending',
                  'slug' => $slug->slug,
                  'service_reference' => $res['data']['id'],
                  'candidate_id' => $res['data']['youverifyCandidateId'],
                  'first_name' => $request->first_name,
                  "middle_name" => $res['data']['middleName']??"",
                  'last_name' => $request->last_name,
                  "phone" => $res['data']['mobile'],
                  "email" => $res['data']['email']??"",
                  "dob" => $res['data']['dateOfBirth']??'',
                  "image" => $candidate_image,
                  'is_sandbox' => $this->sandbox(),
                  'expected_report_date' => Carbon::now()->addDays(4)
                ]);
                DB::commit();
                Session::flash('alert', 'success');
                Session::flash('message', 'Candidate Created Successfully');
                return back()->with([
                 'states' => States::get()
                 ]);
    }
    
}

    public function submitAddressVerify($request,$service_ref)
    {
        $slug = Verification::whereSlug($request->slug)->first();
        $userWallet = Wallet::where('user_id', auth()->user()->id)->first();
        if($this->sandbox() == 1){
        if ($userWallet->avail_balance < $slug->fee) {
            Session::flash('alert', 'error');
            Session::flash('message', 'Your walllet is too low for this transaction');
            return redirect()->back()->withInput($request->all());
        }
    }

        $get_address_verification = AddressVerification::where('service_reference', $service_ref)->first();
        if ($get_address_verification){
          $get_address_verification_id = $get_address_verification->id;
        }
        if ($request->slug == 'individual-address') {
            $valid = Validator::make($request->all(), [
              'flat_number' => 'nullable',
              'building_name' => 'nullable',
              'building_number' => 'required|string',
              'landmark' => 'required|string',
              'street' => 'required|string',
              'sub_street' => 'nullable',
              'state' => 'required|string',
              'city' => 'required|string',
              'lga' => 'nullable',
              'subject_consent' => 'required|accepted'
            ]);
            if ($valid->fails()) {
              Session::flash('alert', 'error');
              Session::flash('message', $valid->errors()->first());
              return redirect()->back()->withErrors($valid)->withInput($request->all());
            }
            $host = $this->ReqUrl().'addresses/individual/request';
            $data = [
              "candidateId" => $service_ref,
              "description" => "Verify the candidate",
              "address" => [
                "flatNumber" => $request->flat_number?? "",
                "buildingName" => $request->building_name??"",
                "buildingNumber" => $request->building_number,
                "landmark" => $request->landmark,
                "street" => $request->street,
                "subStreet" => $request->sub_street??"",
                "state" => $request->state,
                "city" => $request->city,
                "lga" => $request->lga??"",
              ],
              "subjectConsent" => $request->subject_consent ? true : false,
      
            ];
          } elseif ($request->slug == 'reference-address') {
         
            $valid = Validator::make($request->all(), [
              'first_name' => 'required|string',
              'last_name' => 'required|string',
              'phone' => 'required',
              'email' => 'required|email',
              'image' => 'required',
              'flat_number' => 'nullable',
              'building_name' => 'nullable',
              'building_number' => 'required|string',
              'landmark' => 'required|string',
              'street' => 'required|string',
              'sub_street' => 'nullable',
              'state' => 'required|string',
              'city' => 'required|string',
              'lga' => 'nullable|string',
              'subject_consent' => 'required'
            ]);
            if ($valid->fails()) {
              Session::flash('alert', 'error');
              Session::flash('message', $valid->errors()->first());
              return redirect()->back()->withErrors($valid)->withInput($request->all());
            }
            $image = '';
            if (request()->file('image')) {
              $image = request()->file('image');
              $name =  $image->getClientOriginalName();
              $FileName = \pathinfo($name, PATHINFO_FILENAME);
              $ext =  $image->getClientOriginalExtension();
              $time = time() . $FileName;
              $dd = md5($time);
              $fileName = $dd . '.' . $ext;
              if ($image->move('assets/guarantors', $fileName)) {
                $image = $fileName;
              }
            }
      
            $host = $this->ReqUrl().'addresses/guarantor/request';
            $data = [
              "candidateId" => $service_ref,
              "description" => "Verify the candidtate guarantor",
              "guarantor" => [
                "firstName" => $request->first_name,
                'lastName' => $request->last_name,
                'mobile' => $request->phone,
                'email' => $request->email,
                'image' =>  'https://oysterchecks.com/assets/images/logo.png',
              ],
              "address" => [
                "flatNumber" => $request->flat_number != null ? $request->flat_number : "",
                "buildingName" => $request->building_name != null ? $request->building_name : "",
                "buildingNumber" => $request->building_number,
                "landmark" => $request->landmark,
                "street" => $request->street,
                "subStreet" => $request->sub_street != null ? $request->sub_street : "",
                "state" => $request->state,
                "city" => $request->city,
                "lga" => $request->lga != null ? $request->lga : "",
              ],
              "subjectConsent" => true,
            ];
          } else {
            $host = $this->ReqUrl().'addresses/business/request';
            $data = [
              "candidateId" => $service_ref,
              "description" => "Verify the candidate and business",
              "business" => [
                "name" => $request->name,
                "email" => $request->email,
                "mobile" => $request->phone,
                "registrationNumber" => $request->registration_number,
              ],
              "address" => [
                "flatNumber" => $request->flat_number != null ? $request->flat_number : "",
                "buildingName" => $request->building_name != null ? $request->building_name : "",
                "buildingNumber" => $request->building_number,
                "landmark" => $request->landmark,
                "street" => $request->street,
                "subStreet" => $request->sub_street != null ? $request->sub_street : "",
                "state" => $request->state,
                "city" => $request->city,
                "lga" => $request->local_govt != null ? $request->local_govt : "",
              ],
              "subjectConsent" => true,
            ];
          }

    
      $datas = json_encode($data, true);
      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => $host,
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_ENCODING => "",
       CURLOPT_MAXREDIRS => 10,
       CURLOPT_TIMEOUT => 2180,
       CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
       CURLOPT_CUSTOMREQUEST => "POST",
       CURLOPT_POSTFIELDS => $datas,
       CURLOPT_SSL_VERIFYPEER => false,
       CURLOPT_HTTPHEADER => [
         "Content-Type: application/json",
         "Token: ".$this->ReqToken()
       ],
     ]);
      $response_data = curl_exec($curl);
      if (curl_errno($curl)) {
        Session::flash('alert', 'error');
        Session::flash('message', 'An error occured, please try again later');
        return redirect()->back()->withErrors($valid)->withInput($request->all());
      } else {
        $res = json_decode($response_data, true);
        curl_close($curl);
        if ($res['success'] == true && $res['statusCode'] == 201) {
         event(new AddressVerificationCreated($res, $get_address_verification_id));
          DB::commit();
          if($this->sandbox() == 1){
            $reference = $res['data']['id'];
            $reasons = 'Payment for '.$slug->name;
            $account = $request->pin ;
            $this->chargeUser($slug->fee, $reference , $reasons, $account);
        }
    }
    }

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
}