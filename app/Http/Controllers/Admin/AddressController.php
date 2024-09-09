<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Verification;
use App\Models\AddressVerification;
use App\Models\AddressVerificationDetail;
use Illuminate\Support\Facades\Session;

class AddressController extends Controller
{
    //

    public function AddressIndex($slug){
        $slug = Verification::where(['slug' => $slug])->first();
        $address_verifications = AddressVerification::where(['verification_id'=>$slug->id])->latest()->get();
        $data['verifications'] = $address_verifications;
        $data['slug'] = $slug;
        if(count($address_verifications) > 0){
        $data['address_verifications'] = $address_verifications->load('addressVerificationDetail');
          $data['results'] = AddressVerificationDetail::where('execution_date', '!=', null)->get();
         }
        return view('admin.address.index', $data);
    }

    public function ViewCandidateAddresses($verification_id){

        $verification = AddressVerificationDetail::where('address_verification_id', decrypt($verification_id))->get();
        if(!empty($verification)){
          Session::flash('alert', 'error');
          Session::flash('message', 'No verification found');
          return back();
          return back();
        }
        $data['candidate'] =  $verification[0]->addressVerification->first_name . $verification[0]->addressVerification->last_name;
        $data['verified'] = AddressVerificationDetail::where(['address_verification_id' => decrypt($verification_id), 'status' => 'completed'])->get();
        $data['not_verified']  = AddressVerificationDetail::where(['address_verification_id' => decrypt($verification_id), 'status' => 'pending'])->get();
        $data['verification'] = $verification;
        // dd($data);
        return view('admin.address.verifications', $data);
      }

      public function verificationReport($slug, $addressId)
      {
        if($slug == 'guarantor')
        {
          $slug = 'reference-address';
        }else{
          $slug = $slug.'-address';
        }
        $slug = Verification::where('slug', $slug)->first();
       
        $address_verification = AddressVerificationDetail::where(['id' => decrypt($addressId)])->first();
        $address_verification->candidate = json_decode($address_verification->candidate,true);
        if ($address_verification->business != null) $address_verification->business = json_decode($address_verification->business);
        if ($address_verification->guarantor != null) $address_verification->guarantor = json_decode($address_verification->guarantor, true);
      
    //dd(  $address_verification->candidate);
        $address_verification->agent = json_decode($address_verification->agent);
        $address_verification->address = json_decode($address_verification->address);
        $address_verification->notes = json_decode($address_verification->notes);
        $address_verification->images = json_decode($address_verification->images);
        $address_verification->links = json_decode($address_verification->links);
    
        $address_verification->created_at;
    
    
    
        return view('admin.address.addressReport', ['slug' => $slug, 'address_verification' => $address_verification]);
      }
}
