@php $thMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม']; @endphp
<form action="{{ route('report.generate') }}" method="POST" class="form-row align-items-end">
    @csrf
    <div class="form-group col-sm-6 col-md-3">
        <label for="start_month">เดือนเริ่มต้น</label>
        <select name="start_month" id="start_month" class="custom-select" required>
            @for ($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ (old('start_month', $start_month ?? 1) == $i) ? 'selected' : '' }}>{{ $thMonths[$i] }}</option>
            @endfor
        </select>
    </div>
    <div class="form-group col-sm-6 col-md-3">
        <label for="end_month">เดือนสิ้นสุด</label>
        <select name="end_month" id="end_month" class="custom-select" required>
            @for ($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}" {{ (old('end_month', $end_month ?? now()->month) == $i) ? 'selected' : '' }}>{{ $thMonths[$i] }}</option>
            @endfor
        </select>
    </div>
    <div class="form-group col-sm-6 col-md-2">
        <label for="year">ปี (ค.ศ.)</label>
        <input type="number" name="year" id="year" class="form-control" value="{{ old('year', $year ?? date('Y')) }}" required>
    </div>
    <div class="form-group col-sm-6 col-md-4">
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search mr-1"></i> แสดงรายงาน</button>
    </div>
</form>
