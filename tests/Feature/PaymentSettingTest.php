<?php

namespace Tests\Feature;

use App\Models\PaymentSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentSettingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_settings_can_be_saved(): void
    {
        $this->actingAs($this->admin())
            ->patch(route('admin.settings.payment.update'), [
                'bank_name' => 'Mandiri',
                'bank_account_number' => '9876543210',
                'bank_account_holder' => 'Toko Lex',
                'ewallet_provider' => 'DANA',
                'ewallet_number' => '081298765432',
                'ewallet_holder' => 'Toko Lex',
            ])
            ->assertSessionHasNoErrors();

        $setting = PaymentSetting::first();
        $this->assertSame('Mandiri', $setting->bank_name);
        $this->assertSame('DANA', $setting->ewallet_provider);
    }

    public function test_settings_update_reuses_a_single_row(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.settings.payment.update'), ['bank_name' => 'BCA']);
        $this->actingAs($admin)->patch(route('admin.settings.payment.update'), ['bank_name' => 'BNI']);

        $this->assertSame(1, PaymentSetting::count());
        $this->assertSame('BNI', PaymentSetting::first()->bank_name);
    }

    public function test_qris_image_is_stored_and_replaced(): void
    {
        Storage::fake('public');

        $admin = $this->admin();

        $this->actingAs($admin)->patch(route('admin.settings.payment.update'), [
            'qris_image' => UploadedFile::fake()->create('qris-lama.png', 50, 'image/png'),
        ])->assertSessionHasNoErrors();

        $old = PaymentSetting::first()->qris_image;
        Storage::disk('public')->assertExists($old);

        $this->actingAs($admin)->patch(route('admin.settings.payment.update'), [
            'qris_image' => UploadedFile::fake()->create('qris-baru.png', 50, 'image/png'),
        ])->assertSessionHasNoErrors();

        $setting = PaymentSetting::first();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($setting->qris_image);
    }

    public function test_qris_image_can_be_removed(): void
    {
        Storage::fake('public');

        $setting = PaymentSetting::create(['qris_image' => 'qris/ada.png']);
        Storage::disk('public')->put('qris/ada.png', 'x');

        $this->actingAs($this->admin())
            ->patch(route('admin.settings.payment.update'), ['remove_qris_image' => 1])
            ->assertSessionHasNoErrors();

        $this->assertNull($setting->fresh()->qris_image);
        Storage::disk('public')->assertMissing('qris/ada.png');
    }

    public function test_current_returns_an_empty_model_when_table_has_no_row(): void
    {
        $setting = PaymentSetting::current();

        $this->assertNull($setting->qris_image);
        $this->assertSame([], $setting->detailsFor('bank_transfer'));
    }

    public function test_customer_cannot_edit_payment_settings(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.settings.payment.edit'))
            ->assertForbidden();
    }
}
