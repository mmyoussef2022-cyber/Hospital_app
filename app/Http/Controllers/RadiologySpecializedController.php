<?php

namespace App\Http\Controllers;

use App\Models\RadiologyOrder;
use App\Models\RadiologyReport;
use App\Models\RadiologyStudy;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RadiologySpecializedController extends Controller
{
    /**
     * Display the specialized radiology dashboard
     */
    public function dashboard()
    {
        try {
            // Get today's statistics
            $todayStats = $this->getTodayStatistics();
            
            // Get pending orders by priority
            $pendingOrders = $this->getPendingOrdersByPriority();
            
            // Get urgent findings that need attention
            $urgentFindings = $this->getUrgentFindings();
            
            // Get overdue orders
            $overdueOrders = $this->getOverdueOrders();
            
            // Get scheduled studies for today
            $todaySchedule = $this->getTodaySchedule();
            
            // Get workload distribution
            $workloadStats = $this->getWorkloadStatistics();
            
            return view('radiology.specialized-dashboard', compact(
                'todayStats',
                'pendingOrders',
                'urgentFindings',
                'overdueOrders',
                'todaySchedule',
                'workloadStats'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error loading radiology specialized dashboard', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'حدث خطأ أثناء تحميل لوحة تحكم الأشعة');
        }
    }

    /**
     * Get today's radiology statistics
     */
    private function getTodayStatistics(): array
    {
        $today = today();
        
        return [
            'total_orders' => RadiologyOrder::whereDate('ordered_at', $today)->count(),
            'pending_orders' => RadiologyOrder::whereDate('ordered_at', $today)->pending()->count(),
            'completed_orders' => RadiologyOrder::whereDate('ordered_at', $today)->byStatus('completed')->count(),
            'reported_studies' => RadiologyOrder::whereDate('reported_at', $today)->count(),
            'urgent_orders' => RadiologyOrder::whereDate('ordered_at', $today)->urgent()->count(),
            'scheduled_today' => RadiologyOrder::scheduledToday()->count(),
            'urgent_findings' => RadiologyOrder::withUrgentFindings()->whereNull('urgent_notified_at')->count(),
            'overdue_orders' => $this->getOverdueOrdersCount()
        ];
    }

    /**
     * Get pending orders organized by priority
     */
    private function getPendingOrdersByPriority()
    {
        return RadiologyOrder::pending()
            ->with(['patient', 'doctor', 'radiologyStudy'])
            ->orderByRaw("FIELD(priority, 'stat', 'urgent', 'routine')")
            ->orderBy('ordered_at')
            ->limit(20)
            ->get()
            ->groupBy('priority');
    }

    /**
     * Get urgent findings that need immediate attention
     */
    private function getUrgentFindings()
    {
        return RadiologyOrder::withUrgentFindings()
            ->whereNull('urgent_notified_at')
            ->with(['patient', 'doctor', 'report'])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get overdue orders
     */
    private function getOverdueOrders()
    {
        $overdueOrders = collect();
        
        $orders = RadiologyOrder::pending()
            ->with(['patient', 'doctor', 'radiologyStudy'])
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
        $orders = RadiologyOrder::pending()->get();
        
        foreach ($orders as $order) {
            if ($order->isOverdue()) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get today's scheduled studies
     */
    private function getTodaySchedule()
    {
        return RadiologyOrder::scheduledToday()
            ->with(['patient', 'radiologyStudy'])
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * Get workload statistics for radiology management
     */
    private function getWorkloadStatistics(): array
    {
        $last7Days = collect();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7Days->push([
                'date' => $date->format('Y-m-d'),
                'date_display' => $date->format('M d'),
                'orders' => RadiologyOrder::whereDate('ordered_at', $date)->count(),
                'completed' => RadiologyOrder::whereDate('completed_at', $date)->count(),
                'reported' => RadiologyOrder::whereDate('reported_at', $date)->count(),
                'urgent_findings' => RadiologyOrder::whereDate('completed_at', $date)->withUrgentFindings()->count()
            ]);
        }
        
        return [
            'daily_stats' => $last7Days,
            'avg_turnaround_time' => $this->getAverageTurnaroundTime(),
            'study_distribution' => $this->getStudyDistribution(),
            'equipment_utilization' => $this->getEquipmentUtilization()
        ];
    }

    /**
     * Get average turnaround time for completed orders
     */
    private function getAverageTurnaroundTime(): float
    {
        $completedOrders = RadiologyOrder::byStatus('completed')
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
     * Get study distribution for the last 7 days
     */
    private function getStudyDistribution(): array
    {
        return RadiologyOrder::whereDate('ordered_at', '>=', now()->subDays(7))
            ->with('radiologyStudy')
            ->get()
            ->groupBy('radiologyStudy.name')
            ->map(function($orders, $studyName) {
                return [
                    'study_name' => $studyName,
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
     * Get equipment utilization (placeholder - would need equipment management system)
     */
    private function getEquipmentUtilization(): array
    {
        // This would require an equipment management system
        // For now, return sample data structure
        return [
            'total_equipment' => 8,
            'active_equipment' => 7,
            'avg_utilization' => 82,
            'peak_hours' => '10:00-14:00'
        ];
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, RadiologyOrder $radiologyOrder)
    {
        $request->validate([
            'status' => 'required|in:ordered,scheduled,in_progress,completed,cancelled,reported',
            'notes' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        try {
            DB::transaction(function() use ($request, $radiologyOrder) {
                $oldStatus = $radiologyOrder->status;
                $newStatus = $request->status;
                
                // Update data based on status change
                $updateData = ['status' => $newStatus];
                
                switch ($newStatus) {
                    case 'scheduled':
                        if ($request->scheduled_at) {
                            $updateData['scheduled_at'] = $request->scheduled_at;
                        }
                        break;
                        
                    case 'in_progress':
                        if (!$radiologyOrder->started_at) {
                            $updateData['started_at'] = now();
                        }
                        break;
                        
                    case 'completed':
                        if (!$radiologyOrder->completed_at) {
                            $updateData['completed_at'] = now();
                        }
                        break;
                        
                    case 'reported':
                        if (!$radiologyOrder->reported_at) {
                            $updateData['reported_at'] = now();
                        }
                        break;
                }
                
                if ($request->notes) {
                    $updateData['special_instructions'] = $request->notes;
                }
                
                $radiologyOrder->update($updateData);
                
                // Log the status change
                Log::info('Radiology order status updated', [
                    'order_id' => $radiologyOrder->id,
                    'order_number' => $radiologyOrder->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'updated_by' => auth()->id()
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة الطلب بنجاح',
                'new_status' => $radiologyOrder->status_display,
                'new_color' => $radiologyOrder->status_color
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating radiology order status', [
                'order_id' => $radiologyOrder->id,
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
     * Add report to radiology order
     */
    public function addReport(Request $request, RadiologyOrder $radiologyOrder)
    {
        $request->validate([
            'findings' => 'required|string|min:10',
            'impression' => 'required|string|min:5',
            'recommendations' => 'nullable|string',
            'has_urgent_findings' => 'boolean',
            'urgent_findings_description' => 'required_if:has_urgent_findings,true|string'
        ]);

        try {
            DB::transaction(function() use ($request, $radiologyOrder) {
                // Create the report
                $report = $radiologyOrder->report()->create([
                    'findings' => $request->findings,
                    'impression' => $request->impression,
                    'recommendations' => $request->recommendations,
                    'reported_by' => auth()->id(),
                    'reported_at' => now()
                ]);

                // Update order with urgent findings if applicable
                $updateData = ['status' => 'reported'];
                
                if ($request->boolean('has_urgent_findings')) {
                    $updateData['has_urgent_findings'] = true;
                    $report->update([
                        'urgent_findings' => $request->urgent_findings_description
                    ]);
                }
                
                $radiologyOrder->update($updateData);
            });

            Log::info('Radiology report added', [
                'order_id' => $radiologyOrder->id,
                'has_urgent_findings' => $request->boolean('has_urgent_findings'),
                'reported_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة التقرير بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding radiology report', [
                'order_id' => $radiologyOrder->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة التقرير'
            ], 500);
        }
    }

    /**
     * Mark urgent finding as notified
     */
    public function markUrgentNotified(RadiologyOrder $radiologyOrder)
    {
        try {
            $radiologyOrder->update([
                'urgent_notified_at' => now()
            ]);

            Log::info('Urgent finding marked as notified', [
                'order_id' => $radiologyOrder->id,
                'notified_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل إشعار النتيجة العاجلة'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking urgent finding as notified', [
                'order_id' => $radiologyOrder->id,
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
     * Schedule radiology order
     */
    public function scheduleOrder(Request $request, RadiologyOrder $radiologyOrder)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'preparation_notes' => 'nullable|string|max:500'
        ]);

        try {
            $radiologyOrder->update([
                'status' => 'scheduled',
                'scheduled_at' => $request->scheduled_at,
                'special_instructions' => $request->preparation_notes
            ]);

            Log::info('Radiology order scheduled', [
                'order_id' => $radiologyOrder->id,
                'scheduled_at' => $request->scheduled_at,
                'scheduled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم جدولة الفحص بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Error scheduling radiology order', [
                'order_id' => $radiologyOrder->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جدولة الفحص'
            ], 500);
        }
    }

    /**
     * Get detailed order information
     */
    public function getOrderDetails(RadiologyOrder $radiologyOrder)
    {
        try {
            $radiologyOrder->load([
                'patient',
                'doctor',
                'radiologyStudy',
                'report.reportedBy'
            ]);

            return response()->json([
                'success' => true,
                'order' => $radiologyOrder
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting radiology order details', [
                'order_id' => $radiologyOrder->id,
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
     * Generate radiology report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'report_type' => 'required|in:daily,summary,urgent,turnaround'
        ]);

        try {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);
            
            $reportData = match($request->report_type) {
                'daily' => $this->generateDailyReport($dateFrom, $dateTo),
                'summary' => $this->generateSummaryReport($dateFrom, $dateTo),
                'urgent' => $this->generateUrgentReport($dateFrom, $dateTo),
                'turnaround' => $this->generateTurnaroundReport($dateFrom, $dateTo)
            };

            return response()->json([
                'success' => true,
                'report_data' => $reportData
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating radiology report', [
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
        $orders = RadiologyOrder::whereBetween('ordered_at', [$dateFrom, $dateTo])
            ->with(['patient', 'radiologyStudy', 'report'])
            ->get();

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'reported_orders' => $orders->where('status', 'reported')->count(),
            'pending_orders' => $orders->whereIn('status', ['ordered', 'scheduled', 'in_progress'])->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'urgent_findings' => $orders->where('has_urgent_findings', true)->count(),
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
        $orders = RadiologyOrder::whereBetween('ordered_at', [$dateFrom, $dateTo])
            ->with(['radiologyStudy'])
            ->get();

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'study_distribution' => $orders->groupBy('radiologyStudy.name')->map->count()->sortDesc(),
            'priority_distribution' => $orders->groupBy('priority')->map->count(),
            'status_distribution' => $orders->groupBy('status')->map->count(),
            'daily_volume' => $orders->groupBy(function($order) {
                return $order->ordered_at->format('Y-m-d');
            })->map->count()
        ];
    }

    /**
     * Generate urgent findings report
     */
    private function generateUrgentReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $urgentOrders = RadiologyOrder::withUrgentFindings()
            ->whereBetween('completed_at', [$dateFrom, $dateTo])
            ->with(['patient', 'doctor', 'report'])
            ->get();

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'total_urgent' => $urgentOrders->count(),
            'notified_urgent' => $urgentOrders->whereNotNull('urgent_notified_at')->count(),
            'pending_notification' => $urgentOrders->whereNull('urgent_notified_at')->count(),
            'urgent_by_study' => $urgentOrders->groupBy('radiologyStudy.name')->map->count(),
            'urgent_by_priority' => $urgentOrders->groupBy('priority')->map->count()
        ];
    }

    /**
     * Generate turnaround time report
     */
    private function generateTurnaroundReport(Carbon $dateFrom, Carbon $dateTo): array
    {
        $reportedOrders = RadiologyOrder::byStatus('reported')
            ->whereBetween('reported_at', [$dateFrom, $dateTo])
            ->with(['radiologyStudy'])
            ->get();

        $turnaroundTimes = $reportedOrders->map(function($order) {
            return [
                'order_number' => $order->order_number,
                'study_name' => $order->radiologyStudy->name,
                'turnaround_hours' => $order->ordered_at->diffInHours($order->reported_at),
                'priority' => $order->priority
            ];
        });

        return [
            'period' => $dateFrom->format('Y-m-d') . ' إلى ' . $dateTo->format('Y-m-d'),
            'total_reported' => $reportedOrders->count(),
            'avg_turnaround_hours' => $turnaroundTimes->avg('turnaround_hours'),
            'turnaround_by_study' => $turnaroundTimes->groupBy('study_name')->map(function($times) {
                return $times->avg('turnaround_hours');
            }),
            'turnaround_by_priority' => $turnaroundTimes->groupBy('priority')->map(function($times) {
                return $times->avg('turnaround_hours');
            })
        ];
    }
}