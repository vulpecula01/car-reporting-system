<!DOCTYPE html>
<html>
<head>
    <title>Edit Car</title>
</head>
<body>
    <h1>Edit Car</h1>
    <form action="{{ route('cars.update', $car->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="{{ $car->name }}" required>
        <br>

        <label for="xray_machine">X-ray Machine:</label>
        <input type="text" id="xray_machine" name="xray_machine" value="{{ $car->xray_machine }}" required>
        <br>

        <label for="license_plate">License Plate:</label>
        <input type="text" id="license_plate" name="license_plate" value="{{ $car->license_plate }}" required>
        <br>

        <label for="image">Picture:</label>
        <input type="file" id="image" name="image">
        @if($car->image)
            <img src="{{ asset('storage/' . $car->image) }}" alt="Car Image" style="width: 100px;">
        @endif
        <br>

        <button type="submit">Update</button>
    </form>
</body>
</html>
