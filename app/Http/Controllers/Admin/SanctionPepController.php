<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PepSactionVerification;
use App\Models\Verification;

class SanctionPepController extends Controller
{
    public function SanctionPepIndex($slug){
        $slug = Verification::where('slug', $slug)->first();
        $data['slug'] = Verification::where('slug', $slug->slug)->first();
        $data['success'] = PepSactionVerification::where(['status' => 'cleared', 'verification_id' => $slug->id])->get();
        $data['failed'] = PepSactionVerification::where(['status' => 'not_cleared', 'verification_id' => $slug->id])->get();
        $data['pending'] = PepSactionVerification::where(['status' => 'review_required', 'verification_id' => $slug->id])->get();
        $data['logs'] = PepSactionVerification::where(['user_id' => auth()->user()->id])->latest()->get();
        return view('users.aml.media.index', $data);
    }

    public function SanctionPepGetReport($ref){
        return view('users.aml.media.reports.report', [
            'transactions' => PepSactionVerification::where('id', decrypt($ref))->first()
        ]);
    }
}
