<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\LabSpecializedController;
use App\Http\Controllers\RadiologySpecializedController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/admin', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return view('welcome');
})->name('admin.welcome');

// Public Website Routes (Landing Page System)
Route::name('public.')->group(function () {
    // Landing Page
    Route::get('/', [\App\Http\Controllers\LandingPageController::class, 'index'])->name('landing');
    
    // Doctors
    Route::get('/doctors', [\App\Http\Controllers\LandingPageController::class, 'doctors'])->name('doctors');
    Route::get('/doctors/{id}', [\App\Http\Controllers\LandingPageController::class, 'doctorProfile'])->name('doctors.profile');
    
    // Services
    Route::get('/services', [\App\Http\Controllers\LandingPageController::class, 'services'])->name('services');
    Route::get('/services/{id}', [\App\Http\Controllers\LandingPageController::class, 'serviceDetails'])->name('services.details');
    
    // Booking
    Route::get('/booking', [\App\Http\Controllers\LandingPageController::class, 'bookingForm'])->name('booking.form');
    Route::post('/booking', [\App\Http\Controllers\LandingPageController::class, 'storeBooking'])->name('booking.store');
    Route::get('/booking/success/{appointmentId}', [\App\Http\Controllers\LandingPageController::class, 'bookingSuccess'])->name('booking.success');
    
    // Contact
    Route::get('/contact', [\App\Http\Controllers\LandingPageController::class, 'contact'])->name('contact');
    Route::post('/contact', [\App\Http\Controllers\LandingPageController::class, 'storeContact'])->name('contact.store');
});

// Legacy Public Website Routes (Task 20) - Keeping for backward compatibility
Route::prefix('public')->name('public.')->group(function () {
    // الصفحة الرئيسية للموقع العام
    Route::get('/', [\App\Http\Controllers\PublicWebsiteController::class, 'index'])->name('index');
    
    // قائمة الأطباء
    Route::get('/doctors', [\App\Http\Controllers\PublicWebsiteController::class, 'doctors'])->name('doctors.index');
    Route::get('/doctors/{id}', [\App\Http\Controllers\PublicWebsiteController::class, 'doctorProfile'])->name('doctors.profile');
    
    // حجز المواعيد
    Route::get('/booking/{doctorId?}', [\App\Http\Controllers\PublicWebsiteController::class, 'bookingForm'])->name('booking.form');
    Route::post('/booking', [\App\Http\Controllers\PublicWebsiteController::class, 'processBooking'])->name('booking.process');
    
    // تسجيل الزوار
    Route::post('/register', [\App\Http\Controllers\PublicWebsiteController::class, 'registerVisitor'])->name('register');
    
    // لوحة تحكم الزائر
    Route::get('/dashboard', [\App\Http\Controllers\PublicWebsiteController::class, 'visitorDashboard'])->name('dashboard');
    
    // البحث والمساعدة
    Route::get('/search', [\App\Http\Controllers\PublicWebsiteController::class, 'search'])->name('search');
    Route::get('/available-slots', [\App\Http\Controllers\PublicWebsiteController::class, 'getAvailableSlotsAjax'])->name('available-slots');
});

// Language switching route
Route::get('/lang/{language}', function ($language) {
    if (in_array($language, ['ar', 'en'])) {
        session(['locale' => $language]);
    }
    return redirect()->back();
})->name('language.switch');

