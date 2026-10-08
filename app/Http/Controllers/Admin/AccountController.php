<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The admin's own account screen — the counterpart to the customer's /profile.
 *
 * Deliberately smaller than the customer profile: no phone, no address (both
 * are checkout prefill, which an admin never reaches) and no self-deletion
 * (this app seeds exactly one admin and `role` is not mass assignable, so an
 * admin who deleted themselves could not be replaced from the UI).
 *
 * Password changes are not handled here: they reuse Breeze's
 * `route('password.update')`, which is auth-only and already correct.
 */
class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.settings.account', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($request->user()->id),
            ],
        ]);

        $user = $request->user();
        $user->fill($data);
        $user->save();

        return redirect()
            ->route('admin.settings.account.edit')
            ->with('status', 'account-updated');
    }
}
