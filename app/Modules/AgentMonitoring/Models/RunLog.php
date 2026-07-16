<?php

namespace App\Modules\AgentMonitoring\Models;

use Illuminate\Database\Eloquent\Model;

/** Log tiap run pipeline agent (tabel `run_logs` di SQLite agent). */
class RunLog extends Model
{
    protected $connection = 'agent';
    protected $table = 'run_logs';
    public $timestamps = false;

    protected $casts = ['created_at' => 'datetime'];
}