// Home route (after login)
Route::get('/home', function () {
    return view('dashboard.main');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Patient Management
    Route::resource('patients', \App\Http\Controllers\PatientController::class);
    Route::post('/patients/{patient}/toggle-status', [\App\Http\Controllers\PatientController::class, 'toggleStatus'])->name('patients.toggle-status');
    Route::post('/patients/search-barcode', [\App\Http\Controllers\PatientController::class, 'searchByBarcode'])->name('patients.search-barcode');
    Route::get('/patients/{patient}/family-members', [\App\Http\Controllers\PatientController::class, 'getFamilyMembers'])->name('patients.family-members');
    Route::get('/families', [\App\Http\Controllers\PatientController::class, 'families'])->name('patients.families');
    Route::get('/families/{familyCode}', [\App\Http\Controllers\PatientController::class, 'showFamily'])->name('patients.show-family');
    
    // Test route for families
    Route::get('/test-families', function() {
        $familiesCount = \App\Models\Patient::whereNotNull('family_code')->whereNull('family_head_id')->count();
        return view('test-families', compact('familiesCount'));
    })->name('test.families');
    
    // Appointment Management
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
    Route::get('/appointments-calendar', [\App\Http\Controllers\AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/doctor-calendar', [\App\Http\Controllers\AppointmentController::class, 'doctorCalendar'])->name('appointments.doctor-calendar');
    Route::get('/appointments-today', [\App\Http\Controllers\AppointmentController::class, 'todayAppointments'])->name('appointments.today');
    Route::get('/available-slots', [\App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])->name('appointments.available-slots');
    Route::patch('/appointments/{appointment}/status', [\App\Http\Controllers\AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::patch('/appointments/{appointment}/move', [\App\Http\Controllers\AppointmentController::class, 'moveAppointment'])->name('appointments.move');
    Route::patch('/appointments/{appointment}/resize', [\App\Http\Controllers\AppointmentController::class, 'resizeAppointment'])->name('appointments.resize');
    Route::post('/appointments/quick-create', [\App\Http\Controllers\AppointmentController::class, 'quickCreate'])->name('appointments.quick-create');
    Route::get('/doctor-working-hours', [\App\Http\Controllers\AppointmentController::class, 'getDoctorWorkingHours'])->name('appointments.doctor-working-hours');
    
    // Doctor Management
    Route::resource('doctors', \App\Http\Controllers\DoctorController::class);
    Route::patch('/doctors/{doctor}/toggle-availability', [\App\Http\Controllers\DoctorController::class, 'toggleAvailability'])->name('doctors.toggle-availability');
    Route::get('/doctors/{doctor}/working-hours', [\App\Http\Controllers\DoctorController::class, 'workingHours'])->name('doctors.working-hours');
    Route::patch('/doctors/{doctor}/working-hours', [\App\Http\Controllers\DoctorController::class, 'updateWorkingHours'])->name('doctors.update-working-hours');
    
    // Doctor Dashboard (for logged-in doctors)
    Route::get('/doctor-dashboard', [\App\Http\Controllers\DoctorController::class, 'dashboard'])->name('doctor.dashboard');
    
    // Doctor Certificates Management
    Route::resource('doctors.certificates', \App\Http\Controllers\DoctorCertificateController::class)->except(['index']);
    Route::get('/doctor-certificates', [\App\Http\Controllers\DoctorCertificateController::class, 'index'])->name('doctor-certificates.index');
    Route::patch('/doctor-certificates/{certificate}/verify', [\App\Http\Controllers\DoctorCertificateController::class, 'verify'])->name('doctor-certificates.verify');
    Route::patch('/doctor-certificates/{certificate}/unverify', [\App\Http\Controllers\DoctorCertificateController::class, 'unverify'])->name('doctor-certificates.unverify');
    Route::get('/doctor-certificates/{certificate}/download', [\App\Http\Controllers\DoctorCertificateController::class, 'download'])->name('doctor-certificates.download');
    Route::get('/doctor-certificates/expiring-soon', [\App\Http\Controllers\DoctorCertificateController::class, 'expiringSoon'])->name('doctor-certificates.expiring-soon');
    Route::post('/doctor-certificates/bulk-verify', [\App\Http\Controllers\DoctorCertificateController::class, 'bulkVerify'])->name('doctor-certificates.bulk-verify');
    Route::get('/doctor-certificates/statistics', [\App\Http\Controllers\DoctorCertificateController::class, 'statistics'])->name('doctor-certificates.statistics');
    
    // Doctor Services Management
    Route::resource('doctors.services', \App\Http\Controllers\DoctorServiceController::class)->except(['index']);
    Route::get('/doctor-services', [\App\Http\Controllers\DoctorServiceController::class, 'index'])->name('doctor-services.index');
    Route::patch('/doctor-services/{service}/toggle-status', [\App\Http\Controllers\DoctorServiceController::class, 'toggleStatus'])->name('doctor-services.toggle-status');
    Route::post('/doctors/{doctor}/services/{service}/duplicate', [\App\Http\Controllers\DoctorServiceController::class, 'duplicate'])->name('doctor-services.duplicate');
    Route::post('/doctors/{doctor}/services/sort', [\App\Http\Controllers\DoctorServiceController::class, 'updateSortOrder'])->name('doctor-services.sort');
    Route::get('/doctor-services/by-category', [\App\Http\Controllers\DoctorServiceController::class, 'getByCategory'])->name('doctor-services.by-category');
    Route::post('/doctor-services/bulk-action', [\App\Http\Controllers\DoctorServiceController::class, 'bulkAction'])->name('doctor-services.bulk-action');
    Route::get('/doctor-services/statistics', [\App\Http\Controllers\DoctorServiceController::class, 'statistics'])->name('doctor-services.statistics');
    
    // Patient Reviews Management
    Route::resource('reviews', \App\Http\Controllers\PatientReviewController::class);
    Route::patch('/reviews/{review}/approve', [\App\Http\Controllers\PatientReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('/reviews/{review}/reject', [\App\Http\Controllers\PatientReviewController::class, 'reject'])->name('reviews.reject');
    Route::patch('/reviews/{review}/hide', [\App\Http\Controllers\PatientReviewController::class, 'hide'])->name('reviews.hide');
    Route::patch('/reviews/{review}/toggle-feature', [\App\Http\Controllers\PatientReviewController::class, 'toggleFeature'])->name('reviews.toggle-feature');
    Route::get('/doctors/{doctor}/reviews', [\App\Http\Controllers\PatientReviewController::class, 'doctorReviews'])->name('doctors.reviews');
    Route::get('/reviews/statistics', [\App\Http\Controllers\PatientReviewController::class, 'statistics'])->name('reviews.statistics');
    Route::post('/reviews/bulk-action', [\App\Http\Controllers\PatientReviewController::class, 'bulkAction'])->name('reviews.bulk-action');
    
    // Dental Department Management
    Route::prefix('dental')->name('dental.')->group(function () {
        // Dental Treatments
        Route::resource('treatments', \App\Http\Controllers\DentalTreatmentController::class);
        Route::patch('/treatments/{treatment}/status', [\App\Http\Controllers\DentalTreatmentController::class, 'updateStatus'])->name('treatments.update-status');
        Route::post('/treatments/{treatment}/generate-plan', [\App\Http\Controllers\DentalTreatmentController::class, 'generatePlan'])->name('treatments.generate-plan');
        Route::get('/treatments/statistics', [\App\Http\Controllers\DentalTreatmentController::class, 'statistics'])->name('treatments.statistics');
        Route::post('/treatments/bulk-action', [\App\Http\Controllers\DentalTreatmentController::class, 'bulkAction'])->name('treatments.bulk-action');
        Route::get('/treatments/export', [\App\Http\Controllers\DentalTreatmentController::class, 'export'])->name('treatments.export');
        
        // Dental Sessions
        Route::get('/sessions/calendar', [\App\Http\Controllers\DentalSessionController::class, 'calendarView'])->name('sessions.calendar');
        Route::get('/sessions/calendar-data', [\App\Http\Controllers\DentalSessionController::class, 'calendar'])->name('sessions.calendar-data');
        Route::resource('sessions', \App\Http\Controllers\DentalSessionController::class);
        Route::patch('/sessions/{session}/complete', [\App\Http\Controllers\DentalSessionController::class, 'markCompleted'])->name('sessions.complete');
        Route::patch('/sessions/{session}/cancel', [\App\Http\Controllers\DentalSessionController::class, 'markCancelled'])->name('sessions.cancel');
        Route::post('/sessions/bulk-action', [\App\Http\Controllers\DentalSessionController::class, 'bulkAction'])->name('sessions.bulk-action');
        
        // Dental Installments
        Route::get('/installments/overdue-list', [\App\Http\Controllers\DentalInstallmentController::class, 'overdue'])->name('installments.overdue-list');
        Route::get('/installments/calendar', [\App\Http\Controllers\DentalInstallmentController::class, 'calendar'])->name('installments.calendar');
        Route::post('/installments/generate', [\App\Http\Controllers\DentalInstallmentController::class, 'generateInstallments'])->name('installments.generate');
        Route::post('/installments/bulk-action', [\App\Http\Controllers\DentalInstallmentController::class, 'bulkAction'])->name('installments.bulk-action');
        Route::resource('installments', \App\Http\Controllers\DentalInstallmentController::class);
        Route::patch('/installments/{installment}/mark-paid', [\App\Http\Controllers\DentalInstallmentController::class, 'markPaid'])->name('installments.mark-paid');
        Route::patch('/installments/{installment}/overdue', [\App\Http\Controllers\DentalInstallmentController::class, 'markOverdue'])->name('installments.overdue');
        Route::post('/installments/{installment}/reminder', [\App\Http\Controllers\DentalInstallmentController::class, 'sendReminder'])->name('installments.reminder');
    });
    
    // Laboratory Management
    Route::resource('lab', \App\Http\Controllers\LabController::class);
    Route::patch('/lab/{lab}/status', [\App\Http\Controllers\LabController::class, 'updateStatus'])->name('lab.update-status');
    Route::get('/lab/{lab}/results-form', [\App\Http\Controllers\LabController::class, 'getResultsForm'])->name('lab.results-form');
    Route::post('/lab/{lab}/results', [\App\Http\Controllers\LabController::class, 'addResults'])->name('lab.add-results');
    Route::patch('/lab/{lab}/verify', [\App\Http\Controllers\LabController::class, 'verifyResults'])->name('lab.verify-results');
    Route::get('/lab-statistics', [\App\Http\Controllers\LabController::class, 'statistics'])->name('lab.statistics');
    Route::get('/lab-today', [\App\Http\Controllers\LabController::class, 'todayOrders'])->name('lab.today');
    
    // Lab Tests Management
    Route::resource('lab-tests', \App\Http\Controllers\LabTestController::class);
    Route::patch('/lab-tests/{labTest}/toggle-status', [\App\Http\Controllers\LabTestController::class, 'toggleStatus'])->name('lab-tests.toggle-status');
    Route::get('/lab-tests/by-category', [\App\Http\Controllers\LabTestController::class, 'getByCategory'])->name('lab-tests.by-category');
    Route::post('/lab-tests/bulk-action', [\App\Http\Controllers\LabTestController::class, 'bulkAction'])->name('lab-tests.bulk-action');
    Route::get('/lab-tests/export', [\App\Http\Controllers\LabTestController::class, 'export'])->name('lab-tests.export');
    
    // Radiology Management
    Route::resource('radiology', \App\Http\Controllers\RadiologyController::class);
    Route::patch('/radiology/{radiology}/status', [\App\Http\Controllers\RadiologyController::class, 'updateStatus'])->name('radiology.update-status');
    Route::post('/radiology/{radiology}/report', [\App\Http\Controllers\RadiologyController::class, 'createReport'])->name('radiology.create-report');
    Route::get('/radiology/{radiology}/report-form', [\App\Http\Controllers\RadiologyController::class, 'showReportForm'])->name('radiology.report-form');
    Route::patch('/radiology-reports/{report}/verify', [\App\Http\Controllers\RadiologyController::class, 'verifyReport'])->name('radiology.verify-report');
    Route::patch('/radiology-reports/{report}/finalize', [\App\Http\Controllers\RadiologyController::class, 'finalizeReport'])->name('radiology.finalize-report');
    Route::get('/radiology-statistics', [\App\Http\Controllers\RadiologyController::class, 'statistics'])->name('radiology.statistics');
    Route::get('/radiology-today', [\App\Http\Controllers\RadiologyController::class, 'todayOrders'])->name('radiology.today');
    Route::get('/radiology-schedule', [\App\Http\Controllers\RadiologyController::class, 'todaySchedule'])->name('radiology.schedule');
    Route::get('/radiology-urgent', [\App\Http\Controllers\RadiologyController::class, 'urgentFindings'])->name('radiology.urgent');
    
    // Radiology Studies Management
    Route::resource('radiology-studies', \App\Http\Controllers\RadiologyStudyController::class);
    Route::patch('/radiology-studies/{radiologyStudy}/toggle-status', [\App\Http\Controllers\RadiologyStudyController::class, 'toggleStatus'])->name('radiology-studies.toggle-status');
    Route::get('/radiology-studies/by-category', [\App\Http\Controllers\RadiologyStudyController::class, 'getByCategory'])->name('radiology-studies.by-category');
    Route::post('/radiology-studies/bulk-action', [\App\Http\Controllers\RadiologyStudyController::class, 'bulkAction'])->name('radiology-studies.bulk-action');
    Route::get('/radiology-studies/export', [\App\Http\Controllers\RadiologyStudyController::class, 'export'])->name('radiology-studies.export');
    Route::get('/radiology-studies-statistics', [\App\Http\Controllers\RadiologyStudyController::class, 'statistics'])->name('radiology-studies.statistics');
    
    // Room and Bed Management
    Route::resource('rooms', \App\Http\Controllers\RoomController::class);
    Route::post('/rooms/{room}/assign-patient', [\App\Http\Controllers\RoomController::class, 'assignPatient'])->name('rooms.assign-patient');
    Route::post('/rooms/{room}/discharge-patient', [\App\Http\Controllers\RoomController::class, 'dischargePatient'])->name('rooms.discharge-patient');
    Route::patch('/rooms/{room}/mark-cleaned', [\App\Http\Controllers\RoomController::class, 'markCleaned'])->name('rooms.mark-cleaned');
    Route::patch('/rooms/{room}/mark-maintenance', [\App\Http\Controllers\RoomController::class, 'markMaintenance'])->name('rooms.mark-maintenance');
    Route::patch('/rooms/{room}/complete-maintenance', [\App\Http\Controllers\RoomController::class, 'completeMaintenance'])->name('rooms.complete-maintenance');
    Route::get('/rooms-dashboard', [\App\Http\Controllers\RoomController::class, 'dashboard'])->name('rooms.dashboard');
    Route::get('/available-rooms', [\App\Http\Controllers\RoomController::class, 'getAvailableRooms'])->name('rooms.available');
    
    Route::resource('beds', \App\Http\Controllers\BedController::class);
    Route::post('/beds/{bed}/assign-patient', [\App\Http\Controllers\BedController::class, 'assignPatient'])->name('beds.assign-patient');
    Route::post('/beds/{bed}/discharge-patient', [\App\Http\Controllers\BedController::class, 'dischargePatient'])->name('beds.discharge-patient');
    Route::patch('/beds/{bed}/mark-cleaned', [\App\Http\Controllers\BedController::class, 'markCleaned'])->name('beds.mark-cleaned');
    Route::patch('/beds/{bed}/mark-maintenance', [\App\Http\Controllers\BedController::class, 'markMaintenance'])->name('beds.mark-maintenance');
    Route::patch('/beds/{bed}/complete-maintenance', [\App\Http\Controllers\BedController::class, 'completeMaintenance'])->name('beds.complete-maintenance');
    Route::patch('/beds/{bed}/toggle-status', [\App\Http\Controllers\BedController::class, 'toggleStatus'])->name('beds.toggle-status');
    Route::get('/available-beds', [\App\Http\Controllers\BedController::class, 'getAvailableBeds'])->name('beds.available');
    
    // Surgery Management System
    Route::resource('surgeries', \App\Http\Controllers\SurgeryController::class);
    Route::get('/surgeries-dashboard', [\App\Http\Controllers\SurgeryController::class, 'dashboard'])->name('surgeries.dashboard');
    Route::get('/surgeries-today', [\App\Http\Controllers\SurgeryController::class, 'today'])->name('surgeries.today');
    Route::patch('/surgeries/{surgery}/start', [\App\Http\Controllers\SurgeryController::class, 'start'])->name('surgeries.start');
    Route::patch('/surgeries/{surgery}/complete', [\App\Http\Controllers\SurgeryController::class, 'complete'])->name('surgeries.complete');
    Route::patch('/surgeries/{surgery}/cancel', [\App\Http\Controllers\SurgeryController::class, 'cancel'])->name('surgeries.cancel');
    Route::patch('/surgeries/{surgery}/postpone', [\App\Http\Controllers\SurgeryController::class, 'postpone'])->name('surgeries.postpone');
    Route::get('/surgeries/check-availability', [\App\Http\Controllers\SurgeryController::class, 'checkAvailability'])->name('surgeries.check-availability');
    Route::get('/surgical-procedures/{procedure}/details', [\App\Http\Controllers\SurgeryController::class, 'getProcedureDetails'])->name('surgeries.procedure-details');
    
    // Surgical Procedures Management
    Route::resource('surgical-procedures', \App\Http\Controllers\SurgicalProcedureController::class);
    Route::patch('/surgical-procedures/{surgicalProcedure}/toggle-status', [\App\Http\Controllers\SurgicalProcedureController::class, 'toggleStatus'])->name('surgical-procedures.toggle-status');
    Route::get('/surgical-procedures/search', [\App\Http\Controllers\SurgicalProcedureController::class, 'search'])->name('surgical-procedures.search');
    Route::get('/surgical-procedures/{surgicalProcedure}/details', [\App\Http\Controllers\SurgicalProcedureController::class, 'getDetails'])->name('surgical-procedures.details');
    
    // Operating Rooms Management
    Route::resource('operating-rooms', \App\Http\Controllers\OperatingRoomController::class);
    Route::get('/operating-rooms-dashboard', [\App\Http\Controllers\OperatingRoomController::class, 'dashboard'])->name('operating-rooms.dashboard');
    Route::patch('/operating-rooms/{operatingRoom}/mark-cleaned', [\App\Http\Controllers\OperatingRoomController::class, 'markCleaned'])->name('operating-rooms.mark-cleaned');
    Route::patch('/operating-rooms/{operatingRoom}/mark-maintenance', [\App\Http\Controllers\OperatingRoomController::class, 'markMaintenance'])->name('operating-rooms.mark-maintenance');
    Route::patch('/operating-rooms/{operatingRoom}/complete-maintenance', [\App\Http\Controllers\OperatingRoomController::class, 'completeMaintenance'])->name('operating-rooms.complete-maintenance');
    Route::patch('/operating-rooms/{operatingRoom}/start-setup', [\App\Http\Controllers\OperatingRoomController::class, 'startSetup'])->name('operating-rooms.start-setup');
    Route::patch('/operating-rooms/{operatingRoom}/complete-setup', [\App\Http\Controllers\OperatingRoomController::class, 'completeSetup'])->name('operating-rooms.complete-setup');
    Route::patch('/operating-rooms/{operatingRoom}/reserve-emergency', [\App\Http\Controllers\OperatingRoomController::class, 'reserveForEmergency'])->name('operating-rooms.reserve-emergency');
    Route::patch('/operating-rooms/{operatingRoom}/release-emergency', [\App\Http\Controllers\OperatingRoomController::class, 'releaseFromEmergency'])->name('operating-rooms.release-emergency');
    Route::get('/operating-rooms/{operatingRoom}/schedule', [\App\Http\Controllers\OperatingRoomController::class, 'getSchedule'])->name('operating-rooms.schedule');
    Route::get('/operating-rooms/{operatingRoom}/availability', [\App\Http\Controllers\OperatingRoomController::class, 'checkAvailability'])->name('operating-rooms.availability');
    Route::get('/available-operating-rooms', [\App\Http\Controllers\OperatingRoomController::class, 'getAvailableRooms'])->name('operating-rooms.available');
    
    // Financial Management Routes
    Route::prefix('financial')->name('financial.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DoctorFinancialController::class, 'index'])->name('index');
        Route::get('/doctors/{doctor}', [\App\Http\Controllers\DoctorFinancialController::class, 'show'])->name('show');
        Route::get('/doctors/{doctor}/transactions', [\App\Http\Controllers\DoctorFinancialController::class, 'transactions'])->name('transactions');
        Route::post('/doctors/{doctor}/transactions', [\App\Http\Controllers\DoctorFinancialController::class, 'createTransaction'])->name('create-transaction');
        Route::patch('/transactions/{transaction}/process', [\App\Http\Controllers\DoctorFinancialController::class, 'processWithdrawal'])->name('process-withdrawal');
        Route::get('/doctors/{doctor}/commissions', [\App\Http\Controllers\DoctorFinancialController::class, 'commissions'])->name('commissions');
        Route::post('/doctors/{doctor}/commissions', [\App\Http\Controllers\DoctorFinancialController::class, 'updateCommission'])->name('update-commission');
        Route::get('/doctors/{doctor}/report', [\App\Http\Controllers\DoctorFinancialController::class, 'report'])->name('report');
    });
    
    // Advanced Billing System Routes
    Route::prefix('advanced-billing')->name('advanced-billing.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdvancedBillingController::class, 'dashboard'])->name('dashboard');
        Route::get('/cash-invoices', [\App\Http\Controllers\AdvancedBillingController::class, 'cashInvoices'])->name('cash-invoices');
        Route::get('/credit-invoices', [\App\Http\Controllers\AdvancedBillingController::class, 'creditInvoices'])->name('credit-invoices');
        Route::get('/payment-tracking', [\App\Http\Controllers\AdvancedBillingController::class, 'paymentTracking'])->name('payment-tracking');
        Route::get('/overdue-management', [\App\Http\Controllers\AdvancedBillingController::class, 'overdueManagement'])->name('overdue-management');
        Route::get('/financial-reports', [\App\Http\Controllers\AdvancedBillingController::class, 'financialReports'])->name('financial-reports');
        
        // AJAX endpoints
        Route::post('/invoices/{invoice}/quick-cash-payment', [\App\Http\Controllers\AdvancedBillingController::class, 'processQuickCashPayment'])->name('quick-cash-payment');
        Route::post('/invoices/{invoice}/send-reminder', [\App\Http\Controllers\AdvancedBillingController::class, 'sendPaymentReminder'])->name('send-reminder');
        Route::post('/bulk-overdue-actions', [\App\Http\Controllers\AdvancedBillingController::class, 'bulkOverdueActions'])->name('bulk-overdue-actions');
    });

    // Billing System Routes
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::get('/invoices-dashboard', [\App\Http\Controllers\InvoiceController::class, 'dashboard'])->name('invoices.dashboard');
    Route::patch('/invoices/{invoice}/finalize', [\App\Http\Controllers\InvoiceController::class, 'finalize'])->name('invoices.finalize');
    Route::patch('/invoices/{invoice}/cancel', [\App\Http\Controllers\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/items', [\App\Http\Controllers\InvoiceController::class, 'getItems'])->name('invoices.items');
    Route::post('/invoices/mark-overdue', [\App\Http\Controllers\InvoiceController::class, 'markOverdue'])->name('invoices.mark-overdue');
    
    // Payment Management Routes
    Route::resource('payments', \App\Http\Controllers\PaymentController::class);
    Route::get('/payments-dashboard', [\App\Http\Controllers\PaymentController::class, 'dashboard'])->name('payments.dashboard');
    Route::patch('/payments/{payment}/complete', [\App\Http\Controllers\PaymentController::class, 'complete'])->name('payments.complete');
    Route::patch('/payments/{payment}/fail', [\App\Http\Controllers\PaymentController::class, 'fail'])->name('payments.fail');
    Route::patch('/payments/{payment}/cancel', [\App\Http\Controllers\PaymentController::class, 'cancel'])->name('payments.cancel');
    Route::post('/payments/{payment}/refund', [\App\Http\Controllers\PaymentController::class, 'refund'])->name('payments.refund');
    Route::patch('/payments/{payment}/approve', [\App\Http\Controllers\PaymentController::class, 'approve'])->name('payments.approve');
    Route::patch('/payments/{payment}/clear', [\App\Http\Controllers\PaymentController::class, 'clear'])->name('payments.clear');
    Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/invoices/{invoice}/payment-details', [\App\Http\Controllers\PaymentController::class, 'getInvoiceDetails'])->name('payments.invoice-details');
    
    // Insurance Company Management Routes
    Route::resource('insurance-companies', \App\Http\Controllers\InsuranceCompanyController::class);
    Route::get('/insurance-dashboard', [\App\Http\Controllers\InsuranceCompanyController::class, 'dashboard'])->name('insurance-companies.dashboard');
    Route::patch('/insurance-companies/{insuranceCompany}/suspend', [\App\Http\Controllers\InsuranceCompanyController::class, 'suspend'])->name('insurance-companies.suspend');
    Route::patch('/insurance-companies/{insuranceCompany}/activate', [\App\Http\Controllers\InsuranceCompanyController::class, 'activate'])->name('insurance-companies.activate');
    Route::patch('/insurance-companies/{insuranceCompany}/terminate', [\App\Http\Controllers\InsuranceCompanyController::class, 'terminate'])->name('insurance-companies.terminate');
    Route::patch('/insurance-companies/{insuranceCompany}/renew', [\App\Http\Controllers\InsuranceCompanyController::class, 'renew'])->name('insurance-companies.renew');
    Route::post('/insurance-companies/{insuranceCompany}/calculate-coverage', [\App\Http\Controllers\InsuranceCompanyController::class, 'calculateCoverage'])->name('insurance-companies.calculate-coverage');
    Route::get('/insurance-companies-expiring', [\App\Http\Controllers\InsuranceCompanyController::class, 'expiringSoon'])->name('insurance-companies.expiring-soon');
    Route::get('/insurance-companies-active', [\App\Http\Controllers\InsuranceCompanyController::class, 'getActive'])->name('insurance-companies.active');
    
    // Insurance Policy Management Routes
    Route::resource('insurance-policies', \App\Http\Controllers\InsurancePolicyController::class);
    Route::patch('/insurance-policies/{insurancePolicy}/toggle-status', [\App\Http\Controllers\InsurancePolicyController::class, 'toggleStatus'])->name('insurance-policies.toggle-status');
    Route::post('/insurance-policies/{insurancePolicy}/calculate-coverage', [\App\Http\Controllers\InsurancePolicyController::class, 'calculateCoverage'])->name('insurance-policies.calculate-coverage');
    Route::get('/insurance-policies-by-company', [\App\Http\Controllers\InsurancePolicyController::class, 'getByCompany'])->name('insurance-policies.by-company');
    Route::get('/insurance-policies-expiring', [\App\Http\Controllers\InsurancePolicyController::class, 'expiringSoon'])->name('insurance-policies.expiring-soon');
    Route::post('/insurance-policies/bulk-action', [\App\Http\Controllers\InsurancePolicyController::class, 'bulkAction'])->name('insurance-policies.bulk-action');
    Route::get('/insurance-policies-statistics', [\App\Http\Controllers\InsurancePolicyController::class, 'statistics'])->name('insurance-policies.statistics');
    
    // Insurance Claims Management Routes
    Route::resource('insurance-claims', \App\Http\Controllers\InsuranceClaimController::class);
    Route::get('/insurance-claims-dashboard', [\App\Http\Controllers\InsuranceClaimController::class, 'dashboard'])->name('insurance-claims.dashboard');
    Route::post('/insurance-claims/{insuranceClaim}/submit', [\App\Http\Controllers\InsuranceClaimController::class, 'submit'])->name('insurance-claims.submit');
    Route::post('/insurance-claims/{insuranceClaim}/start-review', [\App\Http\Controllers\InsuranceClaimController::class, 'startReview'])->name('insurance-claims.start-review');
    Route::post('/insurance-claims/{insuranceClaim}/approve', [\App\Http\Controllers\InsuranceClaimController::class, 'approve'])->name('insurance-claims.approve');
    Route::post('/insurance-claims/{insuranceClaim}/reject', [\App\Http\Controllers\InsuranceClaimController::class, 'reject'])->name('insurance-claims.reject');
    Route::post('/insurance-claims/{insuranceClaim}/record-payment', [\App\Http\Controllers\InsuranceClaimController::class, 'recordPayment'])->name('insurance-claims.record-payment');
    Route::post('/insurance-claims/{insuranceClaim}/cancel', [\App\Http\Controllers\InsuranceClaimController::class, 'cancel'])->name('insurance-claims.cancel');
    Route::post('/insurance-claims/create-from-invoice', [\App\Http\Controllers\InsuranceClaimController::class, 'createFromInvoice'])->name('insurance-claims.create-from-invoice');
    Route::post('/insurance-claims/bulk-action', [\App\Http\Controllers\InsuranceClaimController::class, 'bulkAction'])->name('insurance-claims.bulk-action');
    
    // Medical Records Management
    Route::resource('medical-records', \App\Http\Controllers\MedicalRecordController::class);
    Route::get('/patients/{patient}/medical-history', [\App\Http\Controllers\MedicalRecordController::class, 'patientHistory'])->name('medical-records.patient-history');
    Route::get('/doctors/{doctor}/medical-records', [\App\Http\Controllers\MedicalRecordController::class, 'doctorRecords'])->name('medical-records.doctor-records');
    Route::get('/medical-record-attachments/{attachment}/download', [\App\Http\Controllers\MedicalRecordController::class, 'downloadAttachment'])->name('medical-record-attachments.download');
    Route::delete('/medical-record-attachments/{attachment}', [\App\Http\Controllers\MedicalRecordController::class, 'deleteAttachment'])->name('medical-record-attachments.delete');
    
    // Prescriptions Management
    Route::resource('prescriptions', \App\Http\Controllers\PrescriptionController::class);
    Route::get('/patients/{patient}/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'patientPrescriptions'])->name('prescriptions.patient-prescriptions');
    Route::get('/doctors/{doctor}/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'doctorPrescriptions'])->name('prescriptions.doctor-prescriptions');
    Route::patch('/prescriptions/{prescription}/complete', [\App\Http\Controllers\PrescriptionController::class, 'markCompleted'])->name('prescriptions.complete');
    Route::patch('/prescriptions/{prescription}/cancel', [\App\Http\Controllers\PrescriptionController::class, 'cancel'])->name('prescriptions.cancel');
    Route::get('/prescriptions/{prescription}/print', [\App\Http\Controllers\PrescriptionController::class, 'print'])->name('prescriptions.print');
    
    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // User management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('/users/{user}/remove-role', [UserController::class, 'removeRole'])->name('users.remove-role');
    });
});

// API routes for mobile app
Route::prefix('api')->group(function () {
    Route::post('/login', [AuthController::class, 'apiLogin']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'apiLogout']);
        Route::get('/user', function () {
            return response()->json([
                'user' => auth()->user()->load('roles', 'department'),
                'permissions' => auth()->user()->getAllPermissions()->pluck('name')
            ]);
        });
        
        // Patient API routes
        Route::apiResource('patients', \App\Http\Controllers\PatientController::class)->names([
            'index' => 'api.patients.index',
            'store' => 'api.patients.store',
            'show' => 'api.patients.show',
            'update' => 'api.patients.update',
            'destroy' => 'api.patients.destroy'
        ]);
        Route::post('/patients/search-barcode', [\App\Http\Controllers\PatientController::class, 'searchByBarcode'])->name('api.patients.search-barcode');
        Route::get('/patients/{patient}/family-members', [\App\Http\Controllers\PatientController::class, 'getFamilyMembers'])->name('api.patients.family-members');
        
        // Appointment API routes
        Route::apiResource('appointments', \App\Http\Controllers\AppointmentController::class)->names([
            'index' => 'api.appointments.index',
            'store' => 'api.appointments.store',
            'show' => 'api.appointments.show',
            'update' => 'api.appointments.update',
            'destroy' => 'api.appointments.destroy'
        ]);
        Route::get('/appointments-calendar', [\App\Http\Controllers\AppointmentController::class, 'calendar'])->name('api.appointments.calendar');
        Route::get('/available-slots', [\App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])->name('api.appointments.available-slots');
        Route::patch('/appointments/{appointment}/status', [\App\Http\Controllers\AppointmentController::class, 'updateStatus'])->name('api.appointments.update-status');
    });
    
    // Shift Management Routes
    Route::get('/shifts/dashboard', [\App\Http\Controllers\ShiftController::class, 'dashboard'])->name('shifts.dashboard');
    Route::get('/shifts/calendar', [\App\Http\Controllers\ShiftController::class, 'calendar'])->name('shifts.calendar');
    Route::get('/shifts/available-slots', [\App\Http\Controllers\ShiftController::class, 'getAvailableSlots'])->name('shifts.available-slots');
    Route::post('/shifts/{shift}/start', [\App\Http\Controllers\ShiftController::class, 'start'])->name('shifts.start');
    Route::post('/shifts/{shift}/end', [\App\Http\Controllers\ShiftController::class, 'end'])->name('shifts.end');
    Route::post('/shifts/{shift}/cancel', [\App\Http\Controllers\ShiftController::class, 'cancel'])->name('shifts.cancel');
    Route::post('/shifts/{shift}/no-show', [\App\Http\Controllers\ShiftController::class, 'markNoShow'])->name('shifts.no-show');
    Route::post('/shifts/{shift}/verify-cash', [\App\Http\Controllers\ShiftController::class, 'verifyCash'])->name('shifts.verify-cash');
    Route::resource('shifts', \App\Http\Controllers\ShiftController::class);
    
    // Cash Register Management Routes
    Route::get('/cash-registers/dashboard', [\App\Http\Controllers\CashRegisterController::class, 'dashboard'])->name('cash-registers.dashboard');
    Route::get('/cash-registers/reconciliation-report', [\App\Http\Controllers\CashRegisterController::class, 'reconciliationReport'])->name('cash-registers.reconciliation-report');
    Route::get('/cash-registers/available', [\App\Http\Controllers\CashRegisterController::class, 'getAvailableRegisters'])->name('cash-registers.available');
    Route::post('/cash-registers/{cashRegister}/open', [\App\Http\Controllers\CashRegisterController::class, 'open'])->name('cash-registers.open');
    Route::post('/cash-registers/{cashRegister}/close', [\App\Http\Controllers\CashRegisterController::class, 'close'])->name('cash-registers.close');
    Route::post('/cash-registers/{cashRegister}/reconcile', [\App\Http\Controllers\CashRegisterController::class, 'reconcile'])->name('cash-registers.reconcile');
    Route::post('/cash-registers/{cashRegister}/adjust', [\App\Http\Controllers\CashRegisterController::class, 'adjust'])->name('cash-registers.adjust');
    Route::post('/cash-registers/{cashRegister}/maintenance', [\App\Http\Controllers\CashRegisterController::class, 'setMaintenance'])->name('cash-registers.maintenance');
    Route::post('/cash-registers/{cashRegister}/activate', [\App\Http\Controllers\CashRegisterController::class, 'activate'])->name('cash-registers.activate');
    Route::post('/cash-registers/{cashRegister}/deactivate', [\App\Http\Controllers\CashRegisterController::class, 'deactivate'])->name('cash-registers.deactivate');
    Route::resource('cash-registers', \App\Http\Controllers\CashRegisterController::class);
    
    // Shift Reports Routes
    Route::resource('shift-reports', \App\Http\Controllers\ShiftReportController::class);
    Route::post('/shift-reports/{shiftReport}/review', [\App\Http\Controllers\ShiftReportController::class, 'review'])->name('shift-reports.review');
    Route::post('/shift-reports/{shiftReport}/approve', [\App\Http\Controllers\ShiftReportController::class, 'approve'])->name('shift-reports.approve');
    Route::post('/shift-reports/{shiftReport}/reject', [\App\Http\Controllers\ShiftReportController::class, 'reject'])->name('shift-reports.reject');
    
    // Shift Handovers Routes
    Route::resource('shift-handovers', \App\Http\Controllers\ShiftHandoverController::class);
    Route::post('/shift-handovers/{shiftHandover}/start', [\App\Http\Controllers\ShiftHandoverController::class, 'start'])->name('shift-handovers.start');
    Route::post('/shift-handovers/{shiftHandover}/complete', [\App\Http\Controllers\ShiftHandoverController::class, 'complete'])->name('shift-handovers.complete');
    Route::post('/shift-handovers/{shiftHandover}/dispute', [\App\Http\Controllers\ShiftHandoverController::class, 'dispute'])->name('shift-handovers.dispute');
    Route::post('/shift-handovers/{shiftHandover}/resolve', [\App\Http\Controllers\ShiftHandoverController::class, 'resolve'])->name('shift-handovers.resolve');
    Route::post('/shift-handovers/{shiftHandover}/witness', [\App\Http\Controllers\ShiftHandoverController::class, 'addWitness'])->name('shift-handovers.witness');
    
    // Staff Productivity Routes
    Route::resource('staff-productivity', \App\Http\Controllers\StaffProductivityController::class);
    Route::post('/staff-productivity/{staffProductivity}/evaluate', [\App\Http\Controllers\StaffProductivityController::class, 'evaluate'])->name('staff-productivity.evaluate');
});
// Advanced User Management Routes
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::resource('advanced-users', \App\Http\Controllers\AdvancedUserController::class);
    
    // إدارة الأدوار المتقدمة
    Route::get('advanced-users/{user}/manage-roles', [\App\Http\Controllers\AdvancedUserController::class, 'manageRoles'])
         ->name('advanced-users.manage-roles')
         ->middleware('permission:users.manage');
    
    Route::post('advanced-users/{user}/assign-role', [\App\Http\Controllers\AdvancedUserController::class, 'assignRole'])
         ->name('advanced-users.assign-role')
         ->middleware('permission:users.manage');
    
    Route::patch('advanced-users/{user}/activate-role/{userRole}', [\App\Http\Controllers\AdvancedUserController::class, 'activateRole'])
         ->name('advanced-users.activate-role')
         ->middleware('permission:users.manage');
    
    Route::patch('advanced-users/{user}/deactivate-role/{userRole}', [\App\Http\Controllers\AdvancedUserController::class, 'deactivateRole'])
         ->name('advanced-users.deactivate-role')
         ->middleware('permission:users.manage');
    
    Route::delete('advanced-users/{user}/remove-role/{userRole}', [\App\Http\Controllers\AdvancedUserController::class, 'removeRole'])
         ->name('advanced-users.remove-role')
         ->middleware('permission:users.manage');
    
    Route::delete('advanced-users/{user}/revoke-role/{userRole}', [\App\Http\Controllers\AdvancedUserController::class, 'revokeRole'])
         ->name('advanced-users.revoke-role')
         ->middleware('permission:users.manage');
    
    // تفويض الصلاحيات
    Route::post('advanced-users/{user}/delegate-permissions', [\App\Http\Controllers\AdvancedUserController::class, 'delegatePermissions'])
         ->name('advanced-users.delegate-permissions')
         ->middleware('permission:delegation.temporary');
});

// Roles Management Routes
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::post('roles/{role}/assign-permissions', [\App\Http\Controllers\RoleController::class, 'assignPermissions'])
         ->name('roles.assign-permissions')
         ->middleware('permission:users.manage');
    Route::delete('roles/{role}/revoke-permission/{permission}', [\App\Http\Controllers\RoleController::class, 'revokePermission'])
         ->name('roles.revoke-permission')
         ->middleware('permission:users.manage');
});

