<?php

include 'config/database.php';
session_start();

try {
    $DB_DSN = $DB_DSN.';dbname=matcha';
    $conn = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $reciever = stripslashes(htmlspecialchars($_GET['reciever']));
    $sender = stripslashes(htmlspecialchars($_SESSION['logged_on_user']));
    $message = stripslashes(htmlspecialchars($_GET['message']));
    if ($message == '' || $sender == '' || $reciever == '') {
        die('error');
    }

    $notification = '<option value="http://localhost:8080/matcha/chat_index.php?chat_with='.$sender.'">New message from '.$sender."</option>\n";
    //$notification = 'New message from '.$sender.'!';
    $notify = $conn->prepare('INSERT INTO notifications (username, notify, seen, printed) VALUES (?, ?, 0, 0)');
    $notify->execute([$reciever, $notification]);

    $result = $conn->prepare('INSERT INTO chat (sender, reciever, message) VALUES (?, ?, ?)');
    $result->execute([$sender, $reciever, $message]);
} catch (PDOException $e) {
    echo $e;
}
