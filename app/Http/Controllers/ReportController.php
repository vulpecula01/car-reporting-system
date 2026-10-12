<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exports\EventsExport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    // แสดงหน้าเลือกเดือนและปี
    public function index()
    {
        return view('report.index');
    }

    // ดึงข้อมูลจากตาราง events ตามเดือนและปีที่เลือก
    public function generateReport(Request $request)
    {
        $start_month = $request->input('start_month');
        $end_month = $request->input('end_month');
        $year = $request->input('year');
    
        // ตรวจสอบว่าผู้ใช้กรอกข้อมูลครบถ้วนหรือไม่
        if (!$start_month || !$end_month || !$year) {
            return back()->withErrors(['message' => 'กรุณาเลือกเดือนเริ่มต้น, เดือนสิ้นสุด และปี']);
        }
    
        // ดึงข้อมูลจาก events ที่อยู่ในช่วงเดือนที่เลือก
        $events = Event::department(Auth::user()->department)->whereYear('start', $year)
                       ->whereMonth('start', '>=', $start_month)
                       ->whereMonth('start', '<=', $end_month)
                       ->get();
    
        // ส่งข้อมูลไปยัง View สำหรับแสดงรายงาน
        return view('report.result', compact('events', 'start_month', 'end_month', 'year'));
    }
    
    public function export(Request $request)
{
    $start_month = $request->input('start_month');
    $end_month = $request->input('end_month');
    $year = $request->input('year', date('Y'));

    $events = Event::department(Auth::user()->department)->whereYear('start', $year)
                    ->whereMonth('start', '>=', $start_month)
                    ->whereMonth('start', '<=', $end_month)
                    ->get();

    // สร้าง Spreadsheet ใหม่
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // ตั้งชื่อ sheet
    $sheet->setTitle('รายงานใช้รถ');
    
    // ตั้งชื่อคอลัมน์
    $sheet->setCellValue('A1', 'วันที่เริ่ม');
    $sheet->setCellValue('B1', 'วันที่สิ้นสุด');
    $sheet->setCellValue('C1', 'รายละเอียด');
    $sheet->setCellValue('D1', 'จำนวนคนที่ออกหน่วย');
    $sheet->setCellValue('E1', 'หมายเหตุ');
    
    
    // เติมข้อมูลใน sheet
    $row = 2; // เริ่มต้นแถวที่ 2 สำหรับข้อมูล
    foreach ($events as $event) {
        $sheet->setCellValue('A' . $row, $event->start);
        $sheet->setCellValue('B' . $row, $event->end);
        $sheet->setCellValue('C' . $row, $event->description);
        $sheet->setCellValue('D' . $row, $event->staff);
        $sheet->setCellValue('E' . $row, $event->comment);
        $row++;
    }

    // ส่งออกไฟล์ Excel
    $writer = new Xlsx($spreadsheet);

    // กำหนดชื่อไฟล์ที่ต้องการ
    $filename = 'รายงานการจองรถ_'. $year . '_' . $start_month . '_' . $end_month .'.xlsx';

    // ส่งไฟล์ไปยัง browser
    return response()->stream(
        function() use ($writer) {
            $writer->save('php://output');
        },
        200,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="'. $filename .'"',
            'Cache-Control' => 'max-age=0',
        ]
    );
}
}
