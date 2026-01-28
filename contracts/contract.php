<?php
session_start();
require "../config/db.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid contract request.");
}

$stmt = $pdo->prepare("
    SELECT r.*, u.name as user_name, u.email as user_email, b.name as bien_name, b.type as bien_type, b.price_per_day
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN biens b ON r.bien_id = b.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$contract_data = $stmt->fetch();

if (!$contract_data) {
    die("Contract not found.");
}

// Security check: ensure the logged-in user is either the client or an admin
if ($_SESSION['user']['id'] !== $contract_data['user_id'] && $_SESSION['user']['role'] !== 'admin') {
    die("Access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Contract #<?= $contract_data['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
        .contract-container {
            max-width: 800px;
            margin: auto;
            padding: 2rem;
            border: 1px solid #dee2e6;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .contract-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .signature-section {
            margin-top: 4rem;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 3rem;
        }
    </style>
</head>
<body>

<div class="contract-container" id="contract">
    <div class="contract-header">
        <h1>Rental Agreement</h1>
        <p class="lead">Contract ID: #<?= $contract_data['id'] ?></p>
    </div>

    <h4>Parties</h4>
    <p>
        <strong>Landlord/Agency:</strong> RentNow<br>
        <strong>Client:</strong> <?= htmlspecialchars($contract_data['user_name']) ?> (<?= htmlspecialchars($contract_data['user_email']) ?>)
    </p>

    <hr>

    <h4>Rental Property</h4>
    <p>
        <strong>Property:</strong> <?= htmlspecialchars($contract_data['bien_name']) ?><br>
        <strong>Type:</strong> <?= ucfirst(htmlspecialchars($contract_data['bien_type'])) ?>
    </p>

    <hr>

    <h4>Terms</h4>
    <p>
        <strong>Start Date:</strong> <?= date('F j, Y', strtotime($contract_data['start_date'])) ?><br>
        <strong>End Date:</strong> <?= date('F j, Y', strtotime($contract_data['end_date'])) ?><br>
        <strong>Rental Rate:</strong> $<?= htmlspecialchars($contract_data['price_per_day']) ?> per day<br>
        <strong>Total Amount:</strong> $<?= htmlspecialchars($contract_data['total_price']) ?>
    </p>

    <hr>

    <h4>Agreement</h4>
    <p>
        The client agrees to rent the property for the specified period and price. The property must be returned in the same condition it was received. Any damages may result in additional charges. This agreement is binding upon both parties.
    </p>

    <div class="signature-section row">
        <div class="col-6">
            <div class="signature-line"></div>
            <p>Client Signature: <?= htmlspecialchars($contract_data['user_name']) ?></p>
        </div>
        <div class="col-6 text-end">
            <div class="signature-line"></div>
            <p>Agency Signature: RentNow</p>
        </div>
    </div>
</div>

<div class="text-center mb-4 no-print">
    <button class="btn btn-primary" onclick="window.print()">Print Contract</button>
    <a href="/reservations/my_reservations.php" class="btn btn-secondary">Back to Reservations</a>
</div>

</body>
</html>
