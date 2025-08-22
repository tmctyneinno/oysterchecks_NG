 <h2>Address Verification Report</h2>

  <div class="section">
    <div class="section-title">Verification Details</div>
    <div class="row"><span class="label">Verification ID:</span> <span class="value">{{$address_verification?->reference_id}}</span></div>
    <div class="row"><span class="label">Type:</span> <span class="value"> {{ucfirst($slug)}} Verification</span></div>
    <div class="row"><span class="label">Created At:</span> <span class="value">{{ date('jS F Y, h:iA', strtotime($address_verification?->created_at))}}</span></div>
     @if(strtolower($address_verification?->status) == 'completed' && strtolower($address_verification?->task_status) == 'verified')
    <div class="status"> Completed and Verified</div>
    @elseif(strtolower($address_verification?->status) == 'completed' && strtolower($address_verification?->task_status) == 'not_verified')
    <span style="color:coral"> Completed and Not Verified<</span>
     @elseif(strtolower($address_verification?->status) == 'INVALID_ADDRESS')
      <span style="color:orangered"> Completed and Not Verified<</span>
      @elseif(strtolower($address_verification?->status) == 'WRONG_ADDRESS')
      <span style="color:coral"> Completed and Not Verified<</span>
     @else 
      <span style="color:blue"> {{$address_verification?->status}}</span>
     @endif
  </div>

  <div class="section">
    <div class="section-title">Candidate Information</div>
    <div class="row"><span class="label">Name:</span> <span class="value">{{isset($address_verification?->candidate['firstName'])?$address_verification?->candidate['firstName']: (isset($address_verification?->candidate['firstname'])?$address_verification?->candidate['firstname']: '')}}
       @if(isset($address_verification?->candidate['middleName']) || isset($address_verification?->candidate['middlename']))
       {{isset($address_verification?->candidate['middleName'])?$address_verification?->candidate['middleName']: (isset($address_verification?->candidate['middlename'])?$address_verification?->candidate['middlename']: '')}}
       @endif
       {{isset($address_verification?->candidate['lastname'])?$address_verification?->candidate['lastname']: (isset($address_verification?->candidate['lastName'])?$address_verification?->candidate['lastName']:'')}}
    </span></div>
    <div class="row"><span class="label">Phone:</span> <span class="value">{{isset($address_verification?->candidate['mobile'])?$address_verification?->candidate['mobile']: (isset($address_verification?->candidate['phone'])?$address_verification?->candidate['phone']:'')}}</span></div>
     @if(isset($address_verification?->candidate['email']))
    <div class="row"><span class="label">Email:</span> <span class="value">
        {{$address_verification?->candidate['email']??''}}
    </span></div>
         @endif
  </div>

  @if($address_verification?->guarantor != null)
  <div class="section">
    <div class="section-title"> Guarantor's information</div>
    <div class="row"><span class="label">Name:</span>
       <span class="value">
        {{isset($address_verification?->guarantor['firstName'])?$address_verification?->guarantor['firstName']: (isset($address_verification?->guarantor['firstname'])?$address_verification?->guarantor['firstname']: '')}} 
      {{isset($address_verification?->guarantor['lastName'])?$address_verification?->guarantor['lastName']: (isset($address_verification?->guarantor['lastname'])?$address_verification?->guarantor['lastname']: '')}} 
    </span></div>
    <div class="row"><span class="label">Phone:</span> <span class="value">{{isset($address_verification?->guarantor['mobile'])?$address_verification?->guarantor['mobile']: (isset($address_verification?->guarantor['phone'])?$address_verification?->guarantor['phone']:'')}}</span></div>
    <div class="row"><span class="label">Email:</span> <span class="value">
     @if(isset($address_verification?->guarantor['email']))
        {{$address_verification?->guarantor['email']}}
      @endif
    </span>
    </div>
  </div>
    @endif


  <div class="section">
    <div class="section-title">Address Information</div>
    <div class="row"><span class="label">Flat/Building:</span> <span class="value">
     @if(isset($address_verification?->address['flatNumber']) )
     {{$address_verification?->address['flatNumber']}}
      @endif
       @if(isset($address_verification?->address['buildingName']))
       {{$address_verification?->address['buildingName']}}
       @endif
      </span></div>
    <div class="row"><span class="label">Street:</span> <span class="value">
     @if(isset($address_verification?->address['street']))
     {{$address_verification?->address['street']}}
     @endif
    </span></div>
     @if(isset($address_verification?->address['city']))
        <div class="row"><span class="label">City:</span> <span class="value">
      {{$address_verification?->address['city']}}
     </span></div>
    @endif
     @if(isset($address_verification?->address['lga']) || isset($address_verification?->address['lgaName']) )
        <div class="row"><span class="label">LGA:</span> <span class="value">
        {{$address_verification?->address['lga']??$address_verification?->address['lgaName']}}
     </span></div>
    @endif

    <div class="row"><span class="label">Country:</span> <span class="value">
       {{ isset($address_verification?->address['state']) 
          ? $address_verification?->address['state'] 
          : (isset($address_verification?->address['stateName']) 
              ? $address_verification?->address['stateName'] 
              : '') }}
    </span></div>
    <div class="row"><span class="label">Longitude:</span> <span class="value">
        {{ isset($address_verification?->address['latlong']['lon']) 
          ? $address_verification?->address['latlong']['lon'] 
          : (isset($address_verification?->address['longitude']) 
              ? $address_verification?->address['longitude'] 
              : '') }}
              </span></div>
    <div class="row"><span class="label">Latitude:</span> <span class="value">
       {{ isset($address_verification?->address['latlong']['lat']) 
          ? $address_verification?->address['latlong']['lat'] 
          : (isset($address_verification?->address['latitude']) 
              ? $address_verification?->address['latitude'] 
              : '') }}
              </span></div>
  </div>

  <div class="section">
    <div class="section-title">Verification Findings</div>
    <div class="row"><span class="label">Building Type:</span> <span class="value">{{$address_verification?->building_type}}</span></div>
    <div class="row"><span class="label">Building Color:</span> <span class="value">{{$address_verification?->building_color}}</span></div>
    <div class="row"><span class="label">Gate Color:</span> <span class="value">{{$address_verification?->gate_color}}</span></div>
    <div class="row"><span class="label">Landmark:</span> <span class="value">{{$address_verification?->closest_landmark}}</span></div>
    <div class="row"><span class="label">Confirmed By:</span> <span class="value">{{$address_verification?->availability_confirmed_by}}</span></div>
    <div class="notes">
       @if(!is_array($address_verification?->notes))
       {{$address_verification?->notes}} 
        @else
       @foreach($address_verification?->notes as $note)
       @if(is_array($note)){{$note['note']}}  @else {{$note}} @endif
          @endforeach
          @endempty
    </div>
     @empty($address_verification?->images)
    <div class="col-12 text-center py-3">No Images Available</div>
    @else
   @foreach($address_verification?->images as $image)
    <div>
            @if(isset($image['filePath'])) 
            <img src="{{$image['filePath']}}"  height="50px" width="100px" alt="" >
            @else 
            <img src="{{$image}}" alt=""  height="50px" width="100px"  >
            @endif
    </div>
    @endforeach
    @endempty
  </div>