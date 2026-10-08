<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentSettingController extends Controller
{
    private const DISK = 'public';

    public function edit(): View
    {
        return view('admin.settings.payment', [
            'setting' => PaymentSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'ewallet_provider' => ['nullable', 'string', 'max:255'],
            'ewallet_number' => ['nullable', 'string', 'max:255'],
            'ewallet_holder' => ['nullable', 'string', 'max:255'],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_qris_image' => ['nullable', 'boolean'],
        ]);

        $setting = PaymentSetting::current();

        if ($request->boolean('remove_qris_image')) {
            if ($setting->qris_image) {
                Storage::disk(self::DISK)->delete($setting->qris_image);
            }
            $data['qris_image'] = null;
        } elseif ($request->hasFile('qris_image')) {
            if ($setting->qris_image) {
                Storage::disk(self::DISK)->delete($setting->qris_image);
            }
            $data['qris_image'] = $request->file('qris_image')->store('qris', self::DISK);
        }

        unset($data['remove_qris_image']);

        $setting->fill($data)->save();

        return back()->with('status', 'Pengaturan pembayaran disimpan.');
    }
}
