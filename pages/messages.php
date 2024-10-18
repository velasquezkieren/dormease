<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Fetching conversations for the logged-in user
$u_ID = $_SESSION['u_ID']; // Assuming u_ID is stored in the session

// Initialize search variables
$search_query = '';
$conversations = [];

// Check if a search term is provided
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($con, $_GET['search']);
}

// Select conversations based on the search query
$conversationsQuery = "
    SELECT u.u_ID, 
           CONCAT(u.u_FName, ' ', COALESCE(u.u_MName, ''), ' ', u.u_LName) AS full_name,
           MAX(m.m_Message) AS last_message,
           MAX(m.m_Sender) AS last_sender, 
           MAX(m.m_DateTime) AS last_time,
           u.u_Picture AS userPicture
    FROM user u
    LEFT JOIN messaging m ON (u.u_ID = m.m_Sender OR u.u_ID = m.m_Recipient)
    WHERE (m.m_Sender = '$u_ID' OR m.m_Recipient = '$u_ID')
    AND (u.u_ID <> '$u_ID')
    " . ($search_query ? "AND (u.u_FName LIKE '%$search_query%' OR u.u_LName LIKE '%$search_query%')" : "") . "
    GROUP BY u.u_ID
    ORDER BY last_time DESC";

$conversationsResult = $con->query($conversationsQuery);

while ($row = $conversationsResult->fetch_assoc()) {
    // Check if the last message was sent by the logged-in user
    if ($row['last_sender'] == $u_ID) {
        $row['last_message'] = "You: " . htmlspecialchars($row['last_message']);
    } else {
        $row['last_message'] = htmlspecialchars($row['full_name']) . ": " . htmlspecialchars($row['last_message']);
    }
    $conversations[] = $row;
}

// Check if a conversation is selected
$currentConversation = null;
if (isset($_GET['u_ID'])) { // Change from m_ID to u_ID
    $recipient_ID = $_GET['u_ID']; // Get the u_ID from the URL (now representing the recipient)
    // Fetch messages for the selected conversation
    $messagesQuery = "SELECT m.m_Message, m.m_DateTime, u.u_FName AS senderFName, u.u_MName AS senderMName, u.u_LName AS senderLName
                      FROM messaging m
                      JOIN user u ON m.m_Sender = u.u_ID
                      WHERE (m.m_Sender = '$u_ID' AND m.m_Recipient = '$recipient_ID') 
                      OR (m.m_Sender = '$recipient_ID' AND m.m_Recipient = '$u_ID')
                      ORDER BY m.m_DateTime ASC"; // Adjust the query for messages
    $messagesResult = $con->query($messagesQuery);
    $messages = [];

    while ($messageRow = $messagesResult->fetch_assoc()) {
        $messages[] = $messageRow;
    }

    // Set the current conversation details
    $currentConversation = array_filter($conversations, fn($conv) => $conv['u_ID'] == $recipient_ID);
    $currentConversation = reset($currentConversation); // Get the first element
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = $_POST['recipient']; // Get recipient ID from the form (you should pass this in the form)
    $message = $_POST['message'];
    $m_ID = uniqid('m_');
    $insertQuery = "INSERT INTO messaging (m_ID, m_Recipient, m_Sender, m_Message, m_DateTime) VALUES ('$m_ID', '$recipient', '$u_ID', '$message', NOW())";
    $con->query($insertQuery);
    header("Location: messages?u_ID=$recipient"); // Redirect to the conversation with the recipient
    exit();
}
?>

<div class="container-fluid pt-5 vh-100">
    <div class="row pt-5 h-100">
        <!-- Left Column: List of Conversations with Search -->
        <div class="col-12 col-md-4 border-end">
            <div class="left-side h-100 d-flex flex-column">
                <div class="container mb-3">
                    <h3>Conversations</h3>
                    <!-- Search bar -->
                    <div class="search-bar mb-3">
                        <form method="GET" action="">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search_query); ?>">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users list -->
                <ul class="list-group flex-grow-1 overflow-auto">
                    <?php if (count($conversations) > 0): ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="?u_ID=<?php echo $conversation['u_ID']; ?>" class="text-decoration-none">
                                <li class="list-group-item d-flex align-items-start border-0 py-3">
                                    <img src="user_avatar/<?php echo $conversation['userPicture']; ?>" alt="Profile" width="50" height="50" class="rounded-circle me-3">
                                    <div class="flex-grow-1">
                                        <strong><?php echo htmlspecialchars($conversation['full_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo $conversation['last_message']; ?></small>
                                    </div>
                                </li>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item border-0">
                            <p class="text-center text-muted h5">No Messages found.</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Middle Column: Conversation -->
        <div class="col-12 col-md-4 d-flex flex-column">
            <?php if ($currentConversation): ?>
                <h4 class="mt-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="profile?u_ID=<?php echo ($currentConversation['u_ID']); ?>" class="text-dark text-decoration-none"><img src="user_avatar/<?php echo $currentConversation['userPicture']; ?>" alt="Profile" width="50" height="50" class="rounded-circle me-2">
                            <span><?php echo htmlspecialchars($currentConversation['full_name']); ?></span></a>
                    </div>
                </h4>
                <div class="border p-3 flex-grow-1 overflow-auto">
                    <?php foreach ($messages as $message): ?>
                        <div class="mb-2">
                            <strong><?php echo ($message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName'] == 'You') ? 'You' : htmlspecialchars($message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName']); ?>:</strong>
                            <p class="d-inline"><?php echo htmlspecialchars($message['m_Message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form action="" method="POST" class="mt-3 mb-3">
                    <input type="hidden" name="recipient" value="<?php echo ($currentConversation['u_ID']); ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" name="message" placeholder="Aa" required>
                        <button class="btn btn-secondary" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z" />
                            </svg>
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="d-flex justify-content-center align-items-center h-100">
                    <h4 class="mt-4 text-muted">Select a chat or start a new conversation</h4>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Person Details -->
        <div class="col-12 col-md-4 border-start" id="detailsDiv">
            <?php if ($currentConversation): ?>
                <div class="p-3 text-center">
                    <img src="user_avatar/<?php echo $currentConversation['userPicture']; ?>" alt="Profile" width="150" height="150" class="rounded-circle img-fluid mb-3"><br>
                    <h5>
                        <a href="profile?u_ID=<?php echo ($currentConversation['u_ID']); ?>" class="text-dark text-decoration-none">
                            <?php echo htmlspecialchars($currentConversation['full_name']); ?>
                        </a>
                    </h5>
                </div>
            <?php else: ?>
                <p class="text-center mt-5">Select a conversation to see details.</p>
            <?php endif; ?>
        </div>
    </div>
</div>