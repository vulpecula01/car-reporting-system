@extends('layouts.layout')
@section('title', 'จัดการผู้ใช้')

@section('content')
    <div class="page-head">
        <div>
            <h1>จัดการผู้ใช้</h1>
            <p class="sub">เพิ่ม/แก้ไขบัญชี กำหนดแผนก และสิทธิ์ผู้ดูแลระบบ · ทั้งหมด {{ $users->count() }} บัญชี</p>
        </div>
        <div class="actions">
            <button type="button" class="btn btn-primary" id="btnAdd"><i class="fas fa-user-plus mr-1"></i> เพิ่มผู้ใช้</button>
        </div>
    </div>

    <div class="row">
        @foreach($departments as $key => $label)
            @php $n = $users->where('department', $key)->count(); @endphp
            <div class="col-md-4 mb-3">
                <div class="kpi {{ $key === 'checkup' ? 'tone-checkup' : '' }}">
                    <div class="kpi-icon"><i class="fas {{ $key === 'checkup' ? 'fa-heartbeat' : 'fa-x-ray' }}"></i></div>
                    <div><p class="kpi-label">แผนก {{ $label }}</p><div class="kpi-value">{{ $n }}<small>บัญชี</small></div></div>
                </div>
            </div>
        @endforeach
        <div class="col-md-4 mb-3">
            <div class="kpi tone-warn">
                <div class="kpi-icon"><i class="fas fa-user-shield"></i></div>
                <div><p class="kpi-label">ผู้ดูแลระบบ</p><div class="kpi-value">{{ $users->where('role', 'admin')->count() }}<small>บัญชี</small></div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th class="pl-4">ผู้ใช้</th><th>อีเมล</th><th>แผนก</th><th>สิทธิ์</th><th>สร้างเมื่อ</th><th class="text-right pr-4">จัดการ</th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="pl-4">
                                    <div class="d-flex align-items-center" style="gap:.6rem;">
                                        <span class="avatar {{ $user->department }}">{{ $user->initials }}</span>
                                        <div>
                                            <div class="fw-600">{{ $user->name }}</div>
                                            @if($user->id === Auth::id())<small class="text-muted">(คุณ)</small>@endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td><span class="dept-badge {{ $user->department }}">{{ $user->department_label }}</span></td>
                                <td>
                                    @if($user->isAdmin())
                                        <span class="badge badge-soft-warning"><i class="fas fa-user-shield mr-1"></i>ผู้ดูแลระบบ</span>
                                    @else
                                        <span class="badge badge-soft-secondary">ผู้ใช้งาน</span>
                                    @endif
                                </td>
                                <td class="small-muted">{{ optional($user->created_at)->format('d/m/Y') ?? '-' }}</td>
                                <td class="text-right pr-4 text-nowrap">
                                    <button type="button" class="btn btn-light btn-sm btn-edit"
                                            data-user="{{ json_encode($user->only(['id', 'name', 'email', 'department', 'role'])) }}">
                                        <i class="fas fa-pen"></i> แก้ไข
                                    </button>
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline form-delete">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm text-danger" data-name="{{ $user->name }}"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal เพิ่ม/แก้ไข --}}
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST" id="userForm" action="{{ route('users.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">เพิ่มผู้ใช้</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="ปิด"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="u_name">ชื่อผู้ใช้ (ใช้ login)</label>
                        <input type="text" class="form-control" id="u_name" name="name" required value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="u_email">อีเมล <small class="text-muted font-weight-normal">(ใช้รับแจ้งเตือน)</small></label>
                        <input type="email" class="form-control" id="u_email" name="email" required value="{{ old('email') }}">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="u_department">แผนก</label>
                            <select class="custom-select" id="u_department" name="department">
                                @foreach($departments as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label for="u_role">สิทธิ์</label>
                            <select class="custom-select" id="u_role" name="role">
                                @foreach($roles as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6 mb-0">
                            <label for="u_password">รหัสผ่าน</label>
                            <input type="password" class="form-control" id="u_password" name="password" autocomplete="new-password">
                        </div>
                        <div class="form-group col-6 mb-0">
                            <label for="u_password2">ยืนยันรหัสผ่าน</label>
                            <input type="password" class="form-control" id="u_password2" name="password_confirmation" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="form-hint" id="passHint">อย่างน้อย 6 ตัวอักษร</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i> บันทึก</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(function () {
    var storeUrl = @json(route('users.store'));
    var baseUrl = @json(url('users'));

    $('#btnAdd').on('click', function () {
        $('#userForm').attr('action', storeUrl)[0].reset();
        $('#formMethod').val('POST');
        $('#userModalTitle').text('เพิ่มผู้ใช้');
        $('#u_password').prop('required', true);
        $('#passHint').text('อย่างน้อย 6 ตัวอักษร');
        $('#userModal').modal('show');
    });

    $('.btn-edit').on('click', function () {
        var u = $(this).data('user');
        $('#userForm').attr('action', baseUrl + '/' + u.id);
        $('#formMethod').val('PUT');
        $('#userModalTitle').text('แก้ไขผู้ใช้: ' + u.name);
        $('#u_name').val(u.name); $('#u_email').val(u.email);
        $('#u_department').val(u.department); $('#u_role').val(u.role || 'user');
        $('#u_password, #u_password2').val('').prop('required', false);
        $('#passHint').text('เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยนรหัสผ่าน');
        $('#userModal').modal('show');
    });

    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        var form = this, name = $(this).find('button').data('name');
        Swal.fire({ icon: 'warning', title: 'ลบผู้ใช้ ' + name + '?', text: 'การลบไม่สามารถย้อนกลับได้', showCancelButton: true, confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก', confirmButtonColor: '#dc2626' })
            .then(function (r) { if (r.isConfirmed) form.submit(); });
    });

    @if($errors->any() && old('name'))
        $('#userModal').modal('show');
    @endif
});
</script>
@endpush
