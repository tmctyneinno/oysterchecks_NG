<?php

namespace App\Jobs;

use App\Models\GenerateAddresssReport;
use App\Models\AddressVerification;
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GenerateAddressReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3; // Number of attempts
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(public $user_id, public $type, public $report_doc_type, public $status, public $start_date, public $end_date)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Starts with querying the database to fetch actual verifications
        $verifications = AddressVerification::where(['user_id' => $this->user_id])
            ->whereHas('addressVerificationDetail', function($query) {
                $query->where('type', $this->type)
                      ->whereBetween('created_at', [$this->start_date, $this->end_date]);

                // Only filter by status if it's not null
                if ($this->status !== null && $this->status !== '') {
                    $query->where('status', $this->status);
                }
            })
            ->with(['addressVerificationDetail' => function($query) {
                $query->where('type', $this->type)
                      ->whereBetween('created_at', [$this->start_date, $this->end_date]);

                // Only filter by status if it's not null
                if ($this->status !== null && $this->status !== '') {
                    $query->where('status', $this->status);
                }
            }])
            ->get();

        if ($this->report_doc_type === 'pdf') {
            $this->generatePdfReport($verifications, $this->type);
        } elseif ($this->report_doc_type === 'excel') {
            $this->generateExcelReport($verifications->toArray(), $this->type);
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($verifications, $type): void
    {
        //Temporary increase memory limit for PDF generation (in case of heavy data)
        ini_set('memory_limit', '2048M');
        
        $html = '';
        foreach ($verifications as $verification) {
            if (!isset($verification->addressVerificationDetail) || empty($verification->addressVerificationDetail)) {
                continue;
            }
            
            foreach ($verification->addressVerificationDetail as $address_verification) {
                // if ($address_verification->status == 'IN_PROGRESS') {
                //     continue;
                // }
                
                $address_verification->candidate = json_decode($address_verification->candidate, true);
                if ($address_verification->business != null) {
                    $address_verification->business = json_decode($address_verification->business);
                }
                if ($address_verification->guarantor != null) {
                    $address_verification->guarantor = json_decode($address_verification->guarantor, true);
                }
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
        
        // Clear the HTML variable to free memory
        unset($html);
        
        $pdf = Pdf::loadHTML($wrapper)->setPaper('a4');
        
        // Clear the wrapper variable to free memory
        unset($wrapper);
        
        $fileName = "verifications-" . date('Y-m-d') . "-" . time() . ".pdf";
        $filePath = public_path("verifications/" . $fileName);
        
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        
        file_put_contents($filePath, $pdf->output());
        
        GenerateAddresssReport::create([
            'user_id' => $this->user_id,
            'address_type' => $type,
            'reports' => "verifications/" . $fileName,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($verifications, $type): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = $this->setupExcelHeaders($type);

        // Add headers to the first row with styling
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Style the header row
        $headerRange = 'A1:' . chr(64 + count($headers)) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FF4472C4', // Blue background
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => Color::COLOR_WHITE,
                ],
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        $row = 2;
        $statusColumnIndex = null;
        $taskStatusColumnIndex = null;
        
        // Find the column indices for Status and Task Status
        foreach ($headers as $index => $header) {
            if ($header === 'Status') {
                $statusColumnIndex = $index;
            }
            if ($header === 'Task Status') {
                $taskStatusColumnIndex = $index;
            }
        }
        
        foreach ($verifications as $verification) {
            if (!isset($verification['address_verification_detail']) || empty($verification['address_verification_detail'])) {
                continue;
            }

            foreach ($verification['address_verification_detail'] as $address_verification) {
                $body = $this->setupExcelBody($verification, $address_verification, $type);
                $col = 'A';
                
                foreach ($body as $colIndex => $data) {
                    $sheet->setCellValue($col . $row, $data);
                    
                    // Apply color styling for Status column
                    if ($statusColumnIndex !== null && $colIndex === $statusColumnIndex) {
                        $this->applyStatusStyling($sheet, $col . $row, $data, 'status');
                    }
                    
                    // Apply color styling for Task Status column
                    if ($taskStatusColumnIndex !== null && $colIndex === $taskStatusColumnIndex) {
                        $this->applyStatusStyling($sheet, $col . $row, $data, 'task_status');
                    }
                    
                    $col++;
                }

                $row++;
            }
        }
        
        // Auto-size columns AFTER all data is written
        foreach (range('A', chr(64 + count($headers))) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $fileName = "address-verifications-" . date('Y-m-d') . "-" . time() . ".xlsx";
        $filePath = public_path("verifications/" . $fileName);
        
        // Create the directory if it doesn't exist
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        
        // Write the file
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
        
        GenerateAddresssReport::create([
            'user_id' => $this->user_id,
            'address_type' => $type,
            'reports' => "verifications/" . $fileName,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);
    }

    private function setupExcelHeaders($type): array
    {
        $headers = [];
        switch ($type) {
            case 'individual':
                $headers = [
                    'Verification ID', 'Candidate Name', 'Phone', 'Address', 'Type', 'Status',
                    'Completion Date', 'Task Status', 'Notes'
                ];
                break;
            case 'business':
                $headers = [
                    'Verification ID', 'Candidate Name', 'Business Name', 'Registration Number', 'Address', 'Type', 'Status',
                    'Completion Date', 'Task Status', 'Notes'
                ];
                break;
            case 'guarantor':
                $headers = [
                    'Verification ID', 'Candidate Name', 'Guarantor Name', 'Guarantor Phone', 'Address', 'Type', 'Status',
                    'Completion Date', 'Task Status', 'Notes'
                ];
                break;
            default:
                break;
        }

        return $headers;
    }

    private function setupExcelBody($address_verification, $address_verification_detail, $type): array
    {
        $body = [];
        $address = json_decode($address_verification_detail['address'], true);
        $notes = $address_verification_detail['notes'] ? json_decode($address_verification_detail['notes'], true) : '';
        

        // Ensure you add data in the same order as headers
        array_push($body, $address_verification_detail['reference_id']);
        array_push($body, $address_verification['first_name']. ' ' . $address_verification['last_name']);
        
        switch ($type) {
            case 'individual':
                array_push($body, $address_verification['phone']);
                array_push($body, $address['street']);
                array_push($body, 'Individual-Address');
                break;
            case 'business':
                $business = json_decode($address_verification_detail['business'], true);
                array_push($body, $business['businessName'] ?? $business['name']);
                array_push($body, $business['rcNumber']);
                array_push($body, 'Business-Address');
                array_push($body, $address['street']);
                break;
            case 'guarantor':
                $guarantor = json_decode($address_verification_detail['guarantor'], true);
                array_push($body, $guarantor['firstname']. ' ' . $guarantor['lastname']);
                array_push($body, $guarantor['phone']);
                array_push($body, $address['street']);
                array_push($body, 'Reference-Address');
                break;
            default:
                break;
        }

        array_push($body, $address_verification_detail['status']);
        array_push($body, $address_verification_detail['execution_date'] ?? '');
        array_push($body, $address_verification_detail['task_status'] ?? '');

        // Handle notes with proper null checking
        $notesValue = '';
        if (is_array($notes)) {
            if (isset($notes['comment']) && $notes['comment'] !== null) {
                $notesValue = $notes['comment'];
            } elseif (isset($notes['note']) && $notes['note'] !== null) {
                $notesValue = $notes['note'];
            } else {
                $notesValue = '';
            }
        }
        array_push($body, $notesValue);

        return $body;
    }

    /**
     * Apply color styling to status cells in Excel
     */
    private function applyStatusStyling($sheet, $cellAddress, $value, $type): void
    {
        $upperValue = strtoupper($value);
        $color = '#000000'; // Default black
        
        if ($type === 'status') {
            // Status field colors
            switch ($upperValue) {
                case 'COMPLETED':
                    $color = '28a745'; // Green
                    break;
                case 'IN_PROGRESS':
                    $color = '17a2b8'; // Info Blue
                    break;
                case 'PENDING':
                    $color = 'ffc107'; // Warning Yellow
                    break;
                case 'CANCELLED':
                    $color = 'dc3545'; // Danger Red
                    break;
                default:
                    $color = '6c757d'; // Gray
                    break;
            }
        } elseif ($type === 'task_status') {
            // Task Status field colors
            switch ($upperValue) {
                case 'VERIFIED':
                    $color = '28a745'; // Success Green
                    break;
                case 'WRONG_ADDRESS':
                case 'INVALID_ADDRESS':
                case 'CANCELLED':
                    $color = 'dc3545'; // Danger Red
                    break;
                case 'NO_ACCESS_UNVERIFIED':
                    $color = 'ffc107'; // Warning Yellow
                    break;
                case 'IN_PROGRESS':
                    $color = '17a2b8'; // Info Blue
                    break;
                default:
                    $color = '6c757d'; // Gray
                    break;
            }
        }

        // Apply the styling
        $sheet->getStyle($cellAddress)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => [
                    'argb' => 'FF' . $color,
                ],
            ],
        ]);
    }
}
