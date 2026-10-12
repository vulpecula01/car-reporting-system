<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'role',
    ];

    // รายชื่อแผนกที่รองรับ
    public const DEPARTMENTS = [
        'xray'    => 'X-ray',
        'checkup' => 'Checkup',
    ];

    public function isXray(): bool
    {
        return $this->department === 'xray';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public const ROLES = [
        'user'  => 'ผู้ใช้งาน',
        'admin' => 'ผู้ดูแลระบบ',
    ];

    // ตัวย่อชื่อ เช่น "Anurak" -> "A" (ใช้ทำ avatar)
    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr(trim($this->name), 0, 1));
    }

    public function isCheckup(): bool
    {
        return $this->department === 'checkup';
    }

    // ชื่อแผนกสำหรับแสดงผล
    public function getDepartmentLabelAttribute(): string
    {
        return self::DEPARTMENTS[$this->department] ?? $this->department;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
