<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * `default` renders the storefront chrome (nav, footer, grain).
     * `centered` renders a bare card on the brand background for the auth
     * screens, where a cart link makes no sense.
     */
    public readonly bool $isAdmin;

    /** Where the brand mark points. Admins get /admin: / is a 403 for them. */
    public readonly string $brandUrl;

    public function __construct(
        public ?string $title = null,
        public string $variant = 'default',
    ) {
        $this->isAdmin = auth()->check() && auth()->user()->isAdmin();
        $this->brandUrl = $this->isAdmin ? route('admin.dashboard') : route('home');
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
