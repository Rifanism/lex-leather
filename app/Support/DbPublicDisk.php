<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\Mime\MimeTypes;

/**
 * Flysystem disk that keeps file bytes in the `media` table. On Vercel there
 * is no writable disk, so FILESYSTEM_PUBLIC_DRIVER=db swaps the public disk
 * for this adapter; local dev keeps the "local" driver and never loads it.
 * Bytes are base64-encoded in a TEXT column: identical behaviour on SQLite
 * (tests) and Postgres (Neon), where raw bytea comes back as a stream.
 */
class DbPublicDisk implements FilesystemAdapter
{
    public function fileExists(string $path): bool
    {
        return DB::table('media')->where('path', $path)->exists();
    }

    public function directoryExists(string $path): bool
    {
        $prefix = rtrim($path, '/').'/';

        return $this->fileExists($path)
            || DB::table('media')->where('path', 'like', $prefix.'%')->exists();
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->store($path, $contents);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $data = stream_get_contents($contents);

        if ($data === false) {
            throw UnableToWriteFile::fromLocation($path, 'stream could not be read');
        }

        $this->store($path, $data);
    }

    public function read(string $path): string
    {
        return base64_decode($this->row($path)->data, true) ?: '';
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        DB::table('media')->where('path', $path)->delete();
    }

    public function deleteDirectory(string $path): void
    {
        DB::table('media')->where('path', 'like', $path.'/%')->delete();
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Rows are flat; a "directory" exists as soon as a path under it does.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Everything in this disk is public by design.
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public', null, $this->row($path)->mime);
    }

    public function lastModified(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public', Carbon::parse($this->row($path)->created_at)->getTimestamp());
    }

    public function fileSize(string $path): FileAttributes
    {
        return new FileAttributes($path, (int) $this->row($path)->size, 'public');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = $path === '' ? '' : rtrim($path, '/');

        $rows = DB::table('media')
            ->when($prefix !== '', fn ($query) => $query->where('path', 'like', $prefix.'/%'))
            ->get();

        foreach ($rows as $row) {
            yield new FileAttributes(
                $row->path,
                (int) $row->size,
                'public',
                Carbon::parse($row->created_at)->getTimestamp(),
                $row->mime,
            );
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->store($destination, $this->read($source));
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        $this->store($destination, $this->read($source));
    }

    /** Absolute URL for the stored file; served by GET /media/{path}. */
    public function getUrl(string $path): string
    {
        return url('media/'.$path);
    }

    private function store(string $path, string $contents): void
    {
        $now = now();
        $row = [
            'mime' => $this->mimeFor($path),
            'size' => strlen($contents),
            'data' => base64_encode($contents),
            'updated_at' => $now,
        ];

        $updated = DB::table('media')->where('path', $path)->update($row);

        if ($updated === 0) {
            DB::table('media')->insert($row + ['path' => $path, 'created_at' => $now]);
        }
    }

    private function row(string $path): object
    {
        $row = DB::table('media')->where('path', $path)->first();

        if (! $row) {
            throw UnableToReadFile::fromLocation($path, 'no media row for that path');
        }

        return $row;
    }

    /** Derived from the extension: uploads are validated as images before they get here. */
    private function mimeFor(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return MimeTypes::getDefault()->getMimeTypes($extension)[0] ?? 'application/octet-stream';
    }
}
