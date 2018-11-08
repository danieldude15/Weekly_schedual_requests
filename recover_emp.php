<?php 
 include 'header.php';
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$check=login($_POST["taz"],$_POST["pass"],$conn);
if ($check==2)
{
	include 'admin.php';

	$taz=$_POST["ienrid"];
	$pass=$_POST["newpass"];

	$sql = "UPDATE `weekly` SET `pass` = '$pass' WHERE `weekly`.`inerid` = '$taz'";

if (mysqli_query($conn, $sql)) {
    echo "סיסמא עודכנה בהצלחה!!!";
} else {
    echo "Error updating record: " . mysqli_error($conn);
}
}
else if ($check==0)
{echo 'אין לך גישה לעמוד זה חזור <a href="'.$home_url.'">לדף הבית</a>';exit;}
else if ($check==1){
echo 'אתה מנסה לשנות סיסמה שלא שלך... חצוץ!';
}
?>
</body>
</html>