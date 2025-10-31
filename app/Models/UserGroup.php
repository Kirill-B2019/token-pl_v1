<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;

    // |KB Модель групп пользователей для управления доступом при банковских операциях
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    /**
     * Связь: пользователи, состоящие в группе
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_group_user')
            ->withPivot(['assigned_by', 'comment'])
            ->withTimestamps();
    }
}


