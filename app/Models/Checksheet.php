<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checksheet extends Model
{
    use HasFactory;

    protected $table = 'checksheets';

    protected $appends = ['remark'];

    protected $fillable = [
        'schedule_plan_id',
        'stopwatch_duration',
        'score',
        'scheduled_target',
        'phase',
        'shift',
        'target',
        'division',
        'attendance',
        'condition',
        'has_replacement',
        'replacement',
        'replacement_of_id',
        'replacement_name',
        'replacement_division',
        'replacement_condition',
    ];

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target', 'employeeID');
    }

    public function getRemarkAttribute(): string
    {
        $createdAt = Carbon::parse($this->created_at);
        $schedule = $this->getSchedule();

        if ($createdAt->diffInDays(now()) >= 7) {
            return 'Miss';
        }

        if ($createdAt->gt($schedule)) {
            return 'Late';
        }

        if ($createdAt->lt($schedule) && auth()->user()->role === 'supervisor') {
            return 'Advanced';
        }

        return 'On Time';
    }

    private function getSchedule(): Carbon
    {
        return Carbon::parse($this->created_at->format('Y-m-d'));
    }

    public function answers()
    {
        return $this->hasMany(ChecksheetAnswer::class, 'checksheet_id');
    }

    public function detail()
    {
        return $this->belongsTo(ScheduleDetail::class, 'schedule_detail_id');
    }

    public function schedulePlan()
    {
        return $this->belongsTo(SchedulePlan::class, 'schedule_plan_id');
    }

    protected static function booted()
    {
        static::created(function ($checksheet) {
            $plan = $checksheet->schedulePlan;
            $roleType = $plan->type === 'leader_checks_operator' ? 'leader' : 'supervisor';

            // 🛑 SATPAM: Kalau Supervisor, biarin lewat. Nanti diurus Cron Job H+1.
            if ($roleType === 'supervisor') {
                return;
            }

            // 👇 Kalau Leader, eksekusi Real-time di sini 👇
            $waktuIsi = $checksheet->created_at;
            $shift = $checksheet->shift;
            $phase = $checksheet->phase;

            $statusWaktu = self::cekStandarWaktu($waktuIsi, $shift, $phase);

            // Sesuai rules: Leader cuma ada "Late". Kalau kepagian (Advanced), anggap Normal.
            if ($statusWaktu === 'Advanced') {
                $statusWaktu = 'Normal';
            }

            if ($statusWaktu === 'Late') {
                ConsistencyProblem::create([
                    'user_id' => $plan->scheduler_id,
                    'inferior_id' => $checksheet->target,
                    'role_type' => 'leader',
                    'remark' => 'Late',
                    'problem' => 'Checksheet terlambat diisi',
                    // 'problem'     => "Mengisi checksheet phase {$phase} (Shift {$shift}) tidak sesuai standar waktu. Diisi pada jam: " . $waktuIsi->format('H:i'),
                    'status' => 'open',
                    'due_date' => Carbon::parse($checksheet->created_at)->addDays(2), // H+2 realtime
                ]);
            }
        });
    }

    private static function cekStandarWaktu(Carbon $waktu, $shift, $phase)
    {
        $jam = $waktu->format('H:i');
        $ranges = [
            1 => [
                'awal_shift' => ['start' => '07:00', 'end' => '08:30'],
                'saat_bekerja' => ['start' => '08:30', 'end' => '12:00'],
                'setelah_istirahat' => ['start' => '13:00', 'end' => '14:00'],
                'akhir_shift' => ['start' => '14:00', 'end' => '15:00'],
            ],
            2 => [
                'awal_shift' => ['start' => '15:00', 'end' => '16:30'],
                'setelah_istirahat' => ['start' => '19:00', 'end' => '20:00'],
                'saat_bekerja' => ['start' => '20:00', 'end' => '22:00'],
                'akhir_shift' => ['start' => '22:00', 'end' => '23:00'],
            ],
            3 => [
                'awal_shift' => ['start' => '23:00', 'end' => '00:30'], // Lewat tengah malam
                'saat_bekerja' => ['start' => '00:30', 'end' => '04:00'],
                'setelah_istirahat' => ['start' => '05:00', 'end' => '06:00'],
                'akhir_shift' => ['start' => '06:00', 'end' => '07:00'],
            ],
        ];

        // Fallback aman
        if (! isset($ranges[$shift][$phase])) {
            return 'Normal';
        }

        $start = $ranges[$shift][$phase]['start'];
        $end = $ranges[$shift][$phase]['end'];

        // Logic khusus Shift 3 Awal Shift (Karena ngelewatin pergantian hari 23:00 -> 00:30)
        if ($shift == 3 && $phase === 'awal_shift') {
            if ($jam >= '23:00' || $jam <= '00:30') {
                return 'Normal';
            }
            // Kalau ngisi jam 22:00 (sebelum 23:00) = Advanced
            if ($jam < '23:00' && $jam > '07:00') {
                return 'Advanced';
            }

            return 'Late';
        }

        // Logic normal Shift 1, Shift 2, dan sisa Shift 3
        if ($jam >= $start && $jam <= $end) {
            return 'Normal';
        }
        if ($jam < $start) {
            return 'Advanced';
        }
        if ($jam > $end) {
            return 'Late';
        }

        return 'Normal';
    }
}
