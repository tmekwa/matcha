<?php

include 'config/database.php';
session_start();
$viewed = $_POST['viewed'];
$user = $_SESSION['logged_on_user'];

try {
    $DB_DSN = $DB_DSN.';dbname=matcha';
    $conn = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = $conn->prepare('SELECT username, views, who_viewed, visited FROM `public`');
    $sql->execute();
    while ($result = $sql->fetch(PDO::FETCH_ASSOC)) {
        if ($result['username'] == $user) {
            $visited = $result['visited'];
            if (!$visited) {
                $visited = '<option value="http://localhost:8080/matcha/view_page_user.php?viewing='.$viewed.'">'.$viewed."</option>\n";
            } else {
                $visited = $visited.'<option value="http://localhost:8080/matcha/view_page_user.php?viewing='.$viewed.'">'.$viewed."</option>\n";
            }
            $sql1 = $conn->prepare('UPDATE `public` SET visited=? WHERE username=?');
            $sql1->execute([$visited, $user]);
        }
        if ($result['username'] == $viewed) {
            $views = $result['views'];
            $views = $views + 1;
            $sql1 = $conn->prepare('UPDATE `public` SET views=? WHERE username=?');
            $sql1->execute([$views, $viewed]);

            $who_viewed = $result['who_viewed'];
            if ($who_viewed == '' || !$who_viewed) {
                $who_viewed = $user;
            } else {
                $who_viewed = $who_viewed."\n".$user;
            }
            $sql1 = $conn->prepare('UPDATE `public` SET who_viewed=? WHERE username=?');
            $sql1->execute([$who_viewed, $viewed]);
        }
    }

    $notification = '<option value="http://localhost:8080/matcha/view_page_user.php?viewing='.$user.'">Your profile has just been viewed by '.$user."</option>\n";
  //  $notification = 'Your profile has just been viewed by '.$user.'!';
    $notify = $conn->prepare('INSERT INTO notifications (username, notify, seen) VALUES (?, ?, 0)');
    $notify->execute([$viewed, $notification]);

    $response = array('status' => true);
    die(json_encode($response));
} catch (PDOException $e) {
    $response = array('status' => false, 'statusMsg' => '<p class="danger">Unfortunately there was an error: '.$e.'</p>');
    die(json_encode($response));
}