// Permissions Management Routes
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    Route::post('permissions/bulk-assign', [\App\Http\Controllers\PermissionController::class, 'bulkAssign'])
         ->name('permissions.bulk-assign')
         ->middleware('permission:users.manage');
});

// Activity Logs Routes
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])
         ->name('activity-logs.index');
    Route::get('activity-logs/{log}', [\App\Http\Controllers\ActivityLogController::class, 'show'])
         ->name('activity-logs.show');
    Route::delete('activity-logs/clear-old', [\App\Http\Controllers\ActivityLogController::class, 'clearOld'])
         ->name('activity-logs.clear-old')
         ->middleware('permission:users.manage');
});

// Test route for reception system
Route::get('/test-reception', function() {
    try {
        $stats = [
            'patients' => \App\Models\Patient::count(),
            'appointments' => \App\Models\Appointment::whereDate('appointment_date', today())->count(),
            'visits' => \App\Models\PatientVisit::whereDate('created_at', today())->count(),
            'queue' => \App\Models\QueueEntry::where('status', 'waiting')->count()
        ];
    } catch (\Exception $e) {
        $stats = [
            'patients' => 'N/A',
            'appointments' => 'N/A', 
            'visits' => 'N/A',
            'queue' => 'N/A'
        ];
    }
    
    return view('test-reception', compact('stats'));
})->name('test.reception');

