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

        $firstLine = fgets($h) ?: '';
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine); 
        $delim = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        rewind($h);

        $header = fgetcsv($h, 0, $delim, '"', '\\');
        if ($header === false) {
            fclose($h);
            return;
        }
        $header = array_map(static fn($v) => is_string($v) ? trim(preg_replace('/^\xEF\xBB\xBF/', '', $v)) : $v, $header);

        while (($row = fgetcsv($h, 0, $delim, '"', '\\')) !== false) {
            if ($row === [null] || $row === [] || (count($row) === 1 && trim((string)$row[0]) === '')) {
                continue;
            }


            $row = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $row);
            if (count($row) !== count($header)) {

                continue;
            }

            $assoc = array_combine($header, $row) ?: [];

            if (!isset($assoc['name']) || trim((string)$assoc['name']) === '') {
                continue;
            }

            if (isset($assoc['attributes']) && is_string($assoc['attributes'])) {
                $decoded = json_decode($assoc['attributes'], true);
                if (is_array($decoded)) {
                    $assoc['attributes'] = $decoded;
                } else {
                    $assoc['attributes'] = [];
                }
            }

            yield $assoc;
        }

        fclose($h);
    }
}
