@extends('layouts.layout')
<style>
    
    .switch input:checked + .slider {
    background-color: #4caf50; /* สีเขียวสำหรับ active */
}

.slider {
    background-color: #ccc; /* สีเทาสำหรับ inactive */
    transition: 0.4s;
}

/* วงกลมภายใน toggle */
.slider:before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    background-color: white;
    transition: 0.4s;
}

input:checked + .slider:before {
    transform: translateX(26px); /* ขยับตำแหน่งวงกลม */
}
    </style>

@section('title', 'รายการรถยนต์')


@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bus mr-2 text-muted"></i>รายการรถ <span class="dept-badge {{ Auth::user()->department }} ml-1">{{ Auth::user()->department_label }}</span></h3>
        <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addCarModal"><i class="fas fa-plus mr-1"></i> เพิ่มรถ</button>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ชื่อ</th>
                    <th>หมายเลขทะเบียน</th>
                    @if(Auth::user()->isXray())
                    <th>เครื่องเอกซเรย์</th>
                    @endif
                    <th>สถานะการใช้งาน</th>
                    <th class="text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cars as $car)
                    <tr>
                        <td>
                            <img src="{{ $car->image ? asset('storage/'.$car->image) : asset('bus.png') }}" alt="{{ $car->name }}" class="img-thumb" onerror="this.onerror=null;this.src='{{ asset('bus.png') }}';">
                        </td>
                        <td>{{ $car->name }}</td>
                        <td>{{ $car->license_plate }}</td>
                        @if(Auth::user()->isXray())
                        <td>
                            @foreach($car->xrayMachines as $xrayMachine)
                                <span 
                                    data-id="{{ $xrayMachine->id }}"
                                    data-image="{{ asset('storage/'.$xrayMachine->image) }}"
                                    data-details="ID: {{ $xrayMachine->machine_id }}<br>Machine Name: {{ $xrayMachine->machine_name }}<br>S/N: {{ $xrayMachine->SNu }}<br>Status: {{ $xrayMachine->status }}" 
                                    class="xray-details-trigger"
                                    style="cursor: pointer;">
                                    {{ $xrayMachine->machine_id }} - {{ $xrayMachine->machine_name }}<br>
                                </span>
                            @endforeach
                        </td>
                        @endif
                        <td>
            <label class="switch">
                <input type="checkbox" class="toggle-status" 
                       data-id="{{ $car->id }}" 
                       data-service_status="{{ $car->service_status }}"
                       {{ $car->service_status === 'Active' ? 'checked' : '' }}>
                <span class="slider"></span>
            </label>
        </td>

<td>
                            <button class="btn btn-light btn-sm edit-btn" data-id="{{ $car->id }}" 
                                    data-name="{{ $car->name }}" 
                                    data-license_plate="{{ $car->license_plate }}" 
                                    data-xray_machine="{{ $car->xrayMachines->pluck('machine_name')->join(', ') }}" 
                                    data-image="{{ asset('storage/'.$car->image) }}"><i class="fas fa-pen"></i> แก้ไข
                            </button>
                            <button class="btn btn-light btn-sm text-danger delete-btn" data-id="{{ $car->id }}"><i class="fas fa-trash-alt"></i> ลบ</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add Car Modal -->
