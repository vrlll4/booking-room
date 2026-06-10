<?php
// الاتصال بقاعدة بيانات الجامعة
$conn = new mysqli("localhost", "root", "", "tutor_booking");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$showSuccess = false;

// استقبال بيانات الحجز بناءً على سيناريو الجامعة الجديد
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tutor_name = $_POST['tutor_name'];
    $class_name = $_POST['class_name'];
    $group_size = $_POST['group_size'];
    $room_id = $_POST['room_id'];
    $booking_date = $_POST['booking_date'];
    $duration_hours = $_POST['duration_hours'];
    
    // حساب التكلفة التقديرية بناءً على نوع القاعة وسعتها
    $result = $conn->query("SELECT capacity FROM rooms WHERE room_id = $room_id");
    $room = $result->fetch_assoc();
    $total_cost = $duration_hours * ($room['capacity'] * 0.5); // معادلة افتراضية للتكلفة

    // إدخال الحجز في جدول حجز القاعات الأكاديمية
    $stmt = $conn->prepare("INSERT INTO classroom_bookings (tutor_name, class_name, group_size, room_id, booking_date, duration_hours, total_cost) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisid", $tutor_name, $class_name, $group_size, $room_id, $booking_date, $duration_hours, $total_cost);
    
    if ($stmt->execute()) { $showSuccess = true; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Classroom Allocation System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
        .navbar { background-color: #4f46e5; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h1 { margin: 0; font-size: 20px; }
        .container { max-width: 500px; margin: 40px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { text-align: center; color: #1f2937; margin-bottom: 25px; }
        label { display: block; margin: 12px 0 6px; color: #4b5563; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        button { width: 100%; background-color: #4f46e5; color: white; padding: 12px; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; transition: background 0.2s; }
        button:hover { background-color: #4338ca; }
        .success-box { background-color: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; text-align: center; margin-top: 20px; font-weight: bold; }
        footer { text-align: center; padding: 20px; color: #9ca3af; font-size: 14px; margin-top: 40px; }
    </style>
</head>
<body>

    <div class="navbar">
        <h1>University Portal: Classroom Allocation Page</h1>
        <div><strong>Role:</strong> Academic Tutor</div>
    </div>

    <div class="container">
        <h2>Classroom Allocation Form</h2>
        
        <form method="POST" action="">
            <label>Tutor Name:</label>
            <input type="text" name="tutor_name" placeholder="Enter your full academic name" required>

            <label>Class Activity / Lesson Type:</label>
            <input type="text" name="class_name" placeholder="e.g. Java Programming Lab, Math Lecture" required>

            <label>Expected Group Size (عدد الطلاب):</label>
            <input type="number" name="group_size" min="1" placeholder="e.g. 35" required>

            <label>Select Room Allocation & Layout:</label>
            <select name="room_id" id="room_select" onchange="calculateTotal()" required>
                <option value="1" data-factor="60">Hall A - Lecture Theatre (Max: 120)</option>
                <option value="2" data-factor="40">Lab 302 - Computer Room (Max: 30)</option>
                <option value="3" data-factor="20">Class 105 - Standard Classroom (Max: 40)</option>
            </select>

            <label>Start Date & Time:</label>
            <input type="date" name="booking_date" required>

            <label>Duration (Hours):</label>
            <input type="number" name="duration_hours" id="duration_hours" min="1" max="6" value="1" onchange="calculateTotal()" required>

            <label>Calculated Overhead Cost ($):</label>
            <input type="text" id="total_price" readonly style="background-color: #f3f4f6; font-weight: bold; color: #4f46e5;">

            <button type="submit">Confirm Classroom Reservation</button>
        </form>

        <?php if ($showSuccess): ?>
            <div class="success-box">🎉 Classroom Allocated Successfully! Timetable generated.</div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; 2026 University Resource Management System. All Rights Reserved.
    </footer>

    <script>
    function calculateTotal() {
        var roomSelect = document.getElementById('room_select');
        var factor = parseFloat(roomSelect.options[roomSelect.selectedIndex].getAttribute('data-factor'));
        var hours = parseInt(document.getElementById('duration_hours').value) || 1;
        document.getElementById('total_price').value = "$" + (factor * hours);
    }
    window.onload = calculateTotal;
    </script>
</body>
</html>
