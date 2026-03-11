<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    // helper ringkas
    private function q(
        string $package,
        string $text,
        ?array $choices = null,             // kalau null -> default choices
        bool $extraFields = false,          // true = perlu problem & countermeasure fields
        int $order = 0
    ): array {
        // default choices
        if (is_null($choices)) {
            $choices = ['0' => 'Pilihan 0', '1' => 'Pilihan 1', '2' => 'Pilihan 2'];
        }

        return [
            'package' => $package,
            'question_text' => $text,
            'choices' => json_encode($choices, JSON_UNESCAPED_UNICODE),
            'extra_fields' => $extraFields,
            'display_order' => $order,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function run(): void
    {
        // bersihin dulu biar rapi saat dev
        DB::table('questions')->truncate();

        $rows = [];

        // ==============================
        // Paket: Operator - AWAL SHIFT
        // ==============================
        $pkg = 'awal_shift';
        $rows[] = $this->q(
            $pkg,
            'Apakah operator ikut 5 Minute Talk sampai selesai?',
            ['0' => 'Tidak mengikuti 5 minutes talk', '1' => 'Mengikuti namun tidak memperhatikan', '2' => 'Mengikuti sampai selesai'],
            true,
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Apakah operator melakukan 5R sebelum bekerja? (check pakai checksheet 5R)',
            ['0' => 'Tidak melakukan 5R', '1' => 'Melakukan 5R dengan baik'],
            true,
            2
        );
        $rows[] = $this->q(
            $pkg,
            'PPE (APD) lengkap sesuai area kerja?',
            ['0' => 'Tidak lengkap', '1' => 'Lengkap'],
            false,
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Kondisi mesin dan area kerja bersih?',
            ['0' => 'Kotor', '1' => 'Perlu dibersihkan', '2' => 'Bersih'],
            true,
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Komunikasi handover dari shift sebelumnya sudah dilakukan?',
            ['0' => 'Belum', '1' => 'Sudah'],
            true,
            5
        );

        // ==============================
        // Paket: Operator - SAAT BEKERJA
        // ==============================
        $pkg = 'saat_bekerja';
        $rows[] = $this->q(
            $pkg,
            'Operator mengikuti instruksi kerja (WI) saat proses?',
            ['0' => 'Tidak sesuai WI', '1' => 'Sebagian sesuai', '2' => 'Sesuai WI'],
            true,
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Kualitas produk sesuai standar?',
            ['0' => 'NG berat', '1' => 'NG ringan', '2' => 'OK'],
            false,
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Penggunaan alat ukur benar?',
            ['0' => 'Tidak benar', '1' => 'Benar'],
            true,
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Safety guard terpasang & digunakan?',
            ['0' => 'Tidak', '1' => 'Ya'],
            true,
            4
        );
        $rows[] = $this->q(
            $pkg,
            '5S (Seiri/Seiton/Seiso/Seiketsu/Shitsuke) dijaga selama proses?',
            ['0' => 'Buruk', '1' => 'Cukup', '2' => 'Baik'],
            true,
            5
        );

        // ==============================
        // Paket: Operator - SETELAH ISTIRAHAT
        // ==============================
        $pkg = 'setelah_istirahat';
        $rows[] = $this->q(
            $pkg,
            'Operator kembali tepat waktu setelah istirahat?',
            ['0' => 'Terlambat', '1' => 'Tepat waktu'],
            false,
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Kesiapan operator setelah istirahat (fisik/mental)?',
            ['0' => 'Tidak siap', '1' => 'Kurang siap', '2' => 'Siap'],
            true,
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Kebersihan area setelah istirahat terjaga?',
            ['0' => 'Kotor', '1' => 'Perlu dirapikan', '2' => 'Rapi'],
            true,
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Pengisian log sheet dilanjutkan?',
            ['0' => 'Belum', '1' => 'Sudah'],
            true,
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Komunikasi ke leader terkait issue sebelum istirahat sudah dilakukan?',
            ['0' => 'Belum', '1' => 'Sudah'],
            true,
            5
        );

        // ==============================
        // Paket: Operator - AKHIR SHIFT
        // ==============================
        $pkg = 'akhir_shift';
        $rows[] = $this->q(
            $pkg,
            'Pembersihan area kerja akhir shift sudah dilakukan?',
            ['0' => 'Belum', '1' => 'Sebagian', '2' => 'Sudah'],
            true,
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Serah terima ke shift berikutnya dilakukan?',
            ['0' => 'Belum', '1' => 'Sudah'],
            true,
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Pengecekan material/alat selesai & tercatat?',
            ['0' => 'Tidak', '1' => 'Ya'],
            true,
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Target output shift tercapai?',
            ['0' => 'Jauh dari target', '1' => 'Kurang sedikit', '2' => 'Tercapai'],
            true,
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Abnormalitas selama shift dilaporkan?',
            ['0' => 'Tidak', '1' => 'Ya'],
            false,
            5
        );

        // ==============================
        // Paket: Supervisor cek Leader (sekali/hari)
        // ==============================
        $pkg = 'leader';
        $rows[] = $this->q(
            $pkg,
            'Leader melakukan 5 Minute Talk ke anggota?',
            ['0' => 'Tidak', '1' => 'Ya'],
            true,
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Kedisiplinan hadir leader?',
            ['0' => 'Sering absen', '1' => 'Kadang terlambat', '2' => 'Baik'],
            true,
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Monitoring kualitas & safety dilakukan?',
            ['0' => 'Tidak', '1' => 'Ya'],
            true,
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Dokumentasi laporan harian rapi?',
            ['0' => 'Tidak rapi', '1' => 'Cukup', '2' => 'Rapi'],
            true,
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Tindak lanjut temuan kemarin diselesaikan?',
            ['0' => 'Belum', '1' => 'Sudah'],
            true,
            5
        );

        DB::table('questions')->insert($rows);
    }
}
