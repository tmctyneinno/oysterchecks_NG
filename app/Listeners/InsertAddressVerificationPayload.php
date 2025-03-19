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
            'closest_landmark' => $res['address']['location']['landmark'],
            'accepted_at' => $res['data']['acceptedAt'],
            'revalidation_date'=> $res['data']['revalidationDate'],
            'notes'=> json_encode($res['data']['notes']),
            'is_flagged'=> $res['data']['isFlagged']??'',
            'subject_consent' =>  true,
            'agent_submitted_longitude' => $res['data']['agentSubmittedLongitude']??'',
            'agent_submitted_latitude' => $res['data']['agentSubmittedLatitude']??'',
            'report_geolocation_url' => $res['data']['reportGeolocationUrl']??'',
            'map_address_url' => $res['data']['mapAddressUrl']??'',
            'submission_distance_in_meters'=> $res['data']['submissionDistanceInMeters']??'',
            'reasons' => $res['data']['reasons']??'',
            'signature' => $res['data']['signature']??'',
            'images' => json_encode($res['data']['images'])??'',
            'building_type' => $res['data']['buildingType']??'',
            'building_color' => $res['data']['buildingColor']??'',
            'gate_present' => $res['data']['gatePresent']??'',
            'gate_color' => $res['data']['gateColor']??'',
            'availability_confirmed_by' => $res['data']['availabilityConfirmedBy']??'',
            'additional_info'=> $res['data']['additionalInfo']??'',
            'report_agent_access' => $res['data']['reportAgentAccess']??'',
            'incident_report' => $res['data']['incidentReport']??'',
            'description' => $res['data']['description']??'',
            'report_id' => $res['data']['reportId']??'',
            'download_url' => $res['data']['downloadUrl']??'',
            'business_type' => $res['data']['businessType']??'',
            'business_id' => $res['data']['businessId']??'',
            'yv_user_id' => $res['data']['userId']??'',
            'type' => $res['data']['type']??'',
            'yv_id' => $res['data']['id'??''],
            'links' => json_encode($res['links'])??'',
            'expected_report_date' => Carbon::now()->addDays(4)
          ]);
    }
}
