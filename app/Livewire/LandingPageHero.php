<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LandingPageSetting;

class LandingPageHero extends Component
{
    public $settings;
    public $stats = [
        'patients' => 5000,
        'doctors' => 15,
        'services' => 25,
        'experience' => 10
    ];

    public function mount()
    {
        $this->settings = LandingPageSetting::getInstance();
    }

    public function render()
    {
        return view('livewire.landing-page-hero');
    }
}