<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Events\AddressVerificationCreated;
use App\Models\AddressVerificationDetail;
use Illuminate\Support\Carbon;

class InsertAddressVerificationPayload implements ShouldQueue
{
    public $connection='';
    public $queue='';
    public $delay='';

    /**
     * Create the event listener.
     * 
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(AddressVerificationCreated $event)
    {
        $res = $event->res;
        $get_address_verification = $event->address_verification;

        $candidate = [
            'first_name' => $get_address_verification->firt_name,
            'last_name' => $get_address_verification->last_name,
            'phone' => $get_address_verification->phone,
        ];
        AddressVerificationDetail::create([
            'address_verification_id' => $get_address_verification->id,
            'reference_id' => $res['customerReference'],
            'candidate' => $res['type'] == 'guarantor'?json_encode(json_encode($candidate)):json_encode($res['applicant']),
            'guarantor' => $res['type'] == 'guarantor'?json_encode($res['applicant']):'',
            'address' => json_encode($res['address']['location']),
            'status' => $res['summary']['address_check'],
            'task_status' => $res['summary']['address_check'],
            'start_date' => $res['address']['requestedAt'],
            'closest_landmark' => isset($res['data'])?$res['address']['location']['landmark']:'',
            'accepted_at' => isset($res['data'])?$res['data']['acceptedAt']:'',
            'revalidation_date'=> isset($res['data'])?$res['data']['revalidationDate']:'',
            'notes'=> isset($res['data'])?json_encode($res['data']['notes']):'',
            'is_flagged'=> isset($res['data'])?$res['data']['isFlagged']:1,
            'subject_consent' =>  true,
            'agent' => "null",
            'agent_submitted_longitude' => isset($res['data'])?$res['data']['agentSubmittedLongitude']:'',
            'agent_submitted_latitude' => isset($res['data'])?$res['data']['agentSubmittedLatitude']:'',
            'report_geolocation_url' => isset($res['data'])?$res['data']['reportGeolocationUrl']:'',
            'map_address_url' => isset($res['data'])?$res['data']['mapAddressUrl']:'',
            'submission_distance_in_meters'=> isset($res['data'])?$res['data']['submissionDistanceInMeters']:'',
            'reasons' => isset($res['data'])?$res['data']['reasons']:'',
            'signature' => isset($res['data'])?$res['data']['signature']:'',
            'images' => isset($res['data'])?json_encode($res['data']['images']):'',
            'building_type' => isset($res['data'])?$res['data']['buildingType']:'',
            'building_color' => isset($res['data'])?$res['data']['buildingColor']:'',
            'gate_present' => isset($res['data'])?$res['data']['gatePresent']:'',
            'gate_color' => isset($res['data'])?$res['data']['gateColor']:'',
            'availability_confirmed_by' => isset($res['data'])?$res['data']['availabilityConfirmedBy']:'',
            'additional_info'=> $res['data']['additionalInfo']??'',
            'report_agent_access' => isset($res['data'])?$res['data']['reportAgentAccess']:'',
            'incident_report' => isset($res['data'])?$res['data']['incidentReport']:'',
            'description' => isset($res['data'])?$res['data']['description']:'',
            'report_id' => isset($res['data'])?$res['data']['reportId']:'',
            'download_url' => isset($res['data'])?$res['data']['downloadUrl']:'',
            'business_type' => isset($res['data'])?$res['data']['businessType']:'',
            'business_id' => isset($res['data'])?$res['data']['businessId']:'',
            'yv_user_id' => isset($res['data'])?$res['data']['userId']:'',
            'type' =>  $res['type'],
            'yv_id' => isset($res['data'])?$res['data']['id']:'',
            'links' => isset($res['data'])?json_encode($res['links']):'',
            'expected_report_date' => Carbon::now()->addDays(4)
          ]);
    }
}
