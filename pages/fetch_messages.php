<?php
if (!isset($_SESSION['u_Email'])) {
    exit(); // Exit if not logged in
}

$u_ID = $_SESSION['u_ID'];

if (isset($_GET['u_ID'])) {
    $recipient_ID = mysqli_real_escape_string($con, $_GET['u_ID']);
    // Fetch messages for the selected conversation
    $messagesQuery = "SELECT m.m_Message, m.m_DateTime, u.u_FName AS senderFName, u.u_MName AS senderMName, u.u_LName AS senderLName
                      FROM messaging m
                      JOIN user u ON m.m_Sender = u.u_ID
                      WHERE (m.m_Sender = '$u_ID' AND m.m_Recipient = '$recipient_ID') 
                      OR (m.m_Sender = '$recipient_ID' AND m.m_Recipient = '$u_ID')
                      ORDER BY m.m_DateTime ASC";

    $messagesResult = $con->query($messagesQuery);
    $messages = [];

    while ($messageRow = $messagesResult->fetch_assoc()) {
        $messages[] = $messageRow;
    }

    // Generate HTML for messages
    foreach ($messages as $message) {
        echo '<div class="mb-2">
                <strong>' . (htmlspecialchars($message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName']) == 'You' ? 'You' : htmlspecialchars($message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName'])) . ':</strong>
                <p class="d-inline">' . htmlspecialchars($message['m_Message']) . '</p>
              </div>';
    }
}