// Simple reception dashboard test
Route::get('/reception-test', function() {
    $statistics = [
        'total_visits' => 15,
        'waiting_visits' => 8,
        'emergency_visits' => 2,
        'completed_visits' => 5
    ];
    
    $todayAppointments = [];
    $departmentQueues = [
        'internal_medicine' => ['name' => 'الطب الباطني', 'waiting' => 3, 'avg_wait' => 25],
        'cardiology' => ['name' => 'أمراض القلب', 'waiting' => 2, 'avg_wait' => 35],
        'pediatrics' => ['name' => 'الأطفال', 'waiting' => 1, 'avg_wait' => 15],
        'emergency' => ['name' => 'الطوارئ', 'waiting' => 2, 'avg_wait' => 10]
    ];
    
    $emergencyAlerts = [
        ['patient_name' => 'أحمد محمد', 'message' => 'حالة حرجة - ألم في الصدر', 'level' => 'critical'],
        ['patient_name' => 'فاطمة علي', 'message' => 'حالة عاجلة - حمى عالية', 'level' => 'urgent']
    ];
    
    $currentPatients = [];
    
    return view('reception.master-dashboard', compact(
        'statistics', 'todayAppointments', 'departmentQueues', 
        'emergencyAlerts', 'currentPatients'
    ));
})->name('reception.test');

