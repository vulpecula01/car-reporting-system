<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'comp_id',       // เพิ่มฟิลด์ title
        'start',
        'end',
        'description',
        'comment',
        'staff',
        'department',
    ];

    // กรอง event ตามแผนก: Event::department('checkup')
    public function scopeDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // event ที่แผนกนี้ควรเห็นใน Planner:
    // งานของแผนกตัวเอง + งานที่แผนกอื่นยืมรถของแผนกนี้ไป
    public function scopeVisibleTo($query, $department)
    {
        return $query->where(function ($q) use ($department) {
            $q->where('department', $department)
              ->orWhereHas('car', function ($c) use ($department) {
                  $c->where('department', $department);
              });
        });
    }

    // หา event ที่ใช้รถคันเดียวกันในช่วงเวลาทับกัน (กันจองซ้อน รวมถึงการยืมข้ามแผนก)
    public static function findConflict($carId, $start, $end, $ignoreId = null)
    {
        return static::with('car')
            ->where('car_id', $carId)
            ->where('start', '<', $end)
            ->where('end', '>', $start)
            ->when($ignoreId, function ($q) use ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            })
            ->first();
    }

    // ข้อความแจ้งเตือนเมื่อจองชน
    public function conflictMessage(): string
    {
        $dept = User::DEPARTMENTS[$this->department] ?? $this->department;
        return 'รถ ' . optional($this->car)->name . ' ถูกจองแล้วโดยแผนก ' . $dept
            . ' ช่วง ' . date('d/m/Y H:i', strtotime($this->start)) . ' - ' . date('H:i', strtotime($this->end));
    }

    public function car()
{
    return $this->belongsTo(Car::class);
}
public function company()
{
    // คอลัมน์ในตาราง events ชื่อ comp_id (ไม่ใช่ company_id)
    return $this->belongsTo(Company::class, 'comp_id');
}
}
