<?php

namespace App\Auth\Support;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class Base64ImageStorage
{
    /**
     * Decode a base64 image string, persist it to the given disk directory, and return its path.
     */
    public function store(string $base64Data, string $directory, string $prefix = 'img_'): string
    {
        preg_match('/^data:image\/(jpeg|jpg|png|webp);base64,/i', $base64Data, $matches);
        $extension = $matches[1] ?? 'jpg';
        $extension = $extension === 'jpg' ? 'jpeg' : $extension;

        $raw = preg_replace('/^data:image\/(jpeg|jpg|png|webp);base64,/i', '', $base64Data);
        $decoded = base64_decode($raw, true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid base64 image data.');
        }

        $filename = uniqid($prefix, true).'.'.$extension;
        $path = $directory.'/'.$filename;

        Storage::disk(config('filesystems.private_disk'))->put($path, $decoded);

        return $path;
    }
}
