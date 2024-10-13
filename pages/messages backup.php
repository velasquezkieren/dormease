<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Sample data for demonstration (you will replace this with dynamic data from your database)
$conversations = [
    ['id' => 1, 'name' => 'Alice', 'lastMessage' => 'Hey, how are you?'],
    ['id' => 2, 'name' => 'Bob', 'lastMessage' => 'Let’s meet tomorrow.'],
    ['id' => 3, 'name' => 'Charlie', 'lastMessage' => 'Did you get my email?'],
];

$currentConversation = ['name' => 'Alice', 'messages' => [
    ['sender' => 'Alice', 'text' => 'Hey, how are you?'],
    ['sender' => 'You', 'text' => 'I am good, thanks! How about you?']
]];
?>

<div class="container-fluid pt-5 mt-5">
    <div class="row pt-5">
        <!-- Left Column: List of Conversations -->
        <div class="col-12 col-md-4 border-end">
            <h4 class="mt-4">Conversations</h4>
            <ul class="list-group">
                <?php foreach ($conversations as $conversation) : ?>
                    <li class="list-group-item">
                        <a href="?conversation_id=<?php echo $conversation['id']; ?>">
                            <?php echo $conversation['name']; ?>
                            <small class="text-muted"><?php echo $conversation['lastMessage']; ?></small>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Middle Column: Conversation -->
        <div class="col-12 col-md-4">
            <h4 class="mt-4">Chat with <?php echo $currentConversation['name']; ?>
                <button class="btn btn-secondary mt-3" id="toggleDetailsBtn">Toggle Details</button>
            </h4>
            <div class="border p-3" style="height: 400px; overflow-y: scroll;">
                <?php foreach ($currentConversation['messages'] as $message) : ?>
                    <div class="mb-2">
                        <strong><?php echo $message['sender'] == 'You' ? 'You' : $message['sender']; ?>:</strong>
                        <p class="d-inline"><?php echo $message['text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <form action="" method="POST" class="mt-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="message" placeholder="Type a message..." required>
                    <button class="btn btn-primary" type="submit">Send</button>
                </div>
            </form>
        </div>

        <!-- Right Column: Person I'm Talking With -->
        <div class="col-12 col-md-4 border-start" id="detailsDiv">
            <h4 class="mt-4">Details</h4>
            <div class="p-3">
                <h5><?php echo $currentConversation['name']; ?></h5>
                <p>Last seen: Just now</p>
                <p>Status: Online</p>
                <!-- Add more details about the person as needed -->
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#toggleDetailsBtn').click(function() {
            $('#detailsDiv').toggleClass('d-none'); // Use d-none to show/hide the details div
        });
    });
</script>



































<!-- Backup 2 -->
<?php
// Redirect to Login if not logged in
if (!isset($_SESSION['u_Email'])) {
    header("location:login?auth-required");
    die();
}

// Fetching conversations for the logged-in user
$u_ID = $_SESSION['u_ID']; // Assuming u_ID is stored in the session
$conversationsQuery = "SELECT m.m_ID, m.m_Sender, m.m_Recipient, m.m_Message, m.m_DateTime, 
                               u.u_FName AS senderFName, u.u_MName AS senderMName, u.u_LName AS senderLName, 
                               u2.u_FName AS recipientFName, u2.u_MName AS recipientMName, u2.u_LName AS recipientLName,
                               u.u_Picture AS senderPicture, u2.u_Picture AS recipientPicture
                       FROM messaging m
                       JOIN user u ON m.m_Sender = u.u_ID
                       JOIN user u2 ON m.m_Recipient = u2.u_ID
                       WHERE m.m_Sender = '$u_ID' OR m.m_Recipient = '$u_ID'
                       GROUP BY m.m_ID ORDER BY m.m_DateTime DESC";
$conversationsResult = $con->query($conversationsQuery);
$conversations = [];

while ($row = $conversationsResult->fetch_assoc()) {
    $conversations[] = $row;
}

// Check if a conversation is selected
$currentConversation = null;
if (isset($_GET['m_ID'])) {
    $m_ID = $_GET['m_ID'];
    // Fetch messages for the selected conversation
    $messagesQuery = "SELECT m.m_Message, m.m_DateTime, u.u_FName AS senderFName, u.u_MName AS senderMName, u.u_LName AS senderLName
                      FROM messaging m
                      JOIN user u ON m.m_Sender = u.u_ID
                      WHERE m.m_ID = '$m_ID' ORDER BY m.m_DateTime ASC";
    $messagesResult = $con->query($messagesQuery);
    $messages = [];

    while ($messageRow = $messagesResult->fetch_assoc()) {
        $messages[] = $messageRow;
    }

    // Set the current conversation details (assuming you want to show sender or recipient based on who you are)
    $currentConversation = array_filter($conversations, fn($conv) => $conv['m_ID'] == $m_ID);
    $currentConversation = reset($currentConversation); // Get the first element
}

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = $_POST['recipient']; // Get recipient ID from the form (you should pass this in the form)
    $message = $_POST['message'];
    $insertQuery = "INSERT INTO messaging (m_ID, m_Recipient, m_Sender, m_Message, m_DateTime) VALUES (UUID(), '$recipient', '$u_ID', '$message', NOW())";
    $con->query($insertQuery);
    header("Location: messages?m_ID=" . $currentConversation['m_ID']);
    exit();
}
?>

