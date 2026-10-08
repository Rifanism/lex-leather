<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

/**
 * Locks the P5 polish invariants. These are the three failure modes the polish
 * pass could silently reintroduce:
 *
 *  1. a raw Tailwind default-palette utility creeping back into a view, which
 *     is how the app ended up grey/indigo/stone in the first place;
 *  2. a primitive that exists in `app.css` but never compiled, because
 *     `@layer components` output is purged until something references it;
 *  3. user-supplied text (order numbers, emails, addresses) widening a card
 *     past the viewport at 360px.
 */
class PolishTest extends TestCase
{
    use RefreshDatabase;

    private function compiledCss(): string
    {
        $manifestPath = public_path('build/manifest.json');

        // The suite does not build assets; skip rather than fail on a clean clone.
        if (! is_file($manifestPath)) {
            $this->markTestSkipped('Run `npm run build` to assert against the compiled CSS.');
        }

        $path = parse_url(Vite::asset('resources/css/app.css'), PHP_URL_PATH) ?: '';

        $this->assertFileExists($css = public_path(ltrim($path, '/')));

        return (string) file_get_contents($css);
    }

    public function test_no_view_reaches_for_a_default_palette_colour(): void
    {
        // `gray-*`/`stone-*`/`slate-*`/`indigo-*` are all still *defined* in the
        // theme (the default palette was left intact on purpose), so nothing
        // stops a future edit from using one. Nothing should.
        $offenders = [];

        foreach ($this->bladeFiles() as $file) {
            $haystack = (string) file_get_contents($file);

            preg_match_all('/\b(?:bg|text|border|ring|fill|stroke|from|to|via|decoration|divide|outline|shadow|accent|caret|placeholder)-(?:gray|stone|slate|zinc|neutral|indigo|blue|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|violet|purple|fuchsia|pink|rose)-\d{2,3}\b/', $haystack, $matches);

            foreach (array_unique($matches[0]) as $class) {
                $offenders[] = str_replace(base_path().'/', '', $file).': '.$class;
            }
        }

        $this->assertSame([], $offenders, "default-palette utilities found:\n".implode("\n", $offenders));
    }

    public function test_nav_underline_compiles_with_both_its_states(): void
    {
        $css = $this->compiledCss();

        // The `:after` rule and the hover/aria-current override must both exist,
        // otherwise the underline is invisible (the @layer-purge trap from P1).
        $this->assertMatchesRegularExpression('/\.nav-underline:after\{[^}]*transform/', $css);
        $this->assertStringContainsString('.nav-underline:hover:after', $css);
        $this->assertStringContainsString('.nav-underline[aria-current=page]:after', $css);
    }

    public function test_grain_tile_is_large_enough_that_its_repeat_is_not_visible(): void
    {
        // At a 180px tile the repeat showed up as a grid on pages taller than the
        // viewport. The compiled CSS must carry the wider tile.
        $this->assertStringContainsString('width=\'260\'', $this->compiledCss());
    }

    public function test_nav_active_state_is_exposed_to_assistive_tech(): void
    {
        $html = $this->get('/products')->assertOk()->getContent();

        $this->assertStringContainsString('nav-underline', $html);
        // `aria-current="page"` — not a colour class — marks the current section.
        $this->assertMatchesRegularExpression(
            '/<a[^>]*aria-current="page"[^>]*>/',
            $html,
            'no link is marked as the current page',
        );
    }

    /**
     * Note: there is deliberately no "every x-show has x-cloak" test. It cannot be
     * written correctly — an `x-show` whose Alpine state defaults to *true* (the
     * hamburger icon, the "Tersimpan." toast) must stay visible before Alpine
     * boots, so cloaking it would hide the one thing the user needs first. Such a
     * test only ever passes by flagging correct code. What is checkable is that
     * each collapsible surface is wired and cloaked, which is below.
     */
    public function test_every_collapsible_surface_is_wired_and_cloaked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Product::factory()->create();
        Category::factory()->create();

        // The mobile drawer: trigger + target + the panel itself cloaked. Read it
        // off the admin account page, since both /products and /profile are a 403
        // for an admin.
        $catalog = $this->actingAs($admin)->get('/admin/settings/account')->assertOk()->getContent();
        $this->assertStringContainsString('@click="open = ! open"', $catalog);
        $this->assertStringContainsString('aria-controls="mobile-nav"', $catalog);
        $this->assertMatchesRegularExpression('/id="mobile-nav"[^>]*x-cloak|x-cloak[^>]*id="mobile-nav"/', $catalog);

        // The user dropdown panel.
        $this->assertMatchesRegularExpression('/x-show="open"[^>]*x-cloak|x-cloak[^>]*x-show="open"/', $catalog);

        // The destructive-action confirm dialogs.
        foreach (['/admin/products', '/admin/categories'] as $url) {
            $html = $this->actingAs($admin)->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('x-data="{ confirm: false }"', $html, "[{$url}] lost its confirm dialog");
            $this->assertMatchesRegularExpression(
                '/x-show="confirm"[^>]*x-cloak|x-cloak[^>]*x-show="confirm"/',
                $html,
                "[{$url}] confirm dialog is not cloaked",
            );
        }
    }

    public function test_long_user_text_is_allowed_to_wrap(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create([
            'shipping_address' => 'Jalan Sangat Panjang Nomor 1234567890hunter2@contoh-domain.co.id Without Any Spaces At All',
        ]);

        $html = $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('break-anywhere', $html);
    }

    public function test_every_table_is_horizontally_scrollable(): void
    {
        foreach ($this->bladeFiles() as $file) {
            $source = (string) file_get_contents($file);

            if (! str_contains($source, '<table')) {
                continue;
            }

            $this->assertStringContainsString(
                'table-wrap',
                $source,
                str_replace(base_path().'/', '', $file).' has a <table> with no .table-wrap wrapper',
            );
        }
    }

    public function test_no_native_confirm_dialogs_anywhere(): void
    {
        foreach ($this->bladeFiles() as $file) {
            $source = (string) file_get_contents($file);

            $this->assertStringNotContainsString(
                'return confirm(',
                $source,
                str_replace(base_path().'/', '', $file).' uses a native confirm()',
            );
        }
    }

    public function test_responsive_audit_at_360px_finds_no_fixed_width_overflow(): void
    {
        // 360px is the narrowest phone this has to survive. Anything pinned
        // wider than the viewport minus its own padding will force a
        // horizontal scrollbar, so the floor is 20rem (320px), not 360px.
        $offenders = [];

        foreach ($this->bladeFiles() as $file) {
            preg_match_all('/\b(?:w|min-w)-\[(?:([0-9.]+)rem|([0-9]+)px)\]/', (string) file_get_contents($file), $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $width = isset($match[2]) && $match[2] !== ''
                    ? (int) $match[2]
                    : (float) ($match[1] ?? 0) * 16;

                if ($width > 320) {
                    $offenders[] = str_replace(base_path().'/', '', $file).': '.$match[0];
                }
            }
        }

        $this->assertSame([], $offenders, "fixed widths wider than 320px:\n".implode("\n", $offenders));
    }

    /** @return list<string> */
    private function bladeFiles(): array
    {
        $files = glob(resource_path('views').'/**/*.blade.php') ?: [];
        sort($files);

        return $files;
    }
}
