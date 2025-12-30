<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\EventDispatcherService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherService $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment)
    {
        try {
            if ($payment->status === 'completed') {
                $this->eventDispatcher->dispatchPaymentEvents($payment, 'received');
            }
        } catch (\Exception $e) {
            Log::error('فشل في معالجة إنشاء الدفعة: ' . $e->getMessage(), [
                'payment_id' => $payment->id
            ]);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment)
    {
        try {
            if ($payment->wasChanged('status')) {
                switch ($payment->status) {
                    case 'completed':
                        $this->eventDispatcher->dispatchPaymentEvents($payment, 'received');
                        break;
                    case 'failed':
                        $this->eventDispatcher->dispatchPaymentEvents($payment, 'failed');
                        break;
                    case 'refunded':
                        $this->eventDispatcher->dispatchPaymentEvents($payment, 'refunded');
                        break;
                }
            }
        } catch (\Exception $e) {
            Log::error('فشل في معالجة تحديث الدفعة: ' . $e->getMessage(), [
                'payment_id' => $payment->id
            ]);
        }
    }
}