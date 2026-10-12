<!-- resources/views/car_booking/bookings.blade.php -->
@extends('layouts.layout')

@section('content')
<div class="container">
    <h2>สถานะการจองรถ X-ray</h2>
    <table>
    <thead>
        <tr>
            <th>บริษัท</th>
            <th>วันที่รับ</th>
            <th>เวลารับเริ่มต้น</th>
            <th>เวลารับสิ้นสุด</th>
            <!-- other headers -->
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->company_name }}</td>
                <td>{{ $booking->pickup_date }}</td>
                <td>{{ $booking->pickup_start_time }}</td>
                <td>{{ $booking->pickup_end_time }}</td>
                <!-- other data -->
            </tr>
        @endforeach
    </tbody>
</table>


</div>
@endsection
