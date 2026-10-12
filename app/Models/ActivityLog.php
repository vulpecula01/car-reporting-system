<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * ประวัติการใช้งานระบบ (ใคร ทำอะไร เมื่อไหร่)
 *
 * ใช้งาน: ActivityLog::record('update', $event, 'แก้ไขงาน ...', $changes);
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'department', 'action',
        'subject_type', 'subject_id', 'description', 'changes', 'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    // ชื่อการกระทำภาษาไทย + สี badge
    public const ACTIONS = [
        'create' => ['เพิ่ม', 'success'],
        'update' => ['แก้ไข', 'warning'],
        'delete' => ['ลบ', 'danger'],
        'copy'   => ['คัดลอก', 'info'],
        'borrow' => ['ยืมรถ', 'primary'],
        'status' => ['เปลี่ยนสถานะ', 'secondary'],
        'login'  => ['เข้าสู่ระบบ', 'light'],
        'logout' => ['ออกจากระบบ', 'light'],
        'password' => ['เปลี่ยนรหัสผ่าน', 'dark'],
    ];

    public const SUBJECTS = [
        'event'   => 'งาน/การจอง',
        'car'     => 'รถ',
        'company' => 'บริษัท',
        'machine' => 'เครื่อง X-ray',
        'user'    => 'ผู้ใช้',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action][0] ?? $this->action;
    }

    public function getActionColorAttribute(): string
    {
        return self::ACTIONS[$this->action][1] ?? 'secondary';
    }

    public function getSubjectLabelAttribute(): string
    {
        return self::SUBJECTS[$this->subject_type] ?? (string) $this->subject_type;
    }

    /**
     * บันทึก log — ไม่ทำให้การทำงานหลักล้มเหลวถ้าบันทึกไม่สำเร็จ
     *
     * @param string      $action  create/update/delete/...
     * @param mixed       $subject Model หรือ [type, id]
     * @param string      $description ข้อความอ่านง่าย
     * @param array|null  $changes ['field' => [old, new]]
     */
    public static function record(string $action, $subject = null, string $description = '', ?array $changes = null, $user = null): void
    {
        try {
            $user = $user ?: Auth::user();
            [$type, $id] = self::subjectInfo($subject);

            static::create([
                'user_id'      => $user?->id,
                'user_name'    => $user?->name,
                'department'   => $user?->department,
                'action'       => $action,
                'subject_type' => $type,
                'subject_id'   => $id,
                'description'  => $description,
                'changes'      => $changes ?: null,
                'ip_address'   => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('ActivityLog failed: ' . $e->getMessage());
        }
    }

    /**
     * เทียบค่าก่อน/หลังแก้ไข คืนเฉพาะ field ที่เปลี่ยน
     */
    public static function diff(array $before, array $after, array $only = []): array
    {
        $changes = [];
        foreach ($after as $key => $new) {
            if ($only && !in_array($key, $only, true)) continue;
            if (in_array($key, ['updated_at', 'created_at', 'password', 'remember_token'], true)) continue;
            $old = $before[$key] ?? null;
            if ((string) $old !== (string) $new) {
                $changes[$key] = [$old, $new];
            }
        }
        return $changes;
    }

    private static function subjectInfo($subject): array
    {
        if (is_array($subject)) return [$subject[0] ?? null, $subject[1] ?? null];
        if (!$subject) return [null, null];
        $map = [
            Event::class => 'event', Car::class => 'car', Company::class => 'company',
            XrayMachine::class => 'machine', User::class => 'user',
        ];
        return [$map[get_class($subject)] ?? class_basename($subject), $subject->getKey()];
    }
}
