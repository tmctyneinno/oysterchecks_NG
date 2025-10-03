<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAddressReportJob;
use App\Models\AddressVerification;
use App\Models\AddressVerificationDetail;
use App\Models\GenerateAddresssReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GenerateAddressReportController extends Controller
{
    
    public function showList()
    {
        $reports = GenerateAddresssReport::where('user_id', auth()->user()->id)->latest()->get();
        return view('users.address.generateReport')
        ->with('reports', $reports);
    }

    public function RunGeneratorJob(Request $request)
    {
        
        $dates = explode('-',$request->daterange);
        $start_date = Carbon::parse(strip_tags($dates[0]))->startOfDay();
        $end_date = Carbon::parse(strip_tags($dates[1]))->endOfDay();
        $status = $request->status ?? null;

        //rescope query type to match address verification detail type
        switch ($request->query_type) {
            case 'individual-address':
                $verification_detail_type = 'individual';
                break;
            case 'reference-address':
                $verification_detail_type = 'guarantor';
                break;
            case 'business-address':
                $verification_detail_type = 'business';
                break;
            default:
                $verification_detail_type = 'individual';
        }

        $verification_count = AddressVerificationDetail::whereHas('addressVerification', function($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->where('type', $verification_detail_type)
            ->whereBetween('created_at', [$start_date, $end_date])
            ->when($status !== null && $status !== '', function($query) use ($status) {
                $query->where('status', $status);
            })
            ->count();

        if($verification_count > 0){

            // Pass only query parameters to the job, not the actual data
            GenerateAddressReportJob::dispatch(
                auth()->user()->id,
                $verification_detail_type,
                $request->report_doc_type,
                $status,
                $start_date,
                $end_date
            );

            Session::flash('alert', 'success');
            Session::flash('message', 'Report generation has been queued. Please check back in a few minutes for your download link.');
            return back();
        }

        Session::flash('alert', 'error');
        Session::flash('message', 'No Report found within the dates selected');
        return back();

    }
}