// Reception Master Dashboard Routes
Route::middleware(['auth', 'permission:reception.view'])->prefix('reception')->name('reception.')->group(function () {
    // لوحة الاستقبال الشاملة
    Route::get('/dashboard', [\App\Http\Controllers\ReceptionMasterController::class, 'dashboard'])
         ->name('dashboard');
    
    // تسجيل المرضى
    Route::post('/register-patient', [\App\Http\Controllers\ReceptionMasterController::class, 'registerPatient'])
         ->name('register-patient')
         ->middleware('permission:patients.create');
    
    Route::post('/register-emergency', [\App\Http\Controllers\ReceptionMasterController::class, 'registerEmergency'])
         ->name('register-emergency')
         ->middleware('permission:emergency.create');
    
    // إدارة الوصول والطوابير
    Route::post('/check-in', [\App\Http\Controllers\ReceptionMasterController::class, 'checkInPatient'])
         ->name('check-in')
         ->middleware('permission:reception.checkin');
    
    Route::post('/call-patient', [\App\Http\Controllers\ReceptionMasterController::class, 'callPatient'])
         ->name('call-patient')
         ->middleware('permission:reception.queue');
    
    // إدارة الطوابير
    Route::get('/queue/{department}', [\App\Http\Controllers\ReceptionMasterController::class, 'getDepartmentQueue'])
         ->name('queue.department');
    
    Route::post('/queue/update-priority', [\App\Http\Controllers\ReceptionMasterController::class, 'updateQueuePriority'])
         ->name('queue.update-priority')
         ->middleware('permission:reception.queue');
    
    Route::post('/queue/transfer-patient', [\App\Http\Controllers\ReceptionMasterController::class, 'transferPatient'])
         ->name('queue.transfer')
         ->middleware('permission:reception.transfer');
    
    // البحث والتصفية
    Route::get('/search-patients', [\App\Http\Controllers\ReceptionMasterController::class, 'searchPatients'])
         ->name('search-patients');
    
    Route::get('/patient-details/{patient}', [\App\Http\Controllers\ReceptionMasterController::class, 'getPatientDetails'])
         ->name('patient-details');
    
    // التقارير والإحصائيات
    Route::get('/statistics', [\App\Http\Controllers\ReceptionMasterController::class, 'getStatistics'])
         ->name('statistics');
    
    Route::get('/daily-report', [\App\Http\Controllers\ReceptionMasterController::class, 'getDailyReport'])
         ->name('daily-report');
});

