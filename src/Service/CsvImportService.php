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

        $header = fgetcsv($h, 0, ',', '"', '\\');
        if ($header === false) {
            fclose($h);
            return;
        }
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]); 
        }

        while (($row = fgetcsv($h, 0, ',', '"', '\\')) !== false) {
            if ($row === [null] || $row === [] || (count($row) === 1 && trim((string)$row[0]) === '')) {
                continue;
            }

            if (count($row) !== count($header)) {
                $row = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $row);
            }
            if (count($row) !== count($header)) {
                continue;
            }

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
