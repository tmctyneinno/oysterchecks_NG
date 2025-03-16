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
        //
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
        // return $res;
        $get_address_verification_id = $event->address_verification;
        AddressVerificationDetail::create([
            'address_verification_id' => $get_address_verification_id,
            'reference_id' => $res['customerReference'],
            'candidate' => json_encode($res['applicant']),
            'guarantor' => isset($res['applicant']) ? json_encode($res['applicant']) : null,
            'address' => json_encode($res['address']['location']),
            'status' => $res['summary']['address_check'],
            'task_status' => $res['summary']['address_check'],
            'start_date' => $res['address']['requestedAt'],
            'closest_landmark' => $res['address']['location']['landmark'],
            'expected_report_date' => Carbon::now()->addDays(4),
            'report_id' => $res['customerReference'],
            'download_url' =>$res['customerReference'],
            'business_type' =>$res['customerReference'],
            'business_id' => $res['customerReference'],
            'yv_user_id' =>$res['customerReference'],
            'type' => $res['type'],
            'yv_id' => $res['customerReference'],
            'links' =>$res['customerReference'],
            'description'=>$res['customerReference'],
          ]);

        
    }
}
