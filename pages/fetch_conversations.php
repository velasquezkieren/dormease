<?php

if (!isset($_SESSION['u_Email'])) {
    exit(); // Exit if not logged in
}

$u_ID = $_SESSION['u_ID'];

// Fetch the search query (if any)
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$conversations = [];

// Modify the query based on whether a search query exists
if (!empty($search_query)) {
    // If searching, fetch users based on the search query
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
            ) AS latest 
            ON (
                (m1.m_Sender = latest.convo_id OR m1.m_Recipient = latest.convo_id) 
                AND m1.m_DateTime = latest.last_time
            )
        ) m 
        ON (u.u_ID = m.m_Sender OR u.u_ID = m.m_Recipient)
        WHERE (u.u_ID <> '$u_ID')
        AND (CONCAT(u.u_FName, ' ', u.u_LName) LIKE '%$search_query%' 
              OR u.u_FName LIKE '%$search_query%' 
              OR u.u_LName LIKE '%$search_query%')
        ORDER BY 
            CASE 
                WHEN m.m_ID IS NOT NULL THEN 0 
                ELSE 1 
            END, 
            last_time DESC,
            full_name ASC";
} else {
    // If no search query, fetch only users you've exchanged messages with
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
    SELECT m1.m_Message, m1.m_Sender, m1.m_Recipient, m1.m_DateTime
    FROM messaging m1
    INNER JOIN (
        SELECT 
            LEAST(m_Sender, m_Recipient) AS user1,
            GREATEST(m_Sender, m_Recipient) AS user2,
            MAX(m_DateTime) AS last_time
        FROM messaging
        WHERE m_Sender = '$u_ID' OR m_Recipient = '$u_ID'
        GROUP BY user1, user2
    ) AS latest
    ON (LEAST(m1.m_Sender, m1.m_Recipient) = latest.user1
        AND GREATEST(m1.m_Sender, m1.m_Recipient) = latest.user2
        AND m1.m_DateTime = latest.last_time)
) m 
ON (u.u_ID = m.m_Sender OR u.u_ID = m.m_Recipient)
WHERE u.u_ID <> '$u_ID'
AND (m.m_Sender = '$u_ID' OR m.m_Recipient = '$u_ID') -- Only show users with whom you've exchanged messages
ORDER BY 
    CASE 
        WHEN m.m_DateTime IS NOT NULL THEN 0 
        ELSE 1 
    END, 
    m.m_DateTime DESC,
    full_name ASC;
";
}

// Execute the query and fetch the results
$conversationsResult = $con->query($conversationsQuery);
$conversations = [];

while ($row = $conversationsResult->fetch_assoc()) {
    // Extract the first name
    $firstName = strtok($row['full_name'], ' ');

    // Format the last message
    if ($row['last_sender'] == $u_ID) {
        // If the logged-in user is the sender, display the message normally
        $row['last_message'] = "You: " . htmlspecialchars($row['last_message']);
    } else {
        // If the logged-in user is the receiver, make the name and message bold
        $row['last_message'] = '<strong>' . htmlspecialchars($firstName) . ":</strong> <strong>" . htmlspecialchars($row['last_message']) . "</strong>";
    }

    // Format the last message time
    $row['last_time'] = date('Y-m-d H:i:s', strtotime($row['last_time']));

    // Add Bootstrap text truncation for the last message
    // Apply flexbox to align the dot and the message on the same line
    $row['last_message'] = '<div class="d-flex justify-content-between" style="max-width: 500px; overflow: hidden; white-space: nowrap;">'
        . '<span class="text-truncate" style="max-width: 100%;">' . $row['last_message'] . '</span>'
        . '<span class="ms-auto">' // Use ms-auto to push the dot to the right
        . (isset($row['last_sender']) && $row['last_sender'] != $u_ID ?
            '<svg width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="4" cy="4" r="4" fill="blue"></circle></svg>'
            : '')
        . '</span></div>';

    // Append to conversations array
    $conversations[] = $row;
}


// Output the HTML for conversations
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
