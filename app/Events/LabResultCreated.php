<?php

namespace App\Events;

use App\Models\LabResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LabResultCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $labResult;

    public function __construct(LabResult $labResult)
    {
        $this->labResult = $labResult;
    }
}