<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSeeder extends Seeder
{
    // helper ringkes
    private function q(
        string $package,
        string $text,
        string $type = 'a',                 // a = 0/1/2, b = 0/1, c = 0/1/2 (tanpa problem by default)
        ?array $choices = null,             // kalau null -> default by type
        ?array $requireProblemWhen = null,  // mis. ['0'], ['0','1']
        ?string $problemLabel = null,
        ?string $counterLabel = null,
        int $order = 0
    ): array {
        // default choices
        if (is_null($choices)) {
            if ($type === 'b') {
                // boleh simpan map atau array objek. Map lebih simple.
                $choices = ['0' => 'Tidak', '1' => 'Ya'];
            } else {
                $choices = ['0' => 'Pilihan 0', '1' => 'Pilihan 1', '2' => 'Pilihan 2'];
            }
        }
        // default conditional
        if (is_null($requireProblemWhen)) {
            // Konvensi:
            // - tipe a: problem saat 0/1
            // - tipe b: problem saat 0
            // - tipe c: tidak wajib problem (bisa diubah di item tertentu)
            $requireProblemWhen = $type === 'a' ? ['0', '1'] : ($type === 'b' ? ['0'] : []);
        }
        // label default
        if (empty($requireProblemWhen)) {
            $problemLabel = null;
            $counterLabel = null;
        } else {
            $problemLabel = $problemLabel ?? 'Problem';
            $counterLabel = $counterLabel ?? 'Countermeasure';
        }

        return [
            'package' => $package,
            'question_text' => $text,
            'answer_type' => $type,
            'choices' => json_encode($choices, JSON_UNESCAPED_UNICODE),
            'require_problem_when' => json_encode($requireProblemWhen),
            'problem_label' => $problemLabel,
            'countermeasure_label' => $counterLabel,
            'display_order' => $order,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function run(): void
    {
        // bersihin dulu biar rapi saat dev
        DB::connection('mysql_control_leader')->table('questions')->truncate();

        $rows = [];

        // ==============================
        // Paket: Operator - AWAL SHIFT
        // ==============================
        $pkg = 'op_awal';
        $rows[] = $this->q(
            $pkg,
            'Apakah operator ikut 5 Minute Talk sampai selesai?',
            'a',
            ['0' => 'Tidak mengikuti 5 minutes talk', '1' => 'Mengikuti namun tidak memperhatikan', '2' => 'Mengikuti sampai selesai'],
            ['0', '1'],
            'Problem',
            'Countermeasure',
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Apakah operator melakukan 5R sebelum bekerja? (check pakai checksheet 5R)',
            'b',
            ['0' => 'Tidak melakukan 5R', '1' => 'Melakukan 5R dengan baik'],
            ['0'],
            'Reason',
            'Quick Action',
            2
        );
        $rows[] = $this->q(
            $pkg,
            'PPE (APD) lengkap sesuai area kerja?',
            'b',
            ['0' => 'Tidak lengkap', '1' => 'Lengkap'],
            ['0'],
            'Kelengkapan kurang',
            'Tindakan',
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Kondisi mesin dan area kerja bersih?',
            'a',
            ['0' => 'Kotor', '1' => 'Perlu dibersihkan', '2' => 'Bersih'],
            ['0', '1'],
            'Temuan',
            'Perbaikan',
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Komunikasi handover dari shift sebelumnya sudah dilakukan?',
            'b',
            ['0' => 'Belum', '1' => 'Sudah'],
            ['0'],
            'Alasan',
            'Tindak lanjut',
            5
        );

        // ==============================
        // Paket: Operator - SAAT BEKERJA
        // ==============================
        $pkg = 'op_bekerja';
        $rows[] = $this->q(
            $pkg,
            'Operator mengikuti instruksi kerja (WI) saat proses?',
            'a',
            ['0' => 'Tidak sesuai WI', '1' => 'Sebagian sesuai', '2' => 'Sesuai WI'],
            ['0', '1'],
            'Pelanggaran WI',
            'Countermeasure',
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Kualitas produk sesuai standar?',
            'a',
            ['0' => 'NG berat', '1' => 'NG ringan', '2' => 'OK'],
            ['0', '1'],
            'Defect',
            'Tindakan korektif',
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Penggunaan alat ukur benar?',
            'b',
            ['0' => 'Tidak benar', '1' => 'Benar'],
            ['0'],
            'Kesalahan',
            'Pelatihan/Sosialisasi',
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Safety guard terpasang & digunakan?',
            'b',
            ['0' => 'Tidak', '1' => 'Ya'],
            ['0'],
            'Bahaya',
            'Mitigasi',
            4
        );
        $rows[] = $this->q(
            $pkg,
            '5S (Seiri/Seiton/Seiso/Seiketsu/Shitsuke) dijaga selama proses?',
            'a',
            ['0' => 'Buruk', '1' => 'Cukup', '2' => 'Baik'],
            ['0', '1'],
            'Temuan 5S',
            'Perbaikan 5S',
            5
        );

        // ==============================
        // Paket: Operator - SETELAH ISTIRAHAT
        // ==============================
        $pkg = 'op_istirahat';
        $rows[] = $this->q(
            $pkg,
            'Operator kembali tepat waktu setelah istirahat?',
            'b',
            ['0' => 'Terlambat', '1' => 'Tepat waktu'],
            ['0'],
            'Alasan',
            'Pembinaan',
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Kesiapan operator setelah istirahat (fisik/mental)?',
            'a',
            ['0' => 'Tidak siap', '1' => 'Kurang siap', '2' => 'Siap'],
            ['0', '1'],
            'Catatan',
            'Pendampingan',
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Kebersihan area setelah istirahat terjaga?',
            'a',
            ['0' => 'Kotor', '1' => 'Perlu dirapikan', '2' => 'Rapi'],
            ['0', '1'],
            'Temuan',
            'Aksi 5S',
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Pengisian log sheet dilanjutkan?',
            'b',
            ['0' => 'Belum', '1' => 'Sudah'],
            ['0'],
            'Kenapa belum?',
            'Tindak lanjut',
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Komunikasi ke leader terkait issue sebelum istirahat sudah dilakukan?',
            'b',
            ['0' => 'Belum', '1' => 'Sudah'],
            ['0'],
            'Issue',
            'Follow-up',
            5
        );

        // ==============================
        // Paket: Operator - AKHIR SHIFT
        // ==============================
        $pkg = 'op_akhir';
        $rows[] = $this->q(
            $pkg,
            'Pembersihan area kerja akhir shift sudah dilakukan?',
            'a',
            ['0' => 'Belum', '1' => 'Sebagian', '2' => 'Sudah'],
            ['0', '1'],
            'Area kotor',
            'Rencana bersih-bersih',
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Serah terima ke shift berikutnya dilakukan?',
            'b',
            ['0' => 'Belum', '1' => 'Sudah'],
            ['0'],
            'Alasan',
            'Rencana Handover',
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Pengecekan material/alat selesai & tercatat?',
            'b',
            ['0' => 'Tidak', '1' => 'Ya'],
            ['0'],
            'Kekurangan',
            'Cara pemenuhan',
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Target output shift tercapai?',
            'a',
            ['0' => 'Jauh dari target', '1' => 'Kurang sedikit', '2' => 'Tercapai'],
            ['0', '1'],
            'Sebab',
            'Action plan',
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Abnormalitas selama shift dilaporkan?',
            'b',
            ['0' => 'Tidak', '1' => 'Ya'],
            ['0'],
            'Detail abnormal',
            'Penanganan',
            5
        );

        // ==============================
        // Paket: Supervisor cek Leader (sekali/hari)
        // ==============================
        $pkg = 'leader';
        $rows[] = $this->q(
            $pkg,
            'Leader melakukan 5 Minute Talk ke anggota?',
            'b',
            ['0' => 'Tidak', '1' => 'Ya'],
            ['0'],
            'Alasan',
            'Perbaikan',
            1
        );
        $rows[] = $this->q(
            $pkg,
            'Kedisiplinan hadir leader?',
            'a',
            ['0' => 'Sering absen', '1' => 'Kadang terlambat', '2' => 'Baik'],
            ['0', '1'],
            'Catatan',
            'Pembinaan',
            2
        );
        $rows[] = $this->q(
            $pkg,
            'Monitoring kualitas & safety dilakukan?',
            'b',
            ['0' => 'Tidak', '1' => 'Ya'],
            ['0'],
            'Kurang monitor',
            'Rencana monitoring',
            3
        );
        $rows[] = $this->q(
            $pkg,
            'Dokumentasi laporan harian rapi?',
            'a',
            ['0' => 'Tidak rapi', '1' => 'Cukup', '2' => 'Rapi'],
            ['0', '1'],
            'Kekurangan',
            'Perbaikan',
            4
        );
        $rows[] = $this->q(
            $pkg,
            'Tindak lanjut temuan kemarin diselesaikan?',
            'b',
            ['0' => 'Belum', '1' => 'Sudah'],
            ['0'],
            'Alasan',
            'Rencana selesai',
            5
        );

        DB::connection('mysql_control_leader')->table('questions')->insert($rows);
    }
}
