<?php

$processedDataPath = './processed-data/csv';
$rawDataPath = './raw-data';
$rawDataFiles = getRawDataFiles($rawDataPath);
foreach ($rawDataFiles as $rawDataFile) {
    $rawData = getRawDataFromFile($rawDataPath . '/' . $rawDataFile);
    writeCSV($processedDataPath . '/' . $rawDataFile, $rawData);
}

function writeCSV(string $fileName, array $data): void
{
    $f = fopen($fileName, 'w+');
    if ($f === false) {
        throw new RuntimeException(
            sprintf(
                'failed to open file for writing: %s',
                $fileName
            )
        );
    }

    if (empty($data)) {
        fclose($f);
        return;
    }

    $headers = array_keys($data[0]);
    if (fputcsv($f, $headers) === false) {
        fclose($f);
        throw new RuntimeException(
            sprintf(
                'failed to write file: %s',
                $fileName
            )
        );
    }

    foreach ($data as $datum) {
        if (fputcsv($f, $datum) === false) {
            fclose($f);
            throw new RuntimeException(
                sprintf(
                    'failed to write file: %s',
                    $fileName
                )
            );
        }
    }

    fclose($f);
}

function getRawDataFromFile(string $fileName): array {
    $f = fopen($fileName, 'r');
    if ($f === false) {
        throw new RuntimeException(
            sprintf(
                'failed to open file for reading: %s',
                $fileName
            )
        );
    }

    $data = [];
    for ($line = fgets($f); $line !== false; $line = fgets($f)) {
        $decoded = json_decode($line, true);
        if ($decoded === null) {
            continue;
        }
        $data[] = $decoded;
    }

    fclose($f);
    return $data;
}

function getRawDataFiles(string $path): array
{
    $files = scandir($path);
    if ($files === false) {
        throw new RuntimeException(
            sprintf(
                'failed to scan path: %s',
                $path
            )
        );
    }
    return array_filter(
        $files,
        function ($name) {
            if (is_dir($name)) {
                return false;
            }
            return str_ends_with($name, '.csv');
        }
    );
}
