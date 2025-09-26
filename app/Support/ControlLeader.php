<?php

namespace App\Support;

class ControlLeader
{
    /**
     * Map arah penilaian + phase → package (5 paket)
     */
    public static function packageFor(string $direction, string $phase): string
    {
        if ($direction === 'supervisor_checks_leader')
            return 'leader';

        return match ($phase) {
            'awal_shift' => 'op_awal',
            'saat_bekerja' => 'op_bekerja',
            'setelah_istirahat' => 'op_istirahat',
            'akhir_shift' => 'op_akhir',
            default => 'op_awal',
        };
    }
}
