<?php
declare(strict_types=1);

namespace App\Service;

final class CsvImportService
{
    /** @return iterable<array<string,mixed>> */
    public function read(string $path): iterable
    {
        $h = fopen($path, 'rb');
        if ($h === false) {
            throw new \RuntimeException(sprintf('Cannot open %s', $path));
        }

        $header = fgetcsv($h);
        if ($header === false) {
            fclose($h);
            return;
        }

        while (($row = fgetcsv($h)) !== false) {
            $assoc = array_combine($header, $row) ?: [];

            if (isset($assoc['attributes']) && is_string($assoc['attributes'])) {
                $decoded = json_decode($assoc['attributes'], true);
                if (is_array($decoded)) {
                    $assoc['attributes'] = $decoded;
                }
            }

            yield $assoc;
        }

        fclose($h);
    }
}
