<?php

namespace App\Jobs;

use App\Models\GenerateAddresssReport;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateAddressReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    
    public function __construct(public $verification, public $type, public $start_date, public $end_date)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle():void
    {
        $verifications = $this->verification;
        $type = $this->type;
        $html = '';
        foreach($verifications as $verification){
     if(!isset($verification->addressVerificationDetail) || empty($verification->addressVerificationDetail)) continue;
        foreach($verification->addressVerificationDetail as $address_verification){

    $address_verification->candidate = json_decode($address_verification->candidate,true);
    if ($address_verification->business != null) $address_verification->business = json_decode($address_verification->business);
    if ($address_verification->guarantor != null) $address_verification->guarantor = json_decode($address_verification->guarantor, true);
    $address_verification->address = json_decode($address_verification->address);
    $address_verification->notes = json_decode($address_verification->notes);
    $address_verification->images = json_decode($address_verification->images);
    $address_verification->links = json_decode($address_verification->links);

       $html .= view('users.address.address_pdf', [
                'address_verification' => $address_verification,
                'slug' => $type
            ])->render();
        $html .= '<div style="page-break-after: always;"></div>';
    }
    }

    $wrapper = view('users.address.pdf-layouts', ['content' => $html])->render();
    $pdf = Pdf::loadHTML($wrapper)->setPaper('a4');
    $fileName = "verifications-{$verifications->first()->id}-".rand(11111,999999).".pdf";
    $filePath = public_path("verifications/".$fileName);
    if (!file_exists(dirname($filePath))) {
        mkdir(dirname($filePath), 0777, true);
    }
    file_put_contents($filePath, $pdf->output());
       GenerateAddresssReport::create([
            'user_id' => $verifications->first()->id,
            'address_type' => $type,
            'reports'  => "verifications/".$fileName,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
}
}