// Lab Specialized Dashboard Routes
Route::middleware(['auth', 'permission:lab.view'])->prefix('lab-specialized')->name('lab-specialized.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\LabSpecializedController::class, 'dashboard'])
         ->name('dashboard');
    
    Route::get('/orders/{labOrder}/details', [\App\Http\Controllers\LabSpecializedController::class, 'getOrderDetails'])
         ->name('order-details');
    
    Route::post('/orders/{labOrder}/result', [\App\Http\Controllers\LabSpecializedController::class, 'addResult'])
         ->name('add-result')
         ->middleware('permission:lab.manage');
    
    Route::patch('/orders/{labOrder}/status', [\App\Http\Controllers\LabSpecializedController::class, 'updateOrderStatus'])
         ->name('update-order-status')
         ->middleware('permission:lab.manage');
    
    Route::post('/reports/generate', [\App\Http\Controllers\LabSpecializedController::class, 'generateReport'])
         ->name('generate-report')
         ->middleware('permission:lab.manage');
    
    Route::patch('/results/{labResult}/mark-notified', [\App\Http\Controllers\LabSpecializedController::class, 'markResultNotified'])
         ->name('mark-result-notified')
         ->middleware('permission:lab.manage');
});

// Radiology Specialized Dashboard Routes
Route::middleware(['auth', 'permission:radiology.view'])->prefix('radiology-specialized')->name('radiology-specialized.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\RadiologySpecializedController::class, 'dashboard'])
         ->name('dashboard');
    
    Route::get('/orders/{radiologyOrder}/details', [\App\Http\Controllers\RadiologySpecializedController::class, 'getOrderDetails'])
         ->name('order-details');
    
    Route::post('/orders/{radiologyOrder}/report', [\App\Http\Controllers\RadiologySpecializedController::class, 'addReport'])
         ->name('add-report')
         ->middleware('permission:radiology.manage');
    
    Route::post('/orders/{radiologyOrder}/schedule', [\App\Http\Controllers\RadiologySpecializedController::class, 'scheduleOrder'])
         ->name('schedule-order')
         ->middleware('permission:radiology.manage');
    
    Route::patch('/orders/{radiologyOrder}/status', [\App\Http\Controllers\RadiologySpecializedController::class, 'updateOrderStatus'])
         ->name('update-order-status')
         ->middleware('permission:radiology.manage');
    
    Route::patch('/orders/{radiologyOrder}/mark-notified', [\App\Http\Controllers\RadiologySpecializedController::class, 'markOrderNotified'])
         ->name('mark-order-notified')
         ->middleware('permission:radiology.manage');
    
    Route::post('/reports/generate', [\App\Http\Controllers\RadiologySpecializedController::class, 'generateReport'])
         ->name('generate-report')
         ->middleware('permission:radiology.manage');
});

// Cashier Advanced Dashboard Routes
Route::middleware(['auth'])->prefix('cashier')->name('cashier.')->group(function () {
    // لوحة الخزينة المتقدمة
    Route::get('/dashboard', [\App\Http\Controllers\CashierAdvancedController::class, 'dashboard'])
         ->name('dashboard');
    
    // معالجة المدفوعات
    Route::post('/process-payment', [\App\Http\Controllers\CashierAdvancedController::class, 'processPayment'])
         ->name('process-payment');
    
    Route::post('/process-cash-payment', [\App\Http\Controllers\CashierAdvancedController::class, 'processCashPayment'])
         ->name('process-cash-payment');
    
    // تفاصيل التأمين
    Route::get('/insurance-details/{patient}', [\App\Http\Controllers\CashierAdvancedController::class, 'getInsuranceDetails'])
         ->name('insurance-details')
         ->middleware('permission:insurance.view');
    
    Route::post('/calculate-insurance-coverage', [\App\Http\Controllers\CashierAdvancedController::class, 'calculateInsuranceCoverage'])
         ->name('calculate-insurance-coverage')
         ->middleware('permission:insurance.view');
    
    // إدارة المدفوعات المعلقة
    Route::get('/pending-payments', [\App\Http\Controllers\CashierAdvancedController::class, 'managePendingPayments'])
         ->name('pending-payments');
    
    Route::post('/approve-payment/{payment}', [\App\Http\Controllers\CashierAdvancedController::class, 'approvePayment'])
         ->name('approve-payment')
         ->middleware('permission:payments.manage');
    
    Route::post('/reject-payment/{payment}', [\App\Http\Controllers\CashierAdvancedController::class, 'rejectPayment'])
         ->name('reject-payment')
         ->middleware('permission:payments.manage');
    
    Route::post('/bulk-approve', [\App\Http\Controllers\CashierAdvancedController::class, 'bulkApprove'])
         ->name('bulk-approve')
         ->middleware('permission:payments.manage');
    
    Route::post('/bulk-reject', [\App\Http\Controllers\CashierAdvancedController::class, 'bulkReject'])
         ->name('bulk-reject')
         ->middleware('permission:payments.manage');
    
    Route::get('/payment-details/{payment}', [\App\Http\Controllers\CashierAdvancedController::class, 'getPaymentDetails'])
         ->name('payment-details');
    
    // إدارة الفواتير المتأخرة
    Route::get('/overdue-invoices', [\App\Http\Controllers\CashierAdvancedController::class, 'manageOverdueInvoices'])
         ->name('overdue-invoices');
    
    Route::get('/payment-form/{invoice}', [\App\Http\Controllers\CashierAdvancedController::class, 'getPaymentForm'])
         ->name('payment-form');
    
    Route::post('/send-reminder/{invoice}', [\App\Http\Controllers\CashierAdvancedController::class, 'sendReminder'])
         ->name('send-reminder')
         ->middleware('permission:invoices.manage');
    
    Route::post('/bulk-send-reminders', [\App\Http\Controllers\CashierAdvancedController::class, 'bulkSendReminders'])
         ->name('bulk-send-reminders')
         ->middleware('permission:invoices.manage');
    
    Route::post('/create-payment-plan', [\App\Http\Controllers\CashierAdvancedController::class, 'createPaymentPlan'])
         ->name('create-payment-plan')
         ->middleware('permission:invoices.manage');
    
    Route::post('/write-off-debt/{invoice}', [\App\Http\Controllers\CashierAdvancedController::class, 'writeOffDebt'])
         ->name('write-off-debt')
         ->middleware('permission:invoices.writeoff');
    
    Route::post('/transfer-to-collection/{invoice}', [\App\Http\Controllers\CashierAdvancedController::class, 'transferToCollection'])
         ->name('transfer-to-collection')
         ->middleware('permission:invoices.manage');
    
    Route::get('/export-overdue-report', [\App\Http\Controllers\CashierAdvancedController::class, 'exportOverdueReport'])
         ->name('export-overdue-report');
});

