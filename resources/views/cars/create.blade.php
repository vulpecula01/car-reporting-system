<!DOCTYPE html>
<html>
<head>
    <title>Add New Car</title>
</head>
<body>
    <h1>Add New Car</h1>
    <form action="{{ route('cars.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>

        <label for="xray_machine">X-ray Machine:</label>
        <input type="text" id="xray_machine" name="xray_machine" required>
        <br>

        <label for="license_plate">License Plate:</label>
        <input type="text" id="license_plate" name="license_plate" required>
        <br>

        <label for="image">Picture:</label>
        <input type="file" id="image" name="image">
        <br>

        <button type="submit">Save</button>
    </form>
</body>
</html>
