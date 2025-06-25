<?php
require_once('tcpdf/tcpdf.php');

ob_clean();

// Redirect to login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Only allow tenants (Account Type 1) to access this page
if (isset($_SESSION['u_Account_Type']) && $_SESSION['u_Account_Type'] != 1) {
    header("location: profile?u_ID=" . $_SESSION['u_ID']);
    die();
}

// Validate the transaction ID from the request
if (!isset($_GET['transaction_id'])) {
    die("Invalid request");
}
$transaction_id = $_GET['transaction_id'];

// Fetch transaction details from the database, including the billed user (tenant) and biller (landlord)
$query = "
    SELECT l.*, 
           CONCAT(u.u_FName, ' ', u.u_MName, ' ', u.u_LName) AS billed_user, 
           CONCAT(b.u_FName, ' ', b.u_MName, ' ', b.u_LName) AS biller
    FROM ledger l
    LEFT JOIN user u ON l.l_Recipient = u.u_ID
    LEFT JOIN user b ON l.l_Biller = b.u_ID
    WHERE l.l_ID = ? AND l.l_Recipient = ?
";

$stmt = $con->prepare($query);
$stmt->bind_param("ss", $transaction_id, $_SESSION['u_ID']);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("Transaction not found.");
}

// Extend TCPDF to fix the Header issue
class CustomPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 14);
        $headerText = "DormEase";
        $this->Cell(0, 10, $headerText, 0, 1, 'C', false);
    }
}

// Generate PDF
$pdf = new CustomPDF();
$pdf->SetCreator('DormEase');
$pdf->SetAuthor('DormEase');
$pdf->SetTitle('Statement of Account');
$pdf->SetHeaderData('', '', 'Statement of Account', 'Transaction ID: ' . htmlspecialchars($transaction_id));

// Set font to DejaVu Sans, which supports the ₱ symbol
$pdf->SetFont('dejavusans', '', 12);

// Add a page
$pdf->AddPage();

// Define some CSS for styling
$html = '
<style>
    body {
        font-family: "dejavusans", sans-serif;
        color: #333;
        margin: 0;
        padding: 0;
    }
    h1 {
        font-size: 20px;
        color: #005A8D;
        text-align: center;
        margin-bottom: 20px;
    }
    .receipt-header {
        font-size: 14pt;
        margin-bottom: 20px;
    }
    table {
        width: 100%;
        border: 1px solid #ddd;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    table th, table td {
        padding: 8px;
        text-align: left;
    }
    table th {
        background-color: #f2f2f2;
    }
    .amount {
        font-size: 16pt;
        color: #D80000; /* Red for negative amount */
        font-weight: bold;
    }
</style>

<div class="receipt-header">
    <h1>Statement of Account</h1>
    <p><strong>Tenant:</strong> ' . htmlspecialchars($transaction['billed_user']) . '</p>
    <p><strong>Date:</strong> ' . htmlspecialchars($transaction['l_Date']) . '</p>
</div>

<table>
    <tr>
        <th>Description</th>
        <td>' . htmlspecialchars($transaction['l_Description']) . '</td>
    </tr>
    <tr>
        <th>Type</th>
        <td>' . ($transaction['l_Type'] == 0 ? 'Expense' : 'Income') . '</td>
    </tr>
    <tr>
        <th>Amount</th>
        <td><span class="amount">₱' . number_format(floatval($transaction['l_Amount']), 2) . '</span></td>
    </tr>
    <tr>
        <th><br><br><br>Biller</th>
        <td><br><br><br>' . htmlspecialchars($transaction['biller']) . '</td>
    </tr>
</table>
';

// Write content to PDF
$pdf->writeHTML($html);

// Output the PDF to the browser for download
$pdf->Output('SOA_Transaction_' . htmlspecialchars($transaction_id) . '.pdf', 'D'); // 'D' forces download
