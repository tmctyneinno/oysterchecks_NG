<?php 


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\{AddressController,  AdminPaymentController, AdverseMediaController, BusinessController, CandidateController, ClientController, IdentityController, SanctionPepController};



Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin', 'auth']], function() { 
    Route::get('/', [AdminController::class, 'Index'])->name('index');
    Route::get('/index', [AdminController::class, 'Index'])->name('index');
    Route::get('/identity/{slug}', [IdentityController::class, 'getVerify'])->name('verify');
    Route::get('/identities/{slug}/{verificationId}', [IdentityController::class, 'verificationReport'])->name('showIdentityReport');
    Route::get('/business/{slug}', [BusinessController::class, 'businessIndex'])->name('businessIndex');
    Route::get('/business/{slug}/{verificationId}', [BusinessController::class, 'businessDetails'])->name('showBusinessReport');


    // ========address routers ===============
    Route::get('/user/address/verification/{slug}', [AddressController::class,'AddressIndex'])->name('addressIndex');
    Route::get('/user/address/verification/{slug}/{addressId?}', [AddressController::class, 'verificationReport'])->name('showAddressReport');
    Route::get('/user/address/verifications/{verification_id}', [AddressController::class, 'ViewCandidateAddresses'])->name('ViewCandidateAddresses');

    Route::get('/candidate', [CandidateController::class, 'getVerify'])->name('candidate.index');
    Route::get('/candidate/index', [CandidateController::class, 'CandidateIndex'])->name('candidate.index');
    Route::get('/candidate/details/{id}', [CandidateController::class, 'CandidateDetails'])->name('candidate.details');
    Route::get('candidate/', [CandidateController::class, 'UserCandidates'])->name('user.candidates');
    Route::get('/clients/candidate/{id}', [CandidateController::class, 'ClientCandidates'])->name('client.candidates');
    Route::get('/user/clients/', [ClientController::class, 'UserClients'])->name('user.clients');
    Route::get('/clients/details/{client_id}', [ClientController::class, 'ClientProfile'])->name('client.details');
    Route::get('/client/activate/{client_id}', [ClientController::class, 'ActivateClientAccount'])->name('client.activateAccount');
    Route::get('/client/suspend/{client_id}', [ClientController::class, 'SuspendClientAccount'])->name('client.suspendAccount');
    Route::get('/clients/create', [ClientController::class, 'createClient'])->name('client.create');
    Route::post('/clients/store', [ClientController::class, 'ClientStore'])->name('client.store');
    Route::get('/administrators/index', [AdminController::class, 'AdministratorIndex'])->name('administratorIndex');
    Route::get('/administrators/create', [AdminController::class, 'AdministratorCreate'])->name('administratorCreate');
    Route::post('/administrators/store', [AdminController::class, 'AdministratorStore'])->name('administratorStore');
    Route::get('/file/download/{id}', [CandidateController::class, 'FileDownload'])->name('fileDownload');
    Route::post('/candidate/status/update/{id}', [CandidateController::class, 'statusUpdate'])->name('statusUpdate');
    Route::post('/candidate/payment/update/{id}', [AdminPaymentController::class, 'paymentUpdate'])->name('paymentUpdate');
    Route::post('/candidate/qa/update/{id}', [CandidateController::class, 'QAUpdate'])->name('qaUpdate');
    Route::post('/candidate/qa/review/{id}', [CandidateController::class, 'QAReview'])->name('qaReviews');
    Route::post('/candidate/document/verified/{id}', [CandidateController::class, 'VerifyCandidate'])->name('VerifyCandidate');
    Route::get('/client/company/details/{id}', [CandidateController::class, 'VerifyCandidate'])->name('VerifyCandidate');
    Route::get('/reports', [AdminController::class, 'UserReports'])->name('report');
    Route::get('/reports/get/', [AdminController::class, 'getReports'])->name('users.getReports');
    Route::get('/profile', [AdminController::class, 'Profile'])->name('profile');
    Route::get('/transactions', [AdminController::class, 'getTransaction'])->name('user.transactions');
    Route::post('/profile/update', [AdminController::class, 'StorePersonalInfo'])->name('form_profileUpdate');
    Route::post('/password/update', [AdminController::class, 'UpdatePassword'])->name('form_PasswordeUpdate');
    Route::post('/basic/information/update', [AdminController::class, 'UpdateBusinessInfo'])->name('basic_information');
    Route::post('/contact/information/update', [AdminController::class, 'UpdateContactInfo'])->name('contact_information');

    Route::get('aml/sanction-pep-screening/{slug}',[SanctionPepController::class, 'SanctionPepIndex'])->name('aml_pep-sanction-list');
Route::get('/aml/sanction-pep-screening/{slug}/check',[SanctionPepController::class, 'SanctionPepCheck'])->name('aml_pep_sanction_check');
Route::post('/aml/sanction-pep-screening/{slug}/verify',[SanctionPepController::class, 'SanctionPepVerify'])->name('aml_pep_sanction_verify');
Route::get('aml/sanction-pep-screening/report/{ref}',[SanctionPepController::class, 'SanctionPepGetReport'])->name('aml_pep_sanction_get_report');

Route::get('aml/adversemedia/{slug}',[AdverseMediaController::class, 'AdverseMediaIndex'])->name('aml_adverse-media-intelligence');
Route::get('aml/adversemedia/{slug}/check',[AdverseMediaController::class, 'AdverseMediaCheck'])->name('aml_adverse_media_check');
Route::post('/aml/adversemedia/{slug}/verify',[AdverseMediaController::class, 'AdverseMediaVerify'])->name('aml_adverse_media_verify');
Route::get('aml/adversemedia/report/{ref}',[AdverseMediaController::class, 'AdverseMediaGetReport'])->name('aml_adverse_media_get_report');
    });