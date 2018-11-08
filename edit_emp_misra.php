<?php 
 include 'header.php';
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$check=login($_POST["taz"],$_POST["pass"],$conn);
if ($check==2)
{include 'admin.php';
$id_isr=$_POST["inerid"];
$misra=intval($_POST["misra"]);
$sql = "UPDATE `weekly` SET `misra` = '$misra' WHERE `weekly`.`inerid` = '$id_isr'";

if (mysqli_query($conn, $sql)) {
    echo '<h1>אחוז משרה ל  '.$id_isr.' עודכן בהצלחה</h1>';
	
} else {
    echo "<h1>Error updating record: " . mysqli_error($conn)."</h1>";
}
}
else if ($check<=1)
{echo 'אין לך גישה לעמוד זה חזור <a href="'.$home_url.'">לדף הבית</a>';exit;}

?>
</body>
</html>