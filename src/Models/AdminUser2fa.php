<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminUser2fa extends Model
{
    use HasFactory;

    protected $table = 'admin_user2fas';

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
