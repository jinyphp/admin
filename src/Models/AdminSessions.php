<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSessions extends Model
{
    use HasFactory;

    protected $table = 'admin_sessions';

    protected $fillable = [
        'enable',
        'title',
        'description',
        'pos',
        'depth',
        'ref',
    ];

    protected $casts = [
        'enable' => 'boolean',
    ];
}
