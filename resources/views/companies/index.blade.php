@extends('layouts.layout')

@section('title', 'บริษัท')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-building mr-2 text-muted"></i>บริษัท / สถานที่ออกหน่วย</h3>
            <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addCompanyModal"><i class="fas fa-plus mr-1"></i> เพิ่มบริษัท</button>
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
                        <th>ชื่อบริษัท</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
    @foreach($companies as $company)
        <tr>
            <td>{{ $company->id }}</td>
            <td style="background-color: {{ $company->color }};">{{ $company->name }}</td>
            <td>
                <button class="btn btn-light btn-sm edit-btn"
                        data-id="{{ $company->id }}"
                        data-name="{{ $company->name }}"
                        data-color="{{ $company->color }}"><i class="fas fa-pen"></i> แก้ไข
                </button>

                <button class="btn btn-light btn-sm text-danger delete-btn"
                        data-id="{{ $company->id }}"><i class="fas fa-trash-alt"></i> ลบ
                </button>
            </td>
        </tr>
    @endforeach
</tbody>
            </table>
        </div>
    </div>

    <!-- Add Company Modal -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1" role="dialog" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCompanyModalLabel">เพิ่มบริษัท</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addCompanyForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Company Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="modal-body">
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control" id="color" name="color" value="{{ old('color', $company->color ?? '#000000') }}">
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

    <!-- Edit Company Modal -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1" role="dialog" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCompanyModalLabel">แก้ไขบริษัท</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editCompanyForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_name">Company Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <input type="hidden" id="edit_id" name="id">
                    </div>
                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Color</label>
                        <input type="color" class="form-control" id="edit_color" name="color" value="{{ old('color', $company->color ?? '#000000') }}">
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

    // Handle Add Company form submission
    $('#addCompanyForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('companies.store') }}",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'Company has been added successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#addCompanyModal').modal('hide');
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
        var name = $(this).data('name');
        var color = $(this).data('color');

        // Set values in the form
        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_color').val(color);

        // Show the modal
        $('#editCompanyModal').modal('show');
    });

    // Handle Edit Company form submission
    $('#editCompanyForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#edit_id').val();

        $.ajax({
            url: '/companies/' + id,
            type: 'PUT',
            data: {
                name: $('#edit_name').val(),
                color: $('#edit_color').val(),
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Company has been updated successfully.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    $('#editCompanyModal').modal('hide');
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
                    url: "{{ url('companies') }}/" + id,
                    method: "POST",
                    data: {
                        _method: 'DELETE',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'Your company has been deleted.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    }
                });
            }
        });
    });
});
    </script>
@endsection
