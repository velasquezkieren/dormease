<?php

if (!isset($_SESSION['u_Email'])) {
    exit(); // Exit if not logged in
}

$u_ID = $_SESSION['u_ID'];

// Retrieve the search query
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$conversations = [];

$conversationsQuery = "
    SELECT u.u_ID, 
           CONCAT(u.u_FName, ' ', COALESCE(u.u_MName, ''), ' ', u.u_LName) AS full_name, 
           u.u_Account_Type,
           m.m_Message AS last_message,
           m.m_Sender AS last_sender, 
           m.m_DateTime AS last_time,
           u.u_Picture AS userPicture
    FROM user u
    LEFT JOIN (
        SELECT m1.*
        FROM messaging m1
        JOIN (
            SELECT 
                GREATEST(m_Sender, m_Recipient) AS convo_id,
                MAX(m_DateTime) AS last_time
            FROM messaging
            WHERE m_Sender = '$u_ID' OR m_Recipient = '$u_ID'
            GROUP BY convo_id
        ) AS latest ON (
            (m1.m_Sender = latest.convo_id OR m1.m_Recipient = latest.convo_id) 
            AND m1.m_DateTime = latest.last_time
        )
    ) m ON (u.u_ID = m.m_Sender OR u.u_ID = m.m_Recipient)
    WHERE (u.u_ID <> '$u_ID')
    ORDER BY 
        CASE 
            WHEN m.m_ID IS NOT NULL THEN 0
            ELSE 1
        END, 
        last_time DESC,
        full_name ASC
";

$conversationsResult = $con->query($conversationsQuery);
$conversations = []; // Initialize the conversations array

while ($row = $conversationsResult->fetch_assoc()) {
    if ($row['last_sender'] == $u_ID) {
        $row['last_message'] = "You: " . htmlspecialchars($row['last_message']);
    } else {
        $row['last_message'] = htmlspecialchars($row['full_name']) . ": " . htmlspecialchars($row['last_message']);
    }

    $row['last_time'] = date('Y-m-d H:i:s', strtotime($row['last_time']));
    $conversations[] = $row; // Append to conversations array
}

// Generate HTML for conversations
foreach ($conversations as $conversation) {
    echo '<a href="?u_ID=' . $conversation['u_ID'] . '" class="text-decoration-none">
            <li class="list-group-item d-flex align-items-start border-0 py-3">
                <img src="user_avatar/' . $conversation['userPicture'] . '" alt="Profile" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                    <strong>' . htmlspecialchars($conversation['full_name']) . '</strong><br>
                    <small class="text-muted">' . $conversation['last_message'] . '</small><br>
                </div>
            </li>
          </a>';
}
