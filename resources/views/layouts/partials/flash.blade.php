{{-- ส่งข้อความ success / error จาก server ไปแสดงเป็น toast (public/js/modern.js) --}}
<div id="flash-data" hidden
     data-success="{{ session('success') }}"
     data-error="{{ $errors->any() ? $errors->first() : session('error') }}"></div>
