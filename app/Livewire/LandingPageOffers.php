<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LandingPageOffer;
use Carbon\Carbon;

class LandingPageOffers extends Component
{
    public $offers;
    public $featuredOffer;

    public function mount()
    {
        $this->loadOffers();
    }

    public function loadOffers()
    {
        $this->offers = LandingPageOffer::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', Carbon::today());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $this->featuredOffer = $this->offers->first();
    }

    public function claimOffer($offerId)
    {
        $offer = LandingPageOffer::find($offerId);
        if ($offer && $offer->isActive()) {
            // Logic to claim offer
            $this->dispatch('offer-claimed', ['offer_id' => $offerId]);
        }
    }

    public function shareOffer($offerId)
    {
        $offer = LandingPageOffer::find($offerId);
        if ($offer) {
            $shareUrl = url('/offers/' . $offer->id);
            $this->dispatch('offer-shared', ['url' => $shareUrl]);
        }
    }

    public function render()
    {
        return view('livewire.landing-page-offers');
    }
}