<?php

namespace App\Http\Controllers;

use App\AppSettingManager;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AppSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.app', [
            'setting' => AppSettingManager::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_description' => ['nullable', 'string', 'max:1000'],
            'app_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'app_favicon' => ['nullable', 'mimes:jpg,jpeg,png,webp,ico', 'max:512'],
        ]);

        /** @var AppSetting $setting */
        $setting = AppSetting::query()->first() ?? new AppSetting();

        $setting->app_name = trim($validated['app_name']);
        $setting->app_description = isset($validated['app_description'])
            ? trim((string) $validated['app_description'])
            : null;

        if ($request->hasFile('app_logo')) {
            $setting->app_logo = $this->storeImage($request->file('app_logo'), $setting->app_logo);
        }

        if ($request->hasFile('app_favicon')) {
            $setting->app_favicon = $this->storeImage($request->file('app_favicon'), $setting->app_favicon);
        }

        $setting->save();
        AppSettingManager::refreshCache();

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui.');
    }

    private function storeImage($file, ?string $existingPath): string
    {
        if ($existingPath && Storage::disk('public')->exists($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        return $file->store('app-settings', 'public');
    }
}
