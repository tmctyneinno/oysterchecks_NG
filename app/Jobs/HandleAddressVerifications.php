<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\AddressVerificationDetail;
use App\Models\AddressVerification;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;

class HandleAddressVerifications extends SpatieProcessWebhookJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // public $webhookCall;


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

    
        // dd($this->webhookCall);
        $webhookCallData = json_decode($this->webhookCall,true)['payload'];
        if(!empty($webhookCallData)){
        // dd($webhookCallData['data']['customerReference']);
        if(in_array($webhookCallData['data']['customerReference'],$webhookCallData) ){
            
           
        // dd($webhookCallData['data']);
        // die();
        //  logger($webhookCallData);
      
            $get_verification_details = AddressVerificationDetail::where('reference_id', $webhookCallData['data']['customerReference'])->firstorfail();
            //   dd($get_verification_details);
            if ($get_verification_details) {
                // dd($webhookCallData['data']['address']);
            $get_verification_details->agent = json_encode($webhookCallData['data']['address']['agentComment']);
            $get_verification_details->status = ($webhookCallData['data']['address']['status']['state'] == 'COMPLETE'?'COMPLETED':$webhookCallData['data']['address']['status']['state']);
            $get_verification_details->task_status = $webhookCallData['data']['address']['status']['status'];
            $get_verification_details->execution_date = $webhookCallData['data']['address']['requestedAt'];
            $get_verification_details->completed_at = $webhookCallData['data']['address']['approvedAt'];
            $get_verification_details->accepted_at = $webhookCallData['data']['address']['requestedAt'];
            $get_verification_details->notes = json_encode($webhookCallData['data']['address']['neighbor']['comment']);
            $get_verification_details->business = json_encode($webhookCallData['data']['address']['business']);
            $get_verification_details->agent_submitted_longitude = $webhookCallData['data']['address']['location']['longitude'];
            $get_verification_details->agent_submitted_latitude = $webhookCallData['data']['address']['location']['latitude'];
            $get_verification_details->report_geolocation_url = $webhookCallData['data']['address']['location']['locationUrl'];
            $get_verification_details->map_address_url = $webhookCallData['data']['address']['location']['locationUrl'];
            $get_verification_details->reasons = $webhookCallData['data']['address']['neighbor']['comment'];
            $get_verification_details->images = json_encode($webhookCallData['data']['address']['exteriorPhotos']);
            $get_verification_details->closest_landmark = $webhookCallData['data']['address']['location']['landmark'];
            $get_verification_details->address = json_encode($webhookCallData['data']['address']['location']);
            $get_verification_details->save();


            // $get_verification_details->availability_confirmed_by = $webhookCallData['data']['availabilityConfirmedBy'];
            // $get_verification_details->submission_distance_in_meters = $webhookCallData['data']['submissionDistanceInMeters'];
            // $get_verification_details->signature = $webhookCallData['data']['signature'];
            // $get_verification_details->building_type = $webhookCallData['data']['location']['street'];//
            // $get_verification_details->building_color = $webhookCallData['data']['buildingColor'];
            // $get_verification_details->gate_present = $webhookCallData['data']['gatePresent'];
            // $get_verification_details->gate_color = $webhookCallData['data']['gateColor'];
            // $get_verification_details->additional_info = $webhookCallData['data']['additionalInfo'];
            // $get_verification_details->report_agent_access = $webhookCallData['data']['reportAgentAccess'];
            // $get_verification_details->incident_report = $webhookCallData['data']['incidentReport'];
            // $get_verification_details->download_url = $webhookCallData['data']['downloadUrl'];
            // $get_verification_details->start_date = $webhookCallData['data']['startDate'];
            // $get_verification_details->end_date = $webhookCallData['data']['endDate'];
            // $get_verification_details->submitted_at = $webhookCallData['data']['submittedAt'];
            // $get_verification_details->revalidation_date = $webhookCallData['data']['revalidationDate'];
            // $get_verification_details->is_flagged = $webhookCallData['data']['isFlagged'];
            // $get_verification_details->save();

            // $get_verification = AddressVerification::where('id', $get_verification_details->address_verification_id)->first();
            // $get_verification->update(['status' => $webhookCallData['data']['task_status']]);
    
        }
        }
    
    }
    }
}
