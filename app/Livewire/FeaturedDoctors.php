<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\LandingPageSetting;

class FeaturedDoctors extends Component
{
    public $doctors;
    public $settings;
    public $limit = 6;

    public function mount()
    {
        $this->settings = LandingPageSetting::getInstance();
        $this->loadDoctors();
    }

    public function loadDoctors()
    {
        $this->doctors = Doctor::with(['user', 'services'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->limit($this->limit)
            ->get();
    }

    public function loadMore()
    {
        $this->limit += 6;
        $this->loadDoctors();
    }

    public function render()
    {
        return view('livewire.featured-doctors');
    }
}