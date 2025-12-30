<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PaymentProcessingService
{
    /**
     * معالجة الدفع حسب الطريقة المحددة
     */
    public function processPayment(array $paymentData)
    {
        try {
            $method = $paymentData['payment_method'];
            
            switch ($method) {
                case 'cash':
                    return $this->processCashPayment($paymentData);
                    
                case 'visa':
                case 'mastercard':
                    return $this->processCardPayment($paymentData);
                    
                case 'bank_transfer':
                    return $this->processBankTransfer($paymentData);
                    
                case 'insurance':
                    return $this->processInsurancePayment($paymentData);
                    
                default:
                    return [
                        'success' => false,
                        'message' => 'طريقة دفع غير مدعومة'
                    ];
            }
            
        } catch (\Exception $e) {
            Log::error('خطأ في معالجة الدفع: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'حدث خطأ في معالجة الدفع'
            ];
        }
    }

    /**
     * معالجة الدفع النقدي
     */
    private function processCashPayment(array $data)
    {
        try {
            $payment = Payment::create([
                'invoice_id' => $data['invoice']->id,
                'patient_id' => $data['patient']->id,
                'amount' => $data['amount'],
                'payment_method' => 'cash',
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'transaction_reference' => 'CASH-' . time() . '-' . rand(1000, 9999),
                'metadata' => json_encode([
                    'cash_received' => $data['amount'],
                    'change_given' => max(0, $data['amount'] - $data['invoice']->total_amount)
                ])
            ]);
            
            // تحديث الفاتورة
            $this->updateInvoiceStatus($data['invoice'], $payment);
            
            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'تم الدفع النقدي بنجاح'
            ];
            
        } catch (\Exception $e) {
            Log::error('خطأ في الدفع النقدي: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'فشل في معالجة الدفع النقدي'
            ];
        }
    }

    /**
     * معالجة دفع البطاقة الائتمانية
     */
    private function processCardPayment(array $data)
    {
        try {
            // محاكاة معالجة البطاقة الائتمانية
            $cardResult = $this->processCardTransaction($data['card_details'], $data['amount']);
            
            if (!$cardResult['success']) {
                return [
                    'success' => false,
                    'message' => $cardResult['message']
                ];
            }
            
            $payment = Payment::create([
                'invoice_id' => $data['invoice']->id,
                'patient_id' => $data['patient']->id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'transaction_reference' => $cardResult['transaction_id'],
                'gateway_response' => json_encode($cardResult['gateway_response']),
                'metadata' => json_encode([
                    'card_last_four' => substr($data['card_details']['card_number'], -4),
                    'card_type' => $data['payment_method'],
                    'authorization_code' => $cardResult['auth_code']
                ])
            ]);
            
            // تحديث الفاتورة
            $this->updateInvoiceStatus($data['invoice'], $payment);
            
            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'تم الدفع بالبطاقة بنجاح'
            ];
            
        } catch (\Exception $e) {
            Log::error('خطأ في دفع البطاقة: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'فشل في معالجة دفع البطاقة'
            ];
        }
    }

    /**
     * معالجة التحويل البنكي
     */
    private function processBankTransfer(array $data)
    {
        try {
            $payment = Payment::create([
                'invoice_id' => $data['invoice']->id,
                'patient_id' => $data['patient']->id,
                'amount' => $data['amount'],
                'payment_method' => 'bank_transfer',
                'status' => 'pending', // يحتاج تأكيد من البنك
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'transaction_reference' => $data['bank_reference'],
                'metadata' => json_encode([
                    'bank_reference' => $data['bank_reference'],
                    'transfer_date' => now()->toDateString()
                ])
            ]);
            
            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'تم تسجيل التحويل البنكي - في انتظار التأكيد'
            ];
            
        } catch (\Exception $e) {
            Log::error('خطأ في التحويل البنكي: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'فشل في تسجيل التحويل البنكي'
            ];
        }
    }

    /**
     * معالجة دفع التأمين
     */
    private function processInsurancePayment(array $data)
    {
        try {
            $coverageDetails = $data['coverage_details'];
            
            // إنشاء دفعة التأمين
            $insurancePayment = Payment::create([
                'invoice_id' => $data['invoice']->id,
                'patient_id' => $data['patient']->id,
                'amount' => $coverageDetails['insurance_amount'],
                'payment_method' => 'insurance',
                'status' => 'pending', // يحتاج موافقة شركة التأمين
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'transaction_reference' => $data['insurance_approval'],
                'metadata' => json_encode([
                    'insurance_company' => $coverageDetails['insurance_company'],
                    'coverage_percentage' => $coverageDetails['coverage_percentage'],
                    'approval_number' => $data['insurance_approval'],
                    'patient_copay' => $coverageDetails['patient_amount']
                ])
            ]);
            
            // إنشاء دفعة المريض إذا كان هناك مبلغ مطلوب منه
            $patientPayment = null;
            if ($coverageDetails['patient_amount'] > 0) {
                $patientPayment = Payment::create([
                    'invoice_id' => $data['invoice']->id,
                    'patient_id' => $data['patient']->id,
                    'amount' => $coverageDetails['patient_amount'],
                    'payment_method' => 'cash', // افتراضي للمبلغ المطلوب من المريض
                    'status' => 'pending',
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                    'transaction_reference' => 'COPAY-' . time(),
                    'metadata' => json_encode([
                        'type' => 'patient_copay',
                        'related_insurance_payment' => $insurancePayment->id
                    ])
                ]);
            }
            
            return [
                'success' => true,
                'payment' => $insurancePayment,
                'patient_payment' => $patientPayment,
                'message' => 'تم تسجيل دفع التأمين - في انتظار الموافقة'
            ];
            
        } catch (\Exception $e) {
            Log::error('خطأ في دفع التأمين: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'فشل في معالجة دفع التأمين'
            ];
        }
    }

    /**
     * معالجة معاملة البطاقة الائتمانية (محاكاة)
     */
    private function processCardTransaction(array $cardDetails, float $amount)
    {
        try {
            // محاكاة استدعاء بوابة الدفع
            // في التطبيق الحقيقي، هنا سيتم الاتصال ببوابة الدفع الفعلية
            
            // التحقق من صحة البطاقة (محاكاة)
            if (!$this->validateCard($cardDetails)) {
                return [
                    'success' => false,
                    'message' => 'بيانات البطاقة غير صحيحة'
                ];
            }
            
            // محاكاة معالجة الدفع
            $transactionId = 'TXN-' . time() . '-' . rand(10000, 99999);
            $authCode = 'AUTH-' . rand(100000, 999999);
            
            // محاكاة نجاح المعاملة (90% نجاح)
            $success = rand(1, 10) <= 9;
            
            if ($success) {
                return [
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'auth_code' => $authCode,
                    'gateway_response' => [
                        'status' => 'approved',
                        'response_code' => '00',
                        'message' => 'Transaction approved',
                        'timestamp' => now()->toISOString()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'تم رفض المعاملة من البنك'
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('خطأ في معالجة البطاقة: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'خطأ في الاتصال ببوابة الدفع'
            ];
        }
    }

    /**
     * التحقق من صحة بيانات البطاقة
     */
    private function validateCard(array $cardDetails)
    {
        // التحقق من رقم البطاقة (خوارزمية Luhn)
        if (!$this->luhnCheck($cardDetails['card_number'])) {
            return false;
        }
        
        // التحقق من تاريخ الانتهاء
        $expiryDate = Carbon::createFromFormat('m/y', $cardDetails['expiry_date']);
        if ($expiryDate->isPast()) {
            return false;
        }
        
        // التحقق من CVV
        if (strlen($cardDetails['cvv']) < 3 || strlen($cardDetails['cvv']) > 4) {
            return false;
        }
        
        return true;
    }

    /**
     * خوارزمية Luhn للتحقق من رقم البطاقة
     */
    private function luhnCheck($cardNumber)
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);
        $sum = 0;
        $alternate = false;
        
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $n = intval($cardNumber[$i]);
            
            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n = ($n % 10) + 1;
                }
            }
            
            $sum += $n;
            $alternate = !$alternate;
        }
        
        return ($sum % 10) == 0;
    }

    /**
     * تحديث حالة الفاتورة بعد الدفع
     */
    private function updateInvoiceStatus(Invoice $invoice, Payment $payment)
    {
        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $totalPaid,
                'payment_date' => now()
            ]);
        } else {
            $invoice->update([
                'status' => 'partially_paid',
                'paid_amount' => $totalPaid
            ]);
        }
    }

    /**
     * استرداد الدفع
     */
    public function refundPayment(Payment $payment, float $refundAmount, string $reason)
    {
        try {
            if ($payment->status !== 'completed') {
                return [
                    'success' => false,
                    'message' => 'لا يمكن استرداد دفعة غير مكتملة'
                ];
            }
            
            if ($refundAmount > $payment->amount) {
                return [
                    'success' => false,
                    'message' => 'مبلغ الاسترداد أكبر من مبلغ الدفعة'
                ];
            }
            
            // إنشاء دفعة الاسترداد
            $refund = Payment::create([
                'invoice_id' => $payment->invoice_id,
                'patient_id' => $payment->patient_id,
                'amount' => -$refundAmount,
                'payment_method' => $payment->payment_method,
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'transaction_reference' => 'REFUND-' . $payment->transaction_reference,
                'metadata' => json_encode([
                    'type' => 'refund',
                    'original_payment_id' => $payment->id,
                    'refund_reason' => $reason
                ])
            ]);
            
            // تحديث الدفعة الأصلية
            $payment->update([
                'refunded_amount' => ($payment->refunded_amount ?? 0) + $refundAmount,
                'status' => $refundAmount >= $payment->amount ? 'refunded' : 'partially_refunded'
            ]);
            
            return [
                'success' => true,
                'refund' => $refund,
                'message' => 'تم الاسترداد بنجاح'
            ];
            
        } catch (\Exception $e) {
            Log::error('خطأ في الاسترداد: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'فشل في معالجة الاسترداد'
            ];
        }
    }
}