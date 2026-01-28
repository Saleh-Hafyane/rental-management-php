<?php
// Start session and connect to database
session_start();
require "../config/db.php";

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Get property ID from URL and fetch details
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM biens WHERE id=?");
$stmt->execute([$id]);
$bien = $stmt->fetch();

$error = "";

// AJAX Availability Check Endpoint
if (isset($_GET['action']) && $_GET['action'] === 'check_availability' && $bien) {
    header('Content-Type: application/json');
    $start_date = $_GET['start'] ?? '';
    $end_date = $_GET['end'] ?? '';

    if (empty($start_date) || empty($end_date) || $end_date <= $start_date) {
        echo json_encode(['available' => false, 'message' => 'Invalid date range.']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations
        WHERE bien_id = ?
        AND (
            (start_date <= ? AND end_date > ?) OR
            (start_date < ? AND end_date >= ?)
        )
    ");
    $stmt->execute([$id, $start_date, $start_date, $end_date, $end_date]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['available' => false, 'message' => 'This property is not available for the selected dates.']);
    } else {
        $days = (strtotime($end_date) - strtotime($start_date)) / 86400;
        $total_price = $days * $bien['price_per_day'];
        echo json_encode(['available' => true, 'total_price' => number_format($total_price, 2), 'days' => $days]);
    }
    exit;
}

// AJAX Get Booked Dates Endpoint
if (isset($_GET['action']) && $_GET['action'] === 'get_booked_dates' && $bien) {
    header('Content-Type: application/json');
    $stmt = $pdo->prepare("SELECT start_date, end_date FROM reservations WHERE bien_id = ?");
    $stmt->execute([$id]);
    $booked_ranges = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $disabled_dates = [];
    foreach ($booked_ranges as $range) {
        $disabled_dates[] = [
            'from' => $range['start_date'],
            'to' => date('Y-m-d', strtotime($range['end_date'] . ' -1 day')) // Flatpickr disables up to and including 'to' date, so subtract one day
        ];
    }
    echo json_encode($disabled_dates);
    exit;
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bien) {
    $start = $_POST['start_date'];
    $end   = $_POST['end_date'];

    // Validate date range
    if ($end <= $start) {
        $error = "Invalid dates";
    } else {
        // Availability check (re-check on server side for security)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM reservations
            WHERE bien_id = ?
            AND (
                (start_date < ? AND end_date > ?) OR
                (start_date < ? AND end_date > ?)
            )
        ");
        $stmt->execute([$id, $end, $start, $end, $start]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error = "This property is not available for these dates. Please select different dates.";
        } else {
            // Calculate duration and total price
            $days = (strtotime($end) - strtotime($start)) / 86400;
            $total = $days * $bien['price_per_day'];

            // Save reservation to database
            $stmt = $pdo->prepare("
              INSERT INTO reservations(user_id,bien_id,start_date,end_date,total_price)
              VALUES (?,?,?,?,?)
            ");
            $stmt->execute([
                $_SESSION['user']['id'],
                $id,
                $start,
                $end,
                $total
            ]);

            // Redirect to reservations list
            header("Location: " . BASE_URL . "reservations/my_reservations.php");
            exit;
        }
    }
}

include "../partials/header.php";
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<div class="container">
    <?php if (!$bien): ?>
        <div class="alert alert-danger" role="alert">
            Property not found.
        </div>
        <a href="<?= BASE_URL ?>biens/index.php" class="btn btn-secondary">Back to Properties</a>
    <?php else: ?>
        <h2>Reserve: <?= htmlspecialchars($bien['name']) ?></h2>
        <p class="lead">Price per day: $<span id="price_per_day"><?= htmlspecialchars($bien['price_per_day']) ?></span></p>

        <?php if($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="card mt-3">
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start date</label>
                        <input type="text" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End date</label>
                        <input type="text" class="form-control" id="end_date" name="end_date" required>
                    </div>

                    <div id="availabilityStatus" class="mb-3">
                        <!-- Availability status will be displayed here -->
                    </div>

                    <div class="mb-3">
                        <strong>Total Price:</strong> $<span id="totalPriceDisplay">0.00</span>
                    </div>

                    <button type="submit" id="confirmReservationBtn" class="btn btn-success" disabled>Confirm Reservation</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const totalPriceDisplay = document.getElementById('totalPriceDisplay');
        const availabilityStatus = document.getElementById('availabilityStatus');
        const confirmReservationBtn = document.getElementById('confirmReservationBtn');
        const pricePerDay = parseFloat(document.getElementById('price_per_day').textContent);
        const bienId = <?= json_encode($id) ?>;

        let bookedDates = []; // To store disabled date ranges
        let fpStartDate, fpEndDate; // Declare Flatpickr instances globally within this scope

        async function fetchBookedDates(bienId) {
            try {
                const response = await fetch(`<?= BASE_URL ?>reservations/reserve.php?action=get_booked_dates&id=${bienId}`);
                const data = await response.json();
                bookedDates = data;
                initializeFlatpickr(); // Initialize after fetching booked dates
            } catch (error) {
                console.error('Error fetching booked dates:', error);
                // Even if error, try to initialize flatpickr without disabled dates
                initializeFlatpickr();
            }
        }

        function initializeFlatpickr() {
            const fpConfig = {
                dateFormat: "Y-m-d",
                minDate: "today",
                disable: bookedDates, // Use fetched booked dates
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        checkAvailabilityAndCalculatePrice(selectedDates[0], selectedDates[1]);
                    } else if (selectedDates.length === 1) { // When only one date is selected, clear info
                        totalPriceDisplay.textContent = '0.00';
                        availabilityStatus.innerHTML = '';
                        confirmReservationBtn.disabled = true;
                    }
                }
            };

            fpStartDate = flatpickr(startDateInput, { // Assign to global variable
                ...fpConfig,
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates[0]) {
                        fpEndDate.set('minDate', selectedDates[0]);
                        // If end date is before new start date, adjust it
                        if (fpEndDate.selectedDates[0] && fpEndDate.selectedDates[0] < selectedDates[0]) {
                            fpEndDate.setDate(selectedDates[0]);
                        }
                    }
                    if (startDateInput.value && endDateInput.value) {
                        checkAvailabilityAndCalculatePrice(startDateInput.value, endDateInput.value);
                    } else {
                        totalPriceDisplay.textContent = '0.00';
                        availabilityStatus.innerHTML = '';
                        confirmReservationBtn.disabled = true;
                    }
                }
            });

            fpEndDate = flatpickr(endDateInput, { // Assign to global variable
                ...fpConfig,
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates[0]) {
                        fpStartDate.set('maxDate', selectedDates[0]);
                        // If start date is after new end date, adjust it
                        if (fpStartDate.selectedDates[0] && fpStartDate.selectedDates[0] > selectedDates[0]) {
                            fpStartDate.setDate(selectedDates[0]);
                        }
                    }
                    if (startDateInput.value && endDateInput.value) {
                        checkAvailabilityAndCalculatePrice(startDateInput.value, endDateInput.value);
                    } else {
                        totalPriceDisplay.textContent = '0.00';
                        availabilityStatus.innerHTML = '';
                        confirmReservationBtn.disabled = true;
                    }
                }
            });
        }


        function checkAvailabilityAndCalculatePrice(start, end) {
            const startDate = new Date(start);
            const endDate = new Date(end);

            if (startDate >= endDate) {
                availabilityStatus.innerHTML = '<div class="alert alert-warning">End date must be after start date.</div>';
                totalPriceDisplay.textContent = '0.00';
                confirmReservationBtn.disabled = true;
                return;
            }

            const url = `<?= BASE_URL ?>reservations/reserve.php?action=check_availability&id=${bienId}&start=${start}&end=${end}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        availabilityStatus.innerHTML = `<div class="alert alert-success">Available!</div>`;
                        totalPriceDisplay.textContent = data.total_price;
                        confirmReservationBtn.disabled = false;
                    } else {
                        availabilityStatus.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        totalPriceDisplay.textContent = '0.00';
                        confirmReservationBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error checking availability:', error);
                    availabilityStatus.innerHTML = '<div class="alert alert-danger">Error checking availability. Please try again.</div>';
                    totalPriceDisplay.textContent = '0.00';
                    confirmReservationBtn.disabled = true;
                });
        }

        // Fetch booked dates on page load
        fetchBookedDates(bienId);
    });
</script>
<?php include "../partials/footer.php"; ?>
