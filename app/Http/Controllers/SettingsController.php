<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:ceo');
    }

    public function edit()
    {
        return view('settings.edit', [
            'companyName' => Setting::get('company_name', 'Artgroups'),
            'companyLogo' => Setting::get('company_logo'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:100',
            'company_logo' => 'nullable|image|mimes:jpeg,png,webp,svg|max:2048',
        ]);

        Setting::set('company_name', $request->company_name);

        if ($request->hasFile('company_logo')) {
            $old = Setting::get('company_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('company_logo')->store('logos', 'public');
            Setting::set('company_logo', $path);
        }

        return back()->with('success', 'Настройки сохранены.');
    }

    public function destroyLogo()
    {
        $old = Setting::get('company_logo');
        if ($old) {
            Storage::disk('public')->delete($old);
        }
        Setting::set('company_logo', null);

        return back()->with('success', 'Логотип удалён.');
    }
}
