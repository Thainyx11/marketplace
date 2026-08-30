<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression guard for a bug found while testing the deployed site: the
 * dark/light toggle (resources/views/components/theme-toggle.blade.php)
 * only sets a class on <html> client-side — nothing server-rendered ever
 * reflects it, so this can't be asserted with assertSee on the toggle
 * itself. What *can* be asserted is that the fix stays in place: the
 * inline theme script (resources/views/partials/theme-init.blade.php)
 * must re-apply the stored theme after every Livewire wire:navigate
 * transition, not just on first page load.
 *
 * Confirmed live before the fix: forcing dark mode then clicking any
 * wire:navigate link reset <html> to no class at all for the entire
 * transition, even though localStorage still held 'dark' — the theme
 * script only ever ran once, on the very first full page load.
 */
class ThemePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_theme_script_reapplies_after_livewire_navigation(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee("document.addEventListener('livewire:navigated', applyTheme)", false);
    }
}
