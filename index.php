<?php
// الاتصال بقاعدة البيانات
$conn = new mysqli("localhost", "root", "", "tutor_booking");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$showSuccess = false;

// استقبال بيانات الفورم المحدثة لسيناريو الجامعة
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tutor_name = $_POST['student_name']; // حافظنا على اسم المتغير بـ DB منعاً للحوسة
    $tutor_phone = $_POST['student_phone']; 
    $room_id = $_POST['tutor_id']; 
    $booking_date = $_POST['booking_date'];
    $session_hours = $_POST['session_hours'];
    
    // جلب تكلفة تشغيل القاعة لكل ساعة
    $result = $conn->query("SELECT price_per_hour FROM tutors WHERE tutor_id = $room_id");
    $room = $result->fetch_assoc();
    $total_price = $session_hours * $room['price_per_hour'];

    // إدخال حجز القاعة الأكاديمية في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO appointments (student_name, student_phone, tutor_id, booking_date, session_hours, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisid", $tutor_name, $tutor_phone, $room_id, $booking_date, $session_hours, $total_price);
    
    if ($stmt->execute()) { $showSuccess = true; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Classroom Booking System</title>
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
        <h1>University Portal: Classroom Allocation</h1>
        <div><strong>Role:</strong> Tutor Panel</div>
    </div>

    <div class="container">
        <h2>Book a University Classroom</h2>
        
        <form method="POST" action="">
            <label>Tutor Name (اسم المحاضر):</label>
            <input type="text" name="student_name" placeholder="Enter your academic name" required>

            <label>Lesson Type / Course Code (نوع الدرس):</label>
            <input type="text" name="student_phone" placeholder="e.g. IT-404 Lecture or Lab Session" required>

            <label>Select Classroom & Type (نوع القاعة):</label>
            <select name="tutor_id" id="room_select" onchange="calculateTotal()" required>
                <option value="1" data-price="50">Room 101 - Main Lecture Hall ($50/hr)</option>
                <option value="2" data-price="80">Room 102 - Advanced Computer Lab ($80/hr)</option>
                <option value="3" data-price="100">Room 107 - Interactive Seminar Room ($100/hr)</option>
            </select>

            <label>Start Date & Time (وقت البدء واليوم):</label>
            <input type="date" name="booking_date" required>

            <label>Duration / Period (المدة بالساعات):</label>
            <input type="number" name="session_hours" id="session_hours" min="1" max="5" value="1" onchange="calculateTotal()" required>

            <label>Estimated Overhead Cost ($):</label>
            <input type="text" id="total_price" readonly style="background-color: #f3f4f6; font-weight: bold; color: #4f46e5;">

            <button type="submit">Confirm Classroom Booking</button>
        </form>

        <?php if ($showSuccess): ?>
            <div class="success-box">🎉 Classroom Reserved Successfully for your Lesson!</div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; 2026 University Classroom Resource Management.
    </footer>

    <script>
    function calculateTotal() {
        var roomSelect = document.getElementById('room_select');
        var pricePerHour = parseFloat(roomSelect.options[roomSelect.selectedIndex].getAttribute('data-price'));
        var hours = parseInt(document.getElementById('session_hours').value) || 1;
        document.getElementById('total_price').value = "$" + (pricePerHour * hours);
    }
    window.onload = calculateTotal;
    </script>
</body>
</html>
