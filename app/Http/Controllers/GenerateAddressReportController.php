<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAddressReportJob;
use App\Models\AddressVerification;
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
        $start_date = Carbon::parse(strip_tags($dates['0']));
        $end_date =  Carbon::parse($dates['1']);;
        $verification = AddressVerification::where(['user_id' => auth()->user()->id])->where('status', '!=', 'pending')
        ->where('slug', $request->query_type)
        ->whereBetween('created_at',[
            $start_date,
            $end_date
        ])
        ->take($request->reportCount??10)
        ->get();
         if(count($verification) > 0){
        GenerateAddressReportJob::dispatch($verification, $request->query_type,$start_date, $end_date);
        Session::flash('alert', 'success');
        Session::flash('message', 'Report is completed, please proceed to download');
        return back();
        }

        Session::flash('alert', 'error');
        Session::flash('message', 'No Report found within the dates selected');
        return back();

    }
}
