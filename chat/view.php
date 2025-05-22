<?php

include '../includes/config.php';

$current_user = $_SESSION['user_id'];
$with_user    = $_GET['user_id'];

$sql = "SELECT * FROM chats 
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) 
        ORDER BY timestamp ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user, $with_user, $with_user, $current_user);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $align = $row['sender_id'] == $current_user ? "right" : "left";
    echo "<p style='text-align:$align'>{$row['message']} <small>{$row['timestamp']}</small></p>";
}
?>

<form method="POST" action="send.php">
    <input type="hidden" name="receiver_id" value="<?= $with_user ?>">
    <textarea name="message" required></textarea>
    <button type="submit">Kirim</button>
</form>