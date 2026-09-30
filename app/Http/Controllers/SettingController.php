<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:200',
            'business_address' => 'nullable|string|max:500',
            'business_mobile' => 'nullable|string|max:50',
            'business_email' => 'nullable|email|max:100',
            'business_gstin' => 'nullable|string|max:50',
            'currency_symbol' => 'required|string|max:10',
            'invoice_terms' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, $val);
        }

        return back()->with('success', 'Settings updated successfully!');
    }
}
