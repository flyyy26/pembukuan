<?php

namespace App\Support;

use App\Models\BrilinkReport;
use App\Models\Import;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImportedFileStorage
{
    public static function deleteAfterImportRemoval(?string $storedPath, ?string $fileHash): void
    {
        $disk = Storage::disk('local');
        $hasRemainingHashReferences = $fileHash !== null && (
            Import::where('file_hash', $fileHash)->exists()
            || BrilinkReport::where('file_hash', $fileHash)->exists()
        );

        if ($storedPath && !Import::where('stored_path', $storedPath)->exists()) {
            if ($disk->exists($storedPath) && !$disk->delete($storedPath)) {
                throw new RuntimeException('File import gagal dihapus dari storage.');
            }
        }

        if ($fileHash === null || $hasRemainingHashReferences) {
            return;
        }

        foreach ($disk->allFiles('imports') as $path) {
            $fullPath = $disk->path($path);
            if (!is_file($fullPath) || hash_file('sha256', $fullPath) !== $fileHash) {
                continue;
            }

            if (!$disk->delete($path)) {
                throw new RuntimeException('File import lama gagal dihapus dari storage.');
            }
        }
    }
}
