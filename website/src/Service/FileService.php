<?php

declare(strict_types=1);

namespace App\Service;

class FileService
{
    public function getHumanFileSize(int $size): string
    {
        $unit = 'Ko';
        $humanSize = ((float) $size) / 1024.;

        if ($humanSize > 1024. * 1.2) {
            $unit = 'Mo';
            $humanSize = $humanSize / 1024.;
        }

        if ($humanSize > 1024. * 1.2) {
            $unit = 'Go';
            $humanSize = $humanSize / 1024.;
        }

        return number_format($humanSize, 1, '.', '') . ' ' . $unit;
    }
}
