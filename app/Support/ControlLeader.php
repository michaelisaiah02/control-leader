<?php

namespace App\Support;

class ControlLeader
{
    /**
     * Map arah penilaian + phase → package (5 paket)
     */
    public static function packageFor(string $direction, string $phase): string
    {
        if ($direction === 'supervisor_checks_leader') {
            return 'leader';
        }

        return match ($phase) {
            'awal_shift' => 'awal_shift',
            'saat_bekerja' => 'saat_bekerja',
            'setelah_istirahat' => 'setelah_istirahat',
            'akhir_shift' => 'akhir_shift',
            default => 'awal_shift',
        };
    }
}
