<?php 
 include 'header.php';
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$check=login($_POST["taz"],$_POST["pass"],$conn);
if ($check==2)
{include 'admin.php';
$taz=$_POST["inerid"];

$sql = "DELETE FROM ".$GLOBALS["sidurdatabase"].".weekly
WHERE inerid='$taz'";

if (mysqli_query($conn, $sql)) {
    echo '<h1 style="direction:rtl;">עובד  '.$taz.' הוסר בהצלחה</h1>';
	
} else {
    echo "<h1>Error updating record: " . mysqli_error($conn)."</h1>";
}
}
else if ($check<=1)
{echo 'אין לך גישה לעמוד זה חזור <a href="'.$home_url.'">לדף הבית</a>';exit;}

?>
</body>
</html>