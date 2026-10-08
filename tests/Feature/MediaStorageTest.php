<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The Vercel build has no writable disk, so FILESYSTEM_PUBLIC_DRIVER=db swaps
 * the public disk for one backed by the media table. Local runs never use
 * that driver, so this test is the only coverage it gets.
 */
class MediaStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_disk_stores_serves_and_deletes_media_rows(): void
    {
        config(['filesystems.disks.public.driver' => 'db']);
        Storage::forgetDisk('public');

        $path = 'products/hero.jpg';
        Storage::disk('public')->put($path, 'jpeg-bytes');

        $this->assertDatabaseHas('media', ['path' => $path, 'mime' => 'image/jpeg', 'size' => strlen('jpeg-bytes')]);
        $this->assertStringEndsWith('/media/'.$path, Storage::disk('public')->url($path));
        $this->assertSame('jpeg-bytes', Storage::disk('public')->get($path));

        $response = $this->get('/media/'.$path);
        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame('jpeg-bytes', $response->baseResponse->getContent());

        // Replacing a file updates the row instead of duplicating the path.
        Storage::disk('public')->put($path, 'replaced');
        $this->assertSame('replaced', Storage::disk('public')->get($path));
        $this->assertSame(1, DB::table('media')->where('path', $path)->count());

        Storage::disk('public')->delete($path);
        $this->assertDatabaseMissing('media', ['path' => $path]);
        $this->get('/media/'.$path)->assertNotFound();
    }
}