<div class="container-fluid pt-5 mt-5">
    <div class="row pt-5">
        <!-- Left Column: List of Conversations -->
        <div class="col-12 col-md-4 border-end">
            <h4 class="mt-4">Conversations</h4>
            <ul class="list-group">
                <?php foreach ($conversations as $conversation) : ?>
                    <li class="list-group-item">
                        <a href="?m_ID=<?php echo $conversation['m_ID']; ?>" class="text-decoration-none">
                            <img src="user_avatar/<?php echo $conversation['recipientPicture']; ?>" alt="Profile" width="50" height="50" class="rounded-circle">
                            <?php
                            echo ($conversation['m_Sender'] == $u_ID)
                                ? $conversation['recipientFName'] . ' ' . $conversation['recipientMName'] . ' ' . $conversation['recipientLName']
                                : $conversation['senderFName'] . ' ' . $conversation['senderMName'] . ' ' . $conversation['senderLName'];
                            ?>
                            <small class="text-muted"><?php echo $conversation['m_Message']; ?></small>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Middle Column: Conversation -->
        <div class="col-12 col-md-4">
            <?php if ($currentConversation) : ?>
                <h4 class="mt-4"><?php
                                    echo ($currentConversation['m_Sender'] == $u_ID)
                                        ? $currentConversation['recipientFName'] . ' ' . $currentConversation['recipientMName'] . ' ' . $currentConversation['recipientLName']
                                        : $currentConversation['senderFName'] . ' ' . $currentConversation['senderMName'] . ' ' . $currentConversation['senderLName']; ?>
                    <button class="btn float-end" id="toggleDetailsBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                        </svg>
                    </button>
                </h4>
                <div class="border p-3" style="height: 400px; overflow-y: scroll;">
                    <?php foreach ($messages as $message) : ?>
                        <div class="mb-2">
                            <strong><?php echo $message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName'] == 'You' ? 'You' : $message['senderFName'] . ' ' . $message['senderMName'] . ' ' . $message['senderLName']; ?>:</strong>
                            <p class="d-inline"><?php echo $message['m_Message']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form action="" method="POST" class="mt-3">
                    <input type="hidden" name="recipient" value="<?php echo ($currentConversation['m_Sender'] == $u_ID) ? $currentConversation['m_Recipient'] : $currentConversation['m_Sender']; ?>">
                    <div class="input-group">
                        <input type="text" class="form-control" name="message" placeholder="Type a message..." required>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </div>
                </form>
            <?php else : ?>
                <h4 class="mt-4">Select a conversation to start chatting</h4>
            <?php endif; ?>
        </div>

        <!-- Right Column: Person I'm Talking With -->
        <div class="col-12 col-md-4 border-start" id="detailsDiv">
            <?php if ($currentConversation) : ?>
                <div class="p-3 text-center">
                    <img src="user_avatar/<?php echo $conversation['recipientPicture']; ?>" alt="Profile" width="200" height="200" class="rounded-circle img-fluid mb-3"><br>
                    <h5>
                        <a href="profile?u_ID=<?php echo ($currentConversation['m_Sender'] == $u_ID) ? $currentConversation['m_Recipient'] : $currentConversation['m_Sender']; ?>" class="text-decoration-none">
                            <?php
                            echo ($currentConversation['m_Sender'] == $u_ID)
                                ? $currentConversation['recipientFName'] . ' ' . $currentConversation['recipientMName'] . ' ' . $currentConversation['recipientLName']
                                : $currentConversation['senderFName'] . ' ' . $currentConversation['senderMName'] . ' ' . $currentConversation['senderLName']; ?>
                        </a>
                    </h5>
                </div>
            <?php else : ?>
                <p class="text-center">Select a conversation to see details</p>
            <?php endif; ?>
        </div>


    </div>
</div>

<script>
    $(document).ready(function() {
        $('#toggleDetailsBtn').click(function() {
            $('#detailsDiv').toggleClass('d-none'); // Use d-none to show/hide the details div
        });
    });
</script>