<?php

namespace App\Models\ControlLeader;

use Illuminate\Database\Eloquent\Model;

abstract class ControlLeaderModel extends Model
{
    // Semua turunan model ini otomatis pakai koneksi 'control_leader'
    protected $connection = 'mysql_control_leader';

    // Feel free: kalau kamu suka mass-assign cepat
    protected $guarded = [];
}
