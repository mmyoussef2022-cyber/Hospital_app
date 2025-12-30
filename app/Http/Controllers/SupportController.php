<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Display the support page.
     */
    public function index()
    {
        return view('support.index');
    }

    /**
     * Get support information as JSON.
     */
    public function getSupportInfo()
    {
        return response()->json([
            'contact' => [
                'email' => config('app.support.email'),
                'phone' => config('app.support.phone'),
                'developer' => config('app.support.developer'),
                'country' => config('app.support.country'),
            ],
            'links' => [
                'github' => 'https://github.com/myouseef/Dental_app',
                'issues' => 'https://github.com/myouseef/Dental_app/issues',
                'email_support' => 'mailto:myoussef400@gmail.com?subject=Hospital Management System Support'
            ],
            'license' => 'MIT License',
            'version' => '1.0.0'
        ]);
    }
}