<div class="modal fade" id="addCarModal" tabindex="-1" role="dialog" aria-labelledby="addCarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCarModalLabel">เพิ่มรถ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCarForm" action="{{ route('cars.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">ชื่อรถ</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="license_plate">ป้ายทะเบียน</label>
                        <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                    </div>
                    @if(Auth::user()->isXray())
                    <div class="form-group">
    <label for="xray_machine">รายการเครื่อง X-ray</label>
    <select name="xray_machines[]" id="xray_machine" class="form-control" multiple {{ Auth::user()->isXray() ? 'required' : '' }}>
        @foreach($availableXrayMachines as $machine)
            <option value="{{ $machine->id }}">
                {{ $machine->machine_name }} ({{ $machine->machine_id }})
            </option>
        @endforeach
    </select>
</div>
                    @endif
                    <div class="form-group">
                        <label for="image">รูปภาพ</label>
                        <input type="file" class="form-control-file" id="image" name="image">
                    </div>
                    <div class="form-group">
    <label for="service_status">สถานะบริการ</label>
    <select name="service_status" id="service_status" class="form-control" required>
        <option value="active">Active</option>
        <option value="maintenance">Maintenance</option>
    </select>
</div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Car Modal -->
<div class="modal fade" id="editCarModal" tabindex="-1" role="dialog" aria-labelledby="editCarModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCarModalLabel">แก้ไขข้อมูลรถ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCarForm" action="{{ route('cars.update', 0) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_name">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_license_plate">License Plate</label>
                        <input type="text" class="form-control" id="edit_license_plate" name="license_plate" required>
                    </div>
                    @if(Auth::user()->isXray())
                    <div class="form-group">
    <label for="edit_xray_machine">X-ray Machine</label>
    <select name="xray_machines[]" id="edit_xray_machine" class="form-control" multiple>
        @foreach($availableXrayMachines as $machine)
            <option value="{{ $machine->id }}" 
                {{ in_array($machine->id, old('xray_machines', [])) ? 'selected' : '' }}>
                {{ $machine->machine_name }} ({{ $machine->machine_id }})
            </option>
        @endforeach
    </select>
</div>
                    @endif
                    <div class="form-group">
                        <label for="edit_image">Image</label>
                        <input type="file" class="form-control-file" id="edit_image" name="image">
                        <img id="edit_image_preview" src="" alt="Current Image" style="width: 100px; height: auto;">
                    </div>
                    

                    <input type="hidden" id="edit_id" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="xrayMachineDetailsModal" tabindex="-1" role="dialog" aria-labelledby="xrayMachineDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="xrayMachineDetailsModalLabel">X-ray Machine Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <img id="xrayMachineImage" src="" alt="X-ray Machine Image" style="width: 100px; height: auto;">
                <div id="xrayMachineDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    
    <script>
    $(document).ready(function () {
    // ตั้งค่าสีเริ่มต้นของ Slider ตามสถานะ
    $('.toggle-status').each(function () {
        updateSliderColor($(this)); // ฟังก์ชันปรับสี
    });

    // จัดการเมื่อเปลี่ยนสถานะ Toggle Switch
    $('.toggle-status').change(function () {
        var toggle = $(this);
        var carId = toggle.data('id');
        var status = toggle.prop('checked') ? 'Active' : 'maintenance';

        // ส่งข้อมูลไปยัง Server
        $.ajax({
            url: '{{ route("cars.updateServiceStatus") }}', // กำหนด URL
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: carId,
                service_status: status
            },
            success: function (response) {
                if (response.success) {
                    alert('สถานะอัปเดตสำเร็จ');
                    updateSliderColor(toggle); // อัปเดตสีของ Slider
                } else {
                    alert('ไม่สามารถอัปเดตสถานะได้');
                    toggle.prop('checked', !toggle.prop('checked')); // กลับสถานะเดิมหากไม่สำเร็จ
                }
            },
            error: function () {
                alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
                toggle.prop('checked', !toggle.prop('checked')); // กลับสถานะเดิมหากเกิดข้อผิดพลาด
            }
        });
    });

    // ฟังก์ชันอัปเดตสีของ Slider
    function updateSliderColor(toggle) {
        var slider = toggle.closest('.switch').find('.slider');
        var status = toggle.prop('checked') ? 'Active' : 'maintenance';
        slider.css('background-color', status === 'Active' ? 'green' : 'gray');
    }
});

</script>

<script>
      
    $(document).ready(function () {
    $('.edit-btn').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var license_plate = $(this).data('license_plate');
        var xray_machine = $(this).data('xray_machine');
        var image = $(this).data('image');
        
        $('#editCarForm').attr('action', '/cars/' + id);
        $('#edit_name').val(name);
        $('#edit_license_plate').val(license_plate);
        $('#edit_xray_machine').val(xray_machine.split(', ').map(x => parseInt(x)));
        $('#edit_image_preview').attr('src', image);
        $('#edit_id').val(id);
        
        $('#editCarModal').modal('show');
    });

    // Handle X-ray Machine Details Click
    $('.xray-details-trigger').on('click', function() {
        var image = $(this).data('image');
        var details = $(this).data('details');
        
        $('#xrayMachineImage').attr('src', image);
        $('#xrayMachineDetails').html(details);
        
        $('#xrayMachineDetailsModal').modal('show');
    });

    $('#addCarForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    alert('ข้อมูลถูกบันทึกเรียบร้อยแล้ว');
                    location.reload(); // Refresh the page
                } else {
                    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์:', error);
                // แสดงข้อความ validation จาก Laravel (422) ถ้ามี
                var msg = 'เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alert(msg);
            }
        });
    });
   

    
    $('.delete-btn').on('click', function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr'); // Gets the row to be removed

        if (confirm('Are you sure you want to delete this car?')) {
            $.ajax({
                url: '/cars/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Car deleted successfully');
                        row.remove(); // Remove the row from the table
                    } else {
                        alert('Failed to delete car');
                    }
                },
                error: function() {
                    alert('Error connecting to server');
                }
            });
        }
    });

    // Initialize DataTable
    $('#example1').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "info": true,
        "lengthChange": true,
        "responsive": true,  // Responsive support
        "autoWidth": false,
        "language": {
            "search": "ค้นหา:",
            "paginate": {
                "first": "หน้าแรก",
                "last": "หน้าสุดท้าย",
                "next": "ถัดไป",
                "previous": "ก่อนหน้า"
            },
            "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ"
        }
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)'); // Add Buttons
});
</script>


@endpush
