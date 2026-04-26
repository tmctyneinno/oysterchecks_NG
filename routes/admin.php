<?php 


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\{AdminBusinessController, AdminAddressController, AdminClientController, AdminCandidateController, AdminIdentityController, AdminPaymentController, BusinessController, ClientController, IdentityController, UserController};
 
 
  
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin', 'auth']], function() { 
    Route::get('/', [AdminController::class, 'Index'])->name('index');
    Route::get('/index', [AdminController::class, 'Index'])->name('home');
    Route::get('/identity/{slug}', [IdentityController::class, 'getVerify'])->name('verify'); 
    Route::get('/identities/{slug}/{verificationId}', [IdentityController::class, 'verificationReport'])->name('showIdentityReport');
    Route::get('/business/{slug}', [BusinessController::class, 'businessIndex'])->name('businessIndex');
    Route::get('/business/{slug}/{verificationId}', [BusinessController::class, 'businessDetails'])->name('showBusinessReport');
    Route::get('/address/{slug}', [AdminAddressController::class, 'AddressIndex'])->name('addressIndex');
    Route::get('/address/details/{id}', [AdminAddressController::class, 'AddressDetails'])->name('details');

    Route::get('/candidate', [AdminCandidateController::class, 'getVerify'])->name('candidate.index');
    Route::get('/candidate/index', [AdminCandidateController::class, 'CandidateIndex'])->name('candidate.index');
    Route::get('/candidate/details/{id}', [AdminCandidateController::class, 'CandidateDetails'])->name('candidate.details');
    Route::get('candidate/', [AdminCandidateController::class, 'UserCandidates'])->name('user.candidates');
    Route::get('/clients/candidate/{id}', [AdminCandidateController::class, 'ClientCandidates'])->name('client.candidates');
    Route::get('/user/clients/', [ClientController::class, 'UserClients'])->name('user.clients');
    Route::get('/clients/details/{client_id}', [ClientController::class, 'ClientProfile'])->name('client.details');
    Route::get('/client/activate/{client_id}', [ClientController::class, 'ActivateClientAccount'])->name('client.activateAccount');
    Route::get('/client/suspend/{client_id}', [ClientController::class, 'SuspendClientAccount'])->name('client.suspendAccount');
    Route::get('/clients/create', [ClientController::class, 'createClient'])->name('client.create');
    Route::post('/clients/store', [ClientController::class, 'ClientStore'])->name('client.store'); 
    Route::get('/administrators/index', [AdminController::class, 'AdministratorIndex'])->name('administratorIndex');
    Route::get('/administrators/create', [AdminController::class, 'AdministratorCreate'])->name('administratorCreate');
    Route::post('/administrators/store', [AdminController::class, 'AdministratorStore'])->name('administratorStore');
    Route::get('/file/download/{id}', [AdminCandidateController::class, 'FileDownload'])->name('fileDownload');
    Route::post('/candidate/status/update/{id}', [AdminCandidateController::class, 'statusUpdate'])->name('statusUpdate');
    Route::post('/candidate/payment/update/{id}', [AdminPaymentController::class, 'paymentUpdate'])->name('paymentUpdate');
    Route::post('/candidate/qa/update/{id}', [AdminCandidateController::class, 'QAUpdate'])->name('qaUpdate'); 
    Route::post('/candidate/qa/review/{id}', [AdminCandidateController::class, 'QAReview'])->name('qaReviews');
    // Route::post('/candidate/document/verified/{id}', [AdminCandidateController::class, 'VerifyCandidatep'])->name('VerifyCandidatep');
    // Route::get('/client/company/details/{id}', [AdminCandidateController::class, 'VerifyCandidate'])->name('VerifyCandidate');
    Route::get('/candidate/index', [AdminCandidateController::class, 'CandidateIndex'])->name('VerifyCandidate');
    Route::get('/reports', [AdminController::class, 'UserReports'])->name('users.report');  
    Route::get('/reports/get/', [AdminController::class, 'getReports'])->name('users.getReports');
    Route::get('/activity', [AdminController::class, 'UserActivity'])->name('users.activity'); 

    Route::get('/profile', [AdminController::class, 'Profile'])->name('user.profile'); 
    Route::get('/transactions', [AdminController::class, 'getTransaction'])->name('user.transactions');
    Route::post('/transactions/download', [AdminController::class, 'download'])->name('transactions.download');
    Route::post('/profile/update', [AdminController::class, 'StorePersonalInfo'])->name('form_profileUpdate');
    Route::post('/password/update', [AdminController::class, 'UpdatePassword'])->name('form_PasswordeUpdate');
    Route::post('/basic/information/update', [AdminController::class, 'UpdateBusinessInfo'])->name('basic_information');
    Route::post('/contact/information/update', [AdminController::class, 'UpdateContactInfo'])->name('contact_information');
}); 