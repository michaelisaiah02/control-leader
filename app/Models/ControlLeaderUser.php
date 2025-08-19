<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ControlLeaderUser extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Beri tahu model ini untuk selalu menggunakan koneksi 'mysql_control_leader'.
     */
    protected $connection = 'mysql_control_leader';

    /**
     * Beri tahu model ini nama tabelnya adalah 'users'.
     */
    protected $table = 'users';

    protected $fillable = [
        'name',
        'employeeID',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];
}
