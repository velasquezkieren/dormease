<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Fetching conversations for the logged-in user
$u_ID = $_SESSION['u_ID']; // Assuming u_ID is stored in the session

// Initialize search variables
$search_query = isset($_GET['search']) ? trim($_GET['search']) : ''; // Get search query from URL
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
    // Check if the last message was sent by the logged-in user
    if ($row['last_sender'] == $u_ID) {
        $row['last_message'] = "You: " . htmlspecialchars($row['last_message']);
    } else {
        $row['last_message'] = htmlspecialchars($row['full_name']) . ": " . htmlspecialchars($row['last_message']);
    }

    // Format the last time as needed
    $row['last_time'] = date('Y-m-d H:i:s', strtotime($row['last_time']));
    $conversations[] = $row; // Append to conversations array
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = $_POST['recipient']; // Get recipient ID from the form
    $message = $_POST['message'];

    // Check if the message is not empty before proceeding
    if (!empty($message)) {
        $m_ID = uniqid('m_');
        $insertQuery = "INSERT INTO messaging (m_ID, m_Recipient, m_Sender, m_Message, m_DateTime) 
                        VALUES ('$m_ID', '$recipient', '$u_ID', '$message', NOW())";
        $con->query($insertQuery);
        exit();
    }
}
?>

<div class="container-fluid pt-md-4 vh-100">
    <div class="row pt-5 h-100">
        <!-- Left Column: List of Conversations with Search -->
        <div class="col-12 col-md-4 border-end">
            <div class="left-side h-100 d-flex flex-column">
                <div class="container mb-3">
                    <h3 class="pt-md-3">Conversations</h3>
                    <form method="GET" action="" id="searchForm" class="pt-1">
                        <input type="text" name="search" class="form-control" id="searchInput" placeholder="Search users..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </form>
                </div>

                <!-- Users list -->
                <ul class="list-group flex-grow-1 overflow-auto" id="conversationsList" style="max-height: calc(100vh - 200px);">
                    <?php if (count($conversations) > 0): ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="?u_ID=<?php echo $conversation['u_ID']; ?>" class="text-decoration-none">
                                <li class="list-group-item d-flex align-items-start border-0 py-3">
                                    <img src="user_avatar/<?php echo $conversation['userPicture']; ?>" alt="Profile" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <strong><?php echo htmlspecialchars($conversation['full_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo $conversation['last_message']; ?></small><br>
                                    </div>
                                </li>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item border-0">
                            <p class="text-center text-muted h5">No Messages found.</p>
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <p class="text-center text-muted h6">New messages will appear here</p>
                            </div>
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
                        <a href="profile?u_ID=<?php echo ($currentConversation['u_ID']); ?>" class="text-dark text-decoration-none">
                            <img src="user_avatar/<?php echo $currentConversation['userPicture']; ?>" alt="Profile" class="rounded-circle img-fluid me-2" style="width: 50px; height: 50px; object-fit: cover;">
                            <span><?php echo htmlspecialchars($currentConversation['full_name']); ?></span>
                        </a>
                    </div>
                </h4>
                <div class="border p-3 flex-grow-1 overflow-auto" style="max-height: calc(100vh - 200px);" id="chatArea">
                    <?php foreach ($messages as $message): ?>
                        <div class="mb-2">
                            <strong><?php echo ($message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName'] == 'You') ? 'You' : htmlspecialchars($message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName']); ?>:</strong>
                            <p class="d-inline"><?php echo htmlspecialchars($message['m_Message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form action="" method="POST" class="mt-3 mb-3" id="messageForm">
                    <input type="hidden" name="recipient" value="<?php echo ($currentConversation['u_ID']); ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" name="message" id="messageInput" placeholder="Aa" required>
                        <button class="btn btn-secondary" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z" />
                            </svg>
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="d-flex justify-content-center align-items-center h-100">
                    <h4 class="mt-4 text-muted text-center">Select a conversation</h4>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Person Details -->
        <div class="col-12 col-md-4 border-start" id="detailsDiv">
            <?php if ($currentConversation): ?>
                <div class="p-3 text-center">
                    <img src="user_avatar/<?php echo $currentConversation['userPicture']; ?>" alt="Profile" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;"><br>
                    <h5>
                        <a href="profile?u_ID=<?php echo ($currentConversation['u_ID']); ?>" class="text-dark text-decoration-none">
                            <?php echo htmlspecialchars($currentConversation['full_name']); ?>
                        </a>
                    </h5>
                    <p class="badge text-bg-secondary">
                        <?php
                        if ($currentConversation['u_Account_Type'] == 0) {
                            echo 'Owner';
                        } else {
                            echo 'Tenant';
                        }
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <p class="text-center mt-5 text-muted">Select a conversation to see details.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const u_ID = "<?php echo $u_ID; ?>"; // Store the logged-in user ID
    const refreshRate = 3000; // Set refresh rate in milliseconds (3 seconds)
    let isSearching = false; // Flag to indicate if a search is active
    let searchTimeout; // Variable to hold the timeout for debounce

    // Function to fetch conversations
    function fetchConversations() {
        if (!isSearching) { // Only fetch conversations if not searching
            $.ajax({
                url: 'fetch_conversations', // URL to the PHP script that fetches conversations
                method: 'GET',
                success: function(data) {
                    $('#conversationsList').html(data); // Update the conversations list
                }
            });
        }
    }

    // Function to fetch messages
    function fetchMessages() {
        const recipient_ID = "<?php echo isset($recipient_ID) ? $recipient_ID : ''; ?>"; // Get the current recipient ID
        if (recipient_ID) {
            $.ajax({
                url: 'fetch_messages', // URL to the PHP script that fetches messages
                method: 'GET',
                data: {
                    u_ID: recipient_ID
                }, // Send the recipient ID as a parameter
                success: function(data) {
                    $('.border.p-3.flex-grow-1.overflow-auto').html(data); // Update the message area
                    scrollToBottom(); // Scroll to bottom after updating messages
                }
            });
        }
    }

    // Set intervals for polling
    setInterval(fetchConversations, refreshRate); // Refresh conversations
    setInterval(fetchMessages, refreshRate); // Refresh messages

    // Function to scroll to the bottom of the chat area
    function scrollToBottom() {
        var chatArea = $('.border.p-3.flex-grow-1.overflow-auto'); // Adjusted selector
        chatArea.scrollTop(chatArea[0].scrollHeight);
    }

    $(document).ready(function() {
        // Handle message sending
        $('#messageForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const formData = $(this).serialize(); // Serialize the form data
            $.ajax({
                url: '', // The current URL to handle the form submission
                method: 'POST',
                data: formData,
                success: function() {
                    $('#messageInput').val(''); // Clear the input field
                    fetchMessages(); // Fetch updated messages after sending
                }
            });
        });

        // Search input handling with debounce
        $('#searchInput').on('input', function() {
            const searchQuery = $(this).val();
            isSearching = searchQuery.length > 0; // Set flag based on input length

            clearTimeout(searchTimeout); // Clear the previous timeout
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: 'fetch_conversations', // Update to your actual fetch URL
                    method: 'GET',
                    data: {
                        search: searchQuery // Pass the search query
                    },
                    success: function(data) {
                        $('#conversationsList').html(data); // Update the conversations list
                    }
                });
            }, 300); // Wait for 300ms before sending the request
        });
    });
</script>