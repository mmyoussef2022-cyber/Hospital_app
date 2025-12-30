<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorScheduleOverview extends Component
{
    public $selectedDate;
    public $availableSlots = [];
    public $doctors;

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadDoctors();
        $this->loadAvailableSlots();
    }

    public function loadDoctors()
    {
        $this->doctors = Doctor::where('is_active', true)
            ->where('is_available', true)
            ->with('user')
            ->get();
    }

    public function updatedSelectedDate()
    {
        $this->loadAvailableSlots();
    }

    public function loadAvailableSlots()
    {
        // Generate sample time slots for demonstration
        $slots = [];
        $startTime = Carbon::parse($this->selectedDate . ' 09:00');
        $endTime = Carbon::parse($this->selectedDate . ' 17:00');

        while ($startTime < $endTime) {
            $slots[] = [
                'time' => $startTime->format('H:i'),
                'available' => rand(0, 1) == 1, // Random availability for demo
                'doctor_id' => $this->doctors->random()->id ?? null
            ];
            $startTime->addMinutes(30);
        }

        $this->availableSlots = $slots;
    }

    public function bookSlot($time)
    {
        // Booking logic would go here
        $this->dispatch('slot-booked', ['time' => $time, 'date' => $this->selectedDate]);
    }

    public function render()
    {
        return view('livewire.doctor-schedule-overview');
    }
}