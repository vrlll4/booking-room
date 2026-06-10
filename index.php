<?php
// الاتصال بقاعدة البيانات الجديدة
$conn = new mysqli("localhost", "root", "", "tutor_booking");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$showSuccess = false;

// استقبال بيانات الفورم عند الضغط على زر التأكيد
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_name = $_POST['student_name'];
    $student_phone = $_POST['student_phone'];
    $tutor_id = $_POST['tutor_id'];
    $booking_date = $_POST['booking_date'];
    $session_hours = $_POST['session_hours'];
    
    // جلب سعر الساعة للمعلم المختار لحساب الإجمالي في السيرفر بأمان
    $result = $conn->query("SELECT price_per_hour FROM tutors WHERE tutor_id = $tutor_id");
    $tutor = $result->fetch_assoc();
    $total_price = $session_hours * $tutor['price_per_hour'];

    // إدخال الحجز في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO appointments (student_name, student_phone, tutor_id, booking_date, session_hours, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisid", $student_name, $student_phone, $tutor_id, $booking_date, $session_hours, $total_price);
    
    if ($stmt->execute()) { $showSuccess = true; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tutor Booking System</title>
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
        <h1>Rana's Tutor Platform</h1>
        <div><strong>Status:</strong> System Online</div>
    </div>

    <div class="container">
        <h2>Book an Academic Session</h2>
        
        <form method="POST" action="">
            <label>Student Name:</label>
            <input type="text" name="student_name" placeholder="Enter your full name" required>

            <label>Phone Number:</label>
            <input type="text" name="student_phone" placeholder="e.g. 05xxxxxxx" required>

            <label>Select Tutor & Subject:</label>
            <select name="tutor_id" id="tutor_select" onchange="calculateTotal()" required>
                <option value="1" data-price="150">Dr. Ahmed - Computer Science ($150/hr)</option>
                <option value="2" data-price="200">Prof. Sara - Data Structure ($200/hr)</option>
                <option value="3" data-price="250">Engineer Rana - Web Development ($250/hr)</option>
            </select>

            <label>Session Date:</label>
            <input type="date" name="booking_date" required>

            <label>Duration (Hours):</label>
            <input type="number" name="session_hours" id="session_hours" min="1" max="5" value="1" onchange="calculateTotal()" required>

            <label>Total Cost ($):</label>
            <input type="text" id="total_price" readonly style="background-color: #f3f4f6; font-weight: bold; color: #4f46e5;">

            <button type="submit">Confirm Session Booking</button>
        </form>

        <?php if ($showSuccess): ?>
            <div class="success-box">🎉 Appointment Reserved Successfully!</div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; 2026 Rana's Tutor Platform. All Rights Reserved.
    </footer>

    <script>
    function calculateTotal() {
        var tutorSelect = document.getElementById('tutor_select');
        var pricePerHour = parseFloat(tutorSelect.options[tutorSelect.selectedIndex].getAttribute('data-price'));
        var hours = parseInt(document.getElementById('session_hours').value) || 1;
        document.getElementById('total_price').value = "$" + (pricePerHour * hours);
    }
    window.onload = calculateTotal;
    </script>
</body>
</html>