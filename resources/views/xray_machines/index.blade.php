@extends('layouts.layout')


@section('title', 'เครื่อง X-ray')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-x-ray mr-2 text-muted"></i>เครื่อง X-ray</h3>
            <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addXrayMachineModal"><i class="fas fa-plus mr-1"></i> เพิ่มเครื่อง</button>
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
                        <th>ID</th>
                        <th>ชื่อเครื่อง</th>
                        <th>Serial Number</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machines as $machine)
                        <tr>
                            <td>{{ $machine->machine_id }}</td>
                            <td>{{ $machine->machine_name }}</td>
                            <td>{{ $machine->SNu }}</td>
                            <td>{{ $machine->status }}</td>
                            <td>
                                <button class="btn btn-light btn-sm edit-btn"
                                        data-id="{{ $machine->id }}"
                                        data-machine_id="{{ $machine->machine_id }}"
                                        data-machine_name="{{ $machine->machine_name }}"
                                        data-SNu="{{ $machine->SNu }}"
                                        data-status="{{ $machine->status }}"><i class="fas fa-pen"></i> แก้ไข
                                </button>
                                
                                <button class="btn btn-light btn-sm text-danger delete-btn"
                                        data-id="{{ $machine->id }}"><i class="fas fa-trash-alt"></i> ลบ
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add X-ray Machine Modal -->
    <div class="modal fade" id="addXrayMachineModal" tabindex="-1" role="dialog" aria-labelledby="addXrayMachineModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addXrayMachineModalLabel">เพิ่มเครื่อง X-ray</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addXrayMachineForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="machine_id">Machine ID</label>
                        <input type="text" class="form-control" id="machine_id" name="machine_id" required>
                    </div>
                    <div class="form-group">
                        <label for="machine_name">Machine Name</label>
                        <input type="text" class="form-control" id="machine_name" name="machine_name" required>
                    </div>
                    <div class="form-group">
                        <label for="SNu">Serial Number (S/N)</label>
                        <input type="text" class="form-control" id="SNu" name="SNu" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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

    <!-- Edit X-ray Machine Modal -->
    <!-- Edit X-ray Machine Modal -->
<div class="modal fade" id="editXrayMachineModal" tabindex="-1" role="dialog" aria-labelledby="editXrayMachineModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editXrayMachineModalLabel">แก้ไขเครื่อง X-ray</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editXrayMachineForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                <div class="form-group">
                    <label for="edit_machine_id">Machine ID</label>
                        <input type="text" class="form-control" id="edit_machine_id" name="machine_id" required>
                </div>
                    <div class="form-group">
                        <label for="edit_machine_name">Machine Name</label>
                        <input type="text" class="form-control" id="edit_machine_name" name="machine_name" required>
                    </div>
                    <div class="form-group">
    <label for="edit_SNu">Serial Number (S/N)</label>
    <input type="text" class="form-control" id="edit_SNu" name="SNu" required>
</div>
                    <div class="form-group">
                        <label for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
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


    <!-- JS code to handle modals and AJAX -->
    <script src="{{ asset('adminlte/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <script>
$(function () {
    // Initialize DataTable
    $("#example1").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

    // Set up CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle Add X-ray Machine form submission
    $('#addXrayMachineForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('xray_machines.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'X-ray machine has been added successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#addXrayMachineModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'An error occurred: ' + xhr.responseText
                });
            }
        });
    });

    // Use event delegation for Edit button
    $('#example1').on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var machine_id = $(this).data('machine_id');
        var machine_name = $(this).data('machine_name');
        var SNu = $(this).data('SNu');
        var status = $(this).data('status');

        // Debugging: Check the values
        console.log({id, machine_id, machine_name, SNu, status});

        // Set values in the form
        $('#edit_id').val(id);
        $('#edit_machine_id').val(machine_id);
        $('#edit_machine_name').val(machine_name);
        $('#edit_SNu').val(SNu);
        $('#edit_status').val(status);

        // Show the modal
        $('#editXrayMachineModal').modal('show');
    });

    // Handle Edit X-ray Machine form submission
    $('#editXrayMachineForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();

        $.ajax({
            url: '/machines/' + id,
            type: 'PUT',
            data: {
                machine_id: $('#edit_machine_id').val(),
                machine_name: $('#edit_machine_name').val(),
                SNu: $('#edit_SNu').val(),
                status: $('#edit_status').val()
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'X-ray machine has been updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#editXrayMachineModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Update failed',
                    text: 'An error occurred: ' + xhr.statusText
                });
            }
        });
    });

    // Use event delegation for Delete button
    $('#example1').on('click', '.delete-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('machines') }}/" + id,
                    method: "POST",
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'X-ray machine has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'An error occurred: ' + xhr.responseText
                        });
                    }
                });
            }
        });
    });
});
    </script>
@endsection
