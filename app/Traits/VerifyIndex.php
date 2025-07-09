<?php 
namespace App\Traits;
use App\Models\IdentityVerification;
use App\Models\Transaction;
use App\Models\ActivityLog;

trait VerifyIndex{
    
    public function IdentityIndex()
    {
       
        $successCount = IdentityVerification::where(['status' => 'found'])->count();
        $failedCount = IdentityVerification::where(['status' => 'not-found'])->count();
        $pendingCount = IdentityVerification::where(['status' => 'null'])->count();
    
        $data['success'] = IdentityVerification::where(['status' => 'found'])->get();
        $data['failed'] = IdentityVerification::where(['status' => 'not-found'])->get();
        $data['pending'] = IdentityVerification::where(['status' => 'null'])->get();
        $data['logs'] = IdentityVerification::latest()->take(5)->get();
        $data['recents'] = IdentityVerification::latest()->take(5)->get();
        $data['activity'] = ActivityLog::take(10)->latest()->get();
        $data['transactions'] = Transaction::latest()->get();
    
        $data['chartDatas'] = [
            'labels' => ['Success', 'Failed', 'Pending'],
            'datasets' => [
                [
                    'label' => 'Verifications',
                    'data' => [$successCount, $failedCount, $pendingCount],
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 206, 86, 0.2)'
                    ],
                    'borderWidth' => 2,
                    'fill' => true
                ]
            ]
        ];

        $data['chartData'] = [
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June'],
            'datasets' => [
                [
                    'label' => 'Success',
                    'data' => [10, 50, 30, 90, 40, 70],
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'borderWidth' => 2,
                    'fill' => false
                ],
                [
                    'label' => 'Failed',
                    'data' => [20, 60, 40, 70, 30, 60],
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 2,
                    'fill' => false
                ],
                [
                    'label' => 'Pending',
                    'data' => [30, 40, 50, 60, 80, 100],
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                    'fill' => false
                ]
            ]
        ];
    
        return $data;
    }
    
 
}

