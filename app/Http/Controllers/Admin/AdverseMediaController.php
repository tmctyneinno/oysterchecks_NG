<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Verification;
use App\Models\AdverseMedia;

class AdverseMediaController extends Controller
{
    //

    public function AdverseMediaIndex($slug)
    {
        $slug = Verification::where('slug', $slug)->first();
        $data['slug'] = Verification::where('slug', $slug->slug)->first();
        $data['no_match_found'] = AdverseMedia::where(['status' => 'no-match-found', 'verification_id' => $slug->id])->get();
        $data['potential_high_risk'] = AdverseMedia::where(['status' => 'potential-high-risk', 'verification_id' => $slug->id])->get();
        $data['potential_medium_risk'] = AdverseMedia::where(['status' => 'potential-medium-risk', 'verification_id' => $slug->id])->get();
        $data['total_request'] = AdverseMedia::where(['verification_id' => $slug->id])->get();
        $data['logs'] = AdverseMedia::where(['user_id' => auth()->user()->id])->latest()->get();
        return view('users.aml.adversemedia.index', $data);
    }

    public function AdverseMediaGetReport($ref){
        return view('users.aml.adversemedia.reports.report', [
            'transactions' => AdverseMedia::where('id', decrypt($ref))->first()
        ]);
    }
}