// Doctor Integrated Dashboard Routes
Route::middleware(['auth'])->prefix('doctor')->name('doctor.')->group(function () {
    // لوحة تحكم الطبيب المتكاملة
    Route::get('/integrated-dashboard', [\App\Http\Controllers\DoctorIntegratedController::class, 'dashboard'])
         ->name('integrated.dashboard');
    
    Route::get('/integrated-dashboard/updates', [\App\Http\Controllers\DoctorIntegratedController::class, 'getDashboardUpdates'])
         ->name('integrated.dashboard.updates');
    
    // إجراء الكشف الطبي
    Route::get('/examination/start/{appointment}', [\App\Http\Controllers\DoctorIntegratedController::class, 'startExamination'])
         ->name('examination.start');
    
    Route::post('/examination/save-report/{appointment}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveMedicalReport'])
         ->name('examination.save-report');
    
    Route::get('/examination/next-steps/{medicalRecord}', [\App\Http\Controllers\DoctorIntegratedController::class, 'nextSteps'])
         ->name('next-steps');
    
    // كتابة الروشتات
    Route::post('/prescription/write/{medicalRecord}', [\App\Http\Controllers\DoctorIntegratedController::class, 'writePrescription'])
         ->name('prescription.write');
    
    // التحويل للتخصصات
    Route::post('/transfer/{medicalRecord}', [\App\Http\Controllers\DoctorIntegratedController::class, 'transferToSpecialist'])
         ->name('transfer.specialist');
    
    // حجز مواعيد المتابعة
    Route::post('/follow-up/{medicalRecord}', [\App\Http\Controllers\DoctorIntegratedController::class, 'bookFollowUp'])
         ->name('follow-up.book');
    
    // تأكيد المواعيد
    Route::post('/appointments/{appointment}/confirm', [\App\Http\Controllers\DoctorIntegratedController::class, 'confirmAppointment'])
         ->name('appointments.confirm');
    
    // إجراء الكشف الطبي
    Route::get('/examination/conduct/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'conductExamination'])
         ->name('examination.conduct')
         ->middleware('permission:doctor.examination');
    
    Route::post('/examination/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveExamination'])
         ->name('examination.save')
         ->middleware('permission:doctor.examination');
    
    Route::post('/examination/save-draft', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveDraft'])
         ->name('examination.save-draft')
         ->middleware('permission:doctor.examination');
    
    Route::get('/examination/next-steps/{medicalRecord}', [\App\Http\Controllers\DoctorIntegratedController::class, 'nextSteps'])
         ->name('examination.next-steps')
         ->middleware('permission:doctor.examination');
    
    // إنشاء الوصفات الطبية
    Route::get('/prescriptions/create/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'createPrescription'])
         ->name('prescriptions.create')
         ->middleware('permission:doctor.prescriptions');
    
    Route::post('/prescriptions/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'savePrescription'])
         ->name('prescriptions.save')
         ->middleware('permission:doctor.prescriptions');
    
    Route::post('/prescriptions/check-interactions', [\App\Http\Controllers\DoctorIntegratedController::class, 'checkDrugInteractions'])
         ->name('prescriptions.check-interactions')
         ->middleware('permission:doctor.prescriptions');
    
    // طلب التحاليل المخبرية
    Route::get('/lab-orders/create/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'orderLabTests'])
         ->name('lab-orders.create')
         ->middleware('permission:doctor.lab_orders');
    
    Route::post('/lab-orders/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveLabOrder'])
         ->name('lab-orders.save')
         ->middleware('permission:doctor.lab_orders');
    
    // طلب الأشعة
    Route::get('/radiology-orders/create/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'orderRadiology'])
         ->name('radiology-orders.create')
         ->middleware('permission:doctor.radiology_orders');
    
    Route::post('/radiology-orders/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveRadiologyOrder'])
         ->name('radiology-orders.save')
         ->middleware('permission:doctor.radiology_orders');
    
    // حجز الاستشارات
    Route::get('/consultations/book/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'bookConsultation'])
         ->name('consultations.book')
         ->middleware('permission:doctor.consultations');
    
    Route::post('/consultations/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveConsultation'])
         ->name('consultations.save')
         ->middleware('permission:doctor.consultations');
    
    // تحويل المرضى
    Route::get('/transfers/create/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'transferPatient'])
         ->name('transfers.create')
         ->middleware('permission:doctor.transfers');
    
    Route::post('/transfers/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveTransfer'])
         ->name('transfers.save')
         ->middleware('permission:doctor.transfers');
    
    // تحويل للقسم الداخلي
    Route::get('/admissions/create/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'admitPatient'])
         ->name('admissions.create')
         ->middleware('permission:doctor.inpatient');
    
    Route::post('/admissions/save/{patient}', [\App\Http\Controllers\DoctorIntegratedController::class, 'saveAdmission'])
         ->name('admissions.save')
         ->middleware('permission:doctor.inpatient');
    
    // البحث والاستعلامات
    Route::get('/patients/search', [\App\Http\Controllers\DoctorIntegratedController::class, 'searchPatients'])
         ->name('patients.search');
    
    Route::get('/patients/{patient}/details', [\App\Http\Controllers\DoctorIntegratedController::class, 'getPatientDetails'])
         ->name('patients.details');
    
    Route::get('/patients/{patient}/history', [\App\Http\Controllers\DoctorIntegratedController::class, 'getPatientHistory'])
         ->name('patients.history');
    
    // مراجعة النتائج
    Route::get('/results/lab/{labResult}/review', [\App\Http\Controllers\DoctorIntegratedController::class, 'reviewLabResult'])
         ->name('results.lab.review');
    
    Route::post('/results/lab/{labResult}/approve', [\App\Http\Controllers\DoctorIntegratedController::class, 'approveLabResult'])
         ->name('results.lab.approve');
    
    Route::get('/results/radiology/{radiologyResult}/review', [\App\Http\Controllers\DoctorIntegratedController::class, 'reviewRadiologyResult'])
         ->name('results.radiology.review');
    
    Route::post('/results/radiology/{radiologyResult}/approve', [\App\Http\Controllers\DoctorIntegratedController::class, 'approveRadiologyResult'])
         ->name('results.radiology.approve');
    
    // التقارير والإحصائيات
    Route::get('/reports/daily', [\App\Http\Controllers\DoctorIntegratedController::class, 'getDailyReport'])
         ->name('reports.daily');
    
    Route::get('/statistics/performance', [\App\Http\Controllers\DoctorIntegratedController::class, 'getPerformanceStatistics'])
         ->name('statistics.performance');
});

// Advanced Patient Insurance Management Routes
Route::middleware(['auth'])->group(function () {
    // Patient Insurance Assignment
    Route::post('/patients/{patient}/assign-insurance', [\App\Http\Controllers\PatientController::class, 'assignInsurance'])
         ->name('patients.assign-insurance')
         ->middleware('permission:patients.manage');
    
    Route::delete('/patients/{patient}/insurance/{patientInsurance}', [\App\Http\Controllers\PatientController::class, 'removeInsurance'])
         ->name('patients.remove-insurance')
         ->middleware('permission:patients.manage');
    
    // Insurance Policy AJAX Routes
    Route::get('/insurance-policies-by-company', [\App\Http\Controllers\PatientController::class, 'getInsurancePolicies'])
         ->name('patients.get-insurance-policies');
    
    // Coverage Calculation
    Route::post('/patients/{patient}/calculate-coverage', [\App\Http\Controllers\PatientController::class, 'calculateCoverage'])
         ->name('patients.calculate-coverage');
});

// Specialized Lab and Radiology Dashboard Routes
Route::middleware(['auth'])->group(function () {
    // Lab Specialized Dashboard
    Route::prefix('lab-specialized')->name('lab-specialized.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\LabSpecializedController::class, 'dashboard'])
             ->name('dashboard')
             ->middleware('permission:lab.view');
        
        Route::patch('/orders/{labOrder}/status', [\App\Http\Controllers\LabSpecializedController::class, 'updateOrderStatus'])
             ->name('update-order-status')
             ->middleware('permission:lab.manage');
        
        Route::post('/orders/{labOrder}/result', [\App\Http\Controllers\LabSpecializedController::class, 'addResult'])
             ->name('add-result')
             ->middleware('permission:lab.manage');
        
        Route::patch('/results/{labResult}/mark-notified', [\App\Http\Controllers\LabSpecializedController::class, 'markCriticalNotified'])
             ->name('mark-critical-notified')
             ->middleware('permission:lab.manage');
        
        Route::get('/orders/{labOrder}/details', [\App\Http\Controllers\LabSpecializedController::class, 'getOrderDetails'])
             ->name('order-details')
             ->middleware('permission:lab.view');
        
        Route::post('/reports/generate', [\App\Http\Controllers\LabSpecializedController::class, 'generateReport'])
             ->name('generate-report')
             ->middleware('permission:lab.reports');
    });
    
    // Radiology Specialized Dashboard
    Route::prefix('radiology-specialized')->name('radiology-specialized.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\RadiologySpecializedController::class, 'dashboard'])
             ->name('dashboard')
             ->middleware('permission:radiology.view');
        
        Route::patch('/orders/{radiologyOrder}/status', [\App\Http\Controllers\RadiologySpecializedController::class, 'updateOrderStatus'])
             ->name('update-order-status')
             ->middleware('permission:radiology.manage');
        
        Route::post('/orders/{radiologyOrder}/report', [\App\Http\Controllers\RadiologySpecializedController::class, 'addReport'])
             ->name('add-report')
             ->middleware('permission:radiology.manage');
        
        Route::patch('/orders/{radiologyOrder}/mark-notified', [\App\Http\Controllers\RadiologySpecializedController::class, 'markUrgentNotified'])
             ->name('mark-urgent-notified')
             ->middleware('permission:radiology.manage');
        
        Route::post('/orders/{radiologyOrder}/schedule', [\App\Http\Controllers\RadiologySpecializedController::class, 'scheduleOrder'])
             ->name('schedule-order')
             ->middleware('permission:radiology.manage');
        
        Route::get('/orders/{radiologyOrder}/details', [\App\Http\Controllers\RadiologySpecializedController::class, 'getOrderDetails'])
             ->name('order-details')
             ->middleware('permission:radiology.view');
        
        Route::post('/reports/generate', [\App\Http\Controllers\RadiologySpecializedController::class, 'generateReport'])
             ->name('generate-report')
             ->middleware('permission:radiology.reports');
    });
});

// Notification System Routes
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    // عرض الإشعارات
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
    
    // إدارة الإشعارات
    Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    
    // إرسال إشعارات (للمدراء)
    Route::post('/', [NotificationController::class, 'store'])
         ->name('store')
         ->middleware('permission:notifications.send');
    
    Route::post('/test', [NotificationController::class, 'sendTest'])
         ->name('send-test')
         ->middleware('permission:notifications.send');
    
    // تفضيلات الإشعارات
    Route::get('/preferences/edit', [NotificationController::class, 'preferences'])->name('preferences');
    Route::patch('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
    
    // إدارة الأجهزة
    Route::post('/devices/register', [NotificationController::class, 'registerDevice'])->name('devices.register');
    Route::delete('/devices/unregister', [NotificationController::class, 'unregisterDevice'])->name('devices.unregister');
    
    // إحصائيات (للمدراء)
    Route::get('/statistics', [NotificationController::class, 'statistics'])
         ->name('statistics')
         ->middleware('permission:notifications.view_statistics');
});

