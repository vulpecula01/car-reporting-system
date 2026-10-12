<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EventExport implements FromCollection, WithHeadings
{
    protected $year;
    protected $month;

    public function __construct($year, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
    }

    // ดึงข้อมูลตามปีและเดือนที่เลือก
    public function collection()
    {
        $query = Event::whereYear('start', $this->year);

        if ($this->month) {
            $query->whereMonth('start', $this->month);
        }

        return $query->get(['start', 'end', 'title', 'description']);
    }

    // เพิ่มหัวตาราง
    public function headings(): array
    {
        return ["วันที่เริ่ม", "วันที่สิ้นสุด", "ชื่อเหตุการณ์", "รายละเอียด"];
    }
}
