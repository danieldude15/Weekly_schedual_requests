<?php 
session_start();
$taz=$_SESSION["taz"];
$mail=$_POST["email"];
$pass=$_POST["newpass"];
 include 'header.php';
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$check=login($taz,$_SESSION["pass"],$conn);
if ($check>=1)
{echo 'אימות משתמש בוצע בהצלחה <a href="'.$home_url.'">התחבר מחדש</a><br>';
if(!strcmp($_POST["action"],"password")){
$sql = "UPDATE `emp` SET 
`pass` = '$pass' WHERE `emp`.`inerid` = '$taz'";

if (mysqli_query($conn, $sql)) {
    echo "סיסמא עודכנה בהצלחה!!!";
} else {
    echo "עדכון סיסמא לא עבד כי: " . mysqli_error($conn);
}
}
else if(!strcmp($_POST["action"],"email")){
$sql = "UPDATE `emp` SET 
`mail` = '$mail' WHERE `emp`.`inerid` = '$taz'";

if (mysqli_query($conn, $sql)) {
    echo "כתובת מייל עודכנה בהצלחה!!!<br>";
} else {
    echo "עדכון כתובת מייל לא עבד כי: " . mysqli_error($conn);
}
}
}
else if ($check==0)
{echo 'אין לך גישה לעמוד זה חזור <a href="'.$home_url.'">לדף הבית</a>';exit;}

?>
</body>
</html>