// API Routes for Mobile App
Route::middleware(['auth:sanctum'])->prefix('api/notifications')->name('api.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'apiIndex'])->name('index');
    Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/devices/register', [NotificationController::class, 'registerDevice'])->name('devices.register');
});

// Advanced Reports System Routes
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    // Main reports dashboard
    Route::get('/', [\App\Http\Controllers\ReportsAdvancedController::class, 'index'])->name('index');
    
    // Financial Reports
    Route::get('/financial', [\App\Http\Controllers\ReportsAdvancedController::class, 'financialReports'])->name('financial');
    
    // Performance Reports
    Route::get('/performance', [\App\Http\Controllers\ReportsAdvancedController::class, 'performanceReports'])->name('performance');
    
    // Patient Statistics
    Route::get('/patient-statistics', [\App\Http\Controllers\ReportsAdvancedController::class, 'patientStatistics'])->name('patient-statistics');
    
    // Insurance Reports
    Route::get('/insurance', [\App\Http\Controllers\ReportsAdvancedController::class, 'insuranceReports'])->name('insurance');
    
    // Inventory Reports
    Route::get('/inventory', [\App\Http\Controllers\ReportsAdvancedController::class, 'inventoryReports'])->name('inventory');
    
    // Executive Summary Report
    Route::get('/executive-summary', [\App\Http\Controllers\ReportsAdvancedController::class, 'executiveSummary'])->name('executive-summary');
});

// Security and Monitoring System Routes
Route::middleware(['auth'])->prefix('security')->name('security.')->group(function () {
    // Security Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\SecurityController::class, 'dashboard'])
         ->name('dashboard')
         ->middleware('permission:security.view');
    
    // Security Logs
    Route::get('/logs', [\App\Http\Controllers\SecurityController::class, 'logs'])
         ->name('logs')
         ->middleware('permission:security.view');
    
    // Login Attempts
    Route::get('/login-attempts', [\App\Http\Controllers\SecurityController::class, 'loginAttempts'])
         ->name('login-attempts')
         ->middleware('permission:security.view');
    
    // Security Event Details
    Route::get('/events/{securityLog}', [\App\Http\Controllers\SecurityController::class, 'showEvent'])
         ->name('show-event')
         ->middleware('permission:security.view');
    
    // Security Actions
    Route::post('/health-check', [\App\Http\Controllers\SecurityController::class, 'healthCheck'])
         ->name('health-check')
         ->middleware('permission:security.manage');
    
    Route::post('/create-backup', [\App\Http\Controllers\SecurityController::class, 'createBackup'])
         ->name('create-backup')
         ->middleware('permission:security.manage');
    
    Route::post('/cleanup-logs', [\App\Http\Controllers\SecurityController::class, 'cleanupLogs'])
         ->name('cleanup-logs')
         ->middleware('permission:security.manage');
    
    Route::post('/block-ip', [\App\Http\Controllers\SecurityController::class, 'blockIp'])
         ->name('block-ip')
         ->middleware('permission:security.manage');
    
    // Export Security Logs
    Route::post('/export-logs', [\App\Http\Controllers\SecurityController::class, 'exportLogs'])
         ->name('export-logs')
         ->middleware('permission:security.export');
});

// Integration Testing System Routes
Route::middleware(['auth'])->prefix('integration')->name('integration.')->group(function () {
    // Run specific integration test
    Route::post('/test/{testType}', [\App\Http\Controllers\IntegrationTestController::class, 'runTest'])
         ->name('run-test')
         ->middleware('permission:integration.test');
    
    // Generate system status report
    Route::post('/system-report', [\App\Http\Controllers\IntegrationTestController::class, 'generateSystemReport'])
         ->name('system-report')
         ->middleware('permission:integration.manage');
    
    // Run all integration tests
    Route::post('/run-all-tests', [\App\Http\Controllers\IntegrationTestController::class, 'runAllTests'])
         ->name('run-all-tests')
         ->middleware('permission:integration.manage');
});

// Landing Page Admin Management Routes
Route::middleware(['auth', 'permission:landing-page.manage'])->prefix('admin/landing-page')->name('admin.landing-page.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\LandingPageAdminController::class, 'dashboard'])
         ->name('dashboard');
    
    // Settings
    Route::get('/settings', [\App\Http\Controllers\LandingPageAdminController::class, 'settings'])
         ->name('settings');
    Route::put('/settings', [\App\Http\Controllers\LandingPageAdminController::class, 'updateSettings'])
         ->name('settings.update');
    
    // Offers Management
    Route::get('/offers', [\App\Http\Controllers\LandingPageAdminController::class, 'offers'])
         ->name('offers');
    Route::get('/offers/create', [\App\Http\Controllers\LandingPageAdminController::class, 'createOffer'])
         ->name('offers.create');
    Route::post('/offers', [\App\Http\Controllers\LandingPageAdminController::class, 'storeOffer'])
         ->name('offers.store');
    Route::get('/offers/{offer}/edit', [\App\Http\Controllers\LandingPageAdminController::class, 'editOffer'])
         ->name('offers.edit');
    Route::put('/offers/{offer}', [\App\Http\Controllers\LandingPageAdminController::class, 'updateOffer'])
         ->name('offers.update');
    Route::delete('/offers/{offer}', [\App\Http\Controllers\LandingPageAdminController::class, 'destroyOffer'])
         ->name('offers.destroy');
    Route::post('/offers/{offer}/toggle-status', [\App\Http\Controllers\LandingPageAdminController::class, 'toggleOfferStatus'])
         ->name('offers.toggle-status');
    
    // Preview and Analytics
    Route::get('/preview', [\App\Http\Controllers\LandingPageAdminController::class, 'preview'])
         ->name('preview');
    Route::get('/analytics', [\App\Http\Controllers\LandingPageAdminController::class, 'analytics'])
         ->name('analytics');
    
    // Cache Management
    Route::post('/clear-cache', [\App\Http\Controllers\LandingPageAdminController::class, 'clearCache'])
         ->name('clear-cache');
});

// Support Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/api/support-info', [SupportController::class, 'getSupportInfo'])->name('support.info');
});

// Public Support Route
Route::get('/support-info', [SupportController::class, 'getSupportInfo'])->name('public.support.info');