<?php

namespace App\Http\Controllers;

use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LabSpecializedController extends Controller
{
    /**
     * Display the specialized lab dashboard
     */
    public function dashboard()
    {
        try {
            // Get today's statistics
            $todayStats = $this->getTodayStatistics();
            
            // Get pending orders by priority
            $pendingOrders = $this->getPendingOrdersByPriority();
            
            // Get critical results that need attention
            $criticalResults = $this->getCriticalResults();
            
            // Get overdue orders
            $overdueOrders = $this->getOverdueOrders();
            
            // Get sample expiry alerts
            $expiringsamples = $this->getExpiringSamples();
            
            // Get workload distribution
            $workloadStats = $this->getWorkloadStatistics();
            
            return view('lab.specialized-dashboard', compact(
                'todayStats',
                'pendingOrders',
                'criticalResults',
                'overdueOrders',
                'expiringsamples',
                'workloadStats'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading lab specialized dashboard', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'حدث خطأ أثناء تحميل لوحة تحكم المختبر');
        }
    }

    /**
     * Get today's lab statistics
     */
    private function getTodayStatistics(): array
    {
        $today = today();
        
        return [
            'total_orders' => LabOrder::whereDate('ordered_at', $today)->count(),
            'pending_orders' => LabOrder::whereDate('ordered_at', $today)->pending()->count(),
            'completed_orders' => LabOrder::whereDate('ordered_at', $today)->byStatus('completed')->count(),
            'critical_results' => LabResult::whereHas('labOrder', function($q) use ($today) {
                $q->whereDate('ordered_at', $today);
            })->critical()->count(),
            'urgent_orders' => LabOrder::whereDate('ordered_at', $today)->urgent()->count(),
            'samples_collected' => LabOrder::whereDate('collected_at', $today)->count(),
            'results_verified' => LabResult::whereDate('verified_at', $today)->count(),
            'overdue_orders' => $this->getOverdueOrdersCount()
        ];
    }

    /**
     * Get pending orders organized by priority
     */
    private function getPendingOrdersByPriority()
    {
        return LabOrder::pending()
            ->with(['patient', 'doctor', 'labTest'])
            ->orderByRaw("FIELD(priority, 'stat', 'urgent', 'routine')")
            ->orderBy('ordered_at')
            ->limit(20)
            ->get()
            ->groupBy('priority');
    }

    /**
     * Get critical results that need immediate attention
     */
    private function getCriticalResults()
    {
        return LabResult::critical()
            ->whereNull('critical_notified_at')
            ->with(['labOrder.patient', 'labOrder.doctor'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get overdue orders
     */
    private function getOverdueOrders()
    {
        $overdueOrders = collect();
        
        $orders = LabOrder::pending()
            ->with(['patient', 'doctor', 'labTest'])
            ->get();
            
        foreach ($orders as $order) {
            if ($order->isOverdue()) {
                $overdueOrders->push($order);
            }
        }
        
        return $overdueOrders->sortBy('ordered_at')->take(15);
    }

    /**
     * Get count of overdue orders
     */
    private function getOverdueOrdersCount(): int
    {
        $count = 0;
        $orders = LabOrder::pending()->get();
        
        foreach ($orders as $order) {
            if ($order->isOverdue()) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get samples that are expiring soon
     */
    private function getExpiringSamples()
    {
        // Samples collected more than 24 hours ago but not processed
        $expiryThreshold = now()->subHours(24);
        
        return LabOrder::whereNotNull('collected_at')
            ->where('collected_at', '<', $expiryThreshold)
            ->whereIn('status', ['collected', 'processing'])
            ->with(['patient', 'labTest'])
            ->orderBy('collected_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get workload statistics for lab management
     */
    private function getWorkloadStatistics(): array
    {
        $last7Days = collect();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7Days->push([
                'date' => $date->format('Y-m-d'),
                'date_display' => $date->format('M d'),
                'orders' => LabOrder::whereDate('ordered_at', $date)->count(),
                'completed' => LabOrder::whereDate('completed_at', $date)->count(),
                'critical' => LabResult::whereHas('labOrder', function($q) use ($date) {
                    $q->whereDate('ordered_at', $date);
                })->critical()->count()
            ]);
        }
        
        return [
            'daily_stats' => $last7Days,
            'avg_turnaround_time' => $this->getAverageTurnaroundTime(),
            'test_distribution' => $this->getTestDistribution(),
            'technician_workload' => $this->getTechnicianWorkload()
        ];
    }

    /**
     * Get average turnaround time for completed orders
     */
    private function getAverageTurnaroundTime(): float
    {
        $completedOrders = LabOrder::byStatus('completed')
            ->whereDate('completed_at', '>=', now()->subDays(7))
            ->get();
            
        if ($completedOrders->isEmpty()) {
            return 0;
        }
        
        $totalMinutes = $completedOrders->sum(function($order) {
            return $order->ordered_at->diffInMinutes($order->completed_at);
        });
        
        return round($totalMinutes / $completedOrders->count() / 60, 1); // Return in hours
    }

    /**
     * Get test distribution for the last 7 days
     */
    private function getTestDistribution(): array
    {
        return LabOrder::whereDate('ordered_at', '>=', now()->subDays(7))
            ->with('labTest')
            ->get()
            ->groupBy('labTest.name')
            ->map(function($orders, $testName) {
                return [
                    'test_name' => $testName,
                    'count' => $orders->count(),
                    'percentage' => 0 // Will be calculated in the view
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get technician workload (placeholder - would need technician assignment system)
     */
    private function getTechnicianWorkload(): array
    {
        // This would require a technician assignment system
        // For now, return sample data structure
        return [
            'active_technicians' => 5,
            'avg_orders_per_tech' => 12,
            'max_capacity' => 60,
            'current_utilization' => 75
        ];
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, LabOrder $labOrder)
    {
        $request->validate([
            'status' => 'required|in:ordered,collected,processing,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function() use ($request, $labOrder) {
                $oldStatus = $labOrder->status;
                $newStatus = $request->status;
                
                // Update timestamps based on status change
                $updateData = ['status' => $newStatus];
                
                switch ($newStatus) {
                    case 'collected':
                        if (!$labOrder->collected_at) {
                            $updateData['collected_at'] = now();
                        }
                        if ($request->notes) {
                            $updateData['collection_notes'] = $request->notes;
                        }
                        break;
                        
                    case 'completed':
                        if (!$labOrder->completed_at) {
                            $updateData['completed_at'] = now();
                        }
                        break;
                }
                
                $labOrder->update($updateData);
                
                // Log the status change
                Log::info('Lab order status updated', [
                    'order_id' => $labOrder->id,
                    'order_number' => $labOrder->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => auth()->id()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الطلب بنجاح',
                'new_status' => $labOrder->status_display,
                'new_color' => $labOrder->status_color
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating lab order status', [
                'order_id' => $labOrder->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة الطلب'
            ], 500);
        }
    }

    /**
     * Add result to lab order
     */
    public function addResult(Request $request, LabOrder $labOrder)
    {
        $request->validate([
            'parameter_name' => 'required|string|max:100',
            'value' => 'required|string|max:50',
            'unit' => 'nullable|string|max:20',
            'reference_range' => 'nullable|string|max:100',
            'flag' => 'required|in:normal,high,low,critical_high,critical_low,abnormal',
            'notes' => 'nullable|string|max:500',
            'is_critical' => 'boolean'
        ]);

        try {
            $result = $labOrder->results()->create([
                'parameter_name' => $request->parameter_name,
                'value' => $request->value,
                'unit' => $request->unit,
                'reference_range' => $request->reference_range,
                'flag' => $request->flag,
                'notes' => $request->notes,
                'is_critical' => $request->boolean('is_critical')
            ]);

            // Update order status to processing if it was collected
            if ($labOrder->status === 'collected') {
                $labOrder->update(['status' => 'processing']);
            }

            Log::info('Lab result added', [
                'order_id' => $labOrder->id,
                'result_id' => $result->id,
                'parameter' => $request->parameter_name,
                'is_critical' => $request->boolean('is_critical'),
                'added_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة النتيجة بنجاح',
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding lab result', [
                'order_id' => $labOrder->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة النتيجة'
            ], 500);
        }
    }

    /**
     * Mark critical result as notified
     */
    public function markCriticalNotified(LabResult $labResult)
    {
        try {
            $labResult->update([
                'critical_notified_at' => now()
            ]);

            Log::info('Critical result marked as notified', [
                'result_id' => $labResult->id,
                'order_id' => $labResult->lab_order_id,
                'notified_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل إشعار النتيجة الحرجة'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking critical result as notified', [
                'result_id' => $labResult->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الإشعار'
            ], 500);
        }
    }

    /**
     * Get detailed order information
     */
    public function getOrderDetails(LabOrder $labOrder)
    {
        try {
            $labOrder->load([
                'patient',
                'doctor',
                'labTest',
                'results.verifiedBy'
            ]);

            return response()->json([
                'success' => true,
                'order' => $labOrder
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting lab order details', [
                'order_id' => $labOrder->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تفاصيل الطلب'
            ], 500);
        }
    }

    /**
     * Generate lab report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'report_type' => 'required|in:daily,summary,critical,turnaround'
        ]);

        try {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            
            $reportData = match($request->report_type) {
                'daily' => $this->generateDailyReport($dateFrom, $dateTo),
                'summary' => $this->generateSummaryReport($dateFrom, $dateTo),
                'critical' => $this->generateCriticalReport($dateFrom, $dateTo),
                'turnaround' => $this->generateTurnaroundReport($dateFrom, $dateTo)
            };

            return response()->json([
                'success' => true,
                'report_data' => $reportData
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating lab report', [
                'report_type' => $request->report_type,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء التقرير'
            ], 500);
        }
    }

    /**
     * Generate daily report
     */
    private function generateDailyReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $orders = LabOrder::whereBetween('ordered_at', [$dateFrom, $dateTo])
            ->with(['patient', 'labTest', 'results'])
            ->get();

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->whereIn('status', ['ordered', 'collected', 'processing'])->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'critical_results' => $orders->sum(function($order) {
                return $order->results->where('is_critical', true)->count();
            }),
            'orders_by_day' => $orders->groupBy(function($order) {
                return $order->ordered_at->format('Y-m-d');
            })->map->count()
        ];
    }

    /**
     * Generate summary report
     */
    private function generateSummaryReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $orders = LabOrder::whereBetween('ordered_at', [$dateFrom, $dateTo])
            ->with(['labTest'])
            ->get();

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'test_distribution' => $orders->groupBy('labTest.name')->map->count()->sortDesc(),
            'priority_distribution' => $orders->groupBy('priority')->map->count(),
            'status_distribution' => $orders->groupBy('status')->map->count(),
            'daily_volume' => $orders->groupBy(function($order) {
                return $order->ordered_at->format('Y-m-d');
            })->map->count()
        ];
    }

    /**
     * Generate critical results report
     */
    private function generateCriticalReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $criticalResults = LabResult::critical()
            ->whereHas('labOrder', function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('ordered_at', [$dateFrom, $dateTo]);
            })
            ->with(['labOrder.patient', 'labOrder.doctor'])
            ->get();

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'total_critical' => $criticalResults->count(),
            'notified_critical' => $criticalResults->whereNotNull('critical_notified_at')->count(),
            'pending_notification' => $criticalResults->whereNull('critical_notified_at')->count(),
            'critical_by_test' => $criticalResults->groupBy('labOrder.labTest.name')->map->count(),
            'critical_by_flag' => $criticalResults->groupBy('flag')->map->count()
        ];
    }

    /**
     * Generate turnaround time report
     */
    private function generateTurnaroundReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $completedOrders = LabOrder::byStatus('completed')
            ->whereBetween('completed_at', [$dateFrom, $dateTo])
            ->with(['labTest'])
            ->get();

        $turnaroundTimes = $completedOrders->map(function($order) {
            return [
                'order_number' => $order->order_number,
                'test_name' => $order->labTest->name,
                'turnaround_hours' => $order->ordered_at->diffInHours($order->completed_at),
                'priority' => $order->priority
            ];
        });

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'total_completed' => $completedOrders->count(),
            'avg_turnaround_hours' => $turnaroundTimes->avg('turnaround_hours'),
            'turnaround_by_test' => $turnaroundTimes->groupBy('test_name')->map(function($times) {
                return $times->avg('turnaround_hours');
            }),
            'turnaround_by_priority' => $turnaroundTimes->groupBy('priority')->map(function($times) {
                return $times->avg('turnaround_hours');
            })
        ];
    }
}