<?php 
session_start();
include_once 'header.php';
$conn = connect_sql();
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
update_weekly_sidur($conn,$_POST,$_SESSION["taz"]);
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".archive
WHERE inerid='".$_SESSION["taz"]."' AND date_range_rash='".$_SESSION["week"]."'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result)==1) {
$row = mysqli_fetch_assoc($result);
    echo return_sidur_table($row);
    $subject='סידור עבודה מ'.$row["firstname_rash"].' '.$row["lastname_rash"];
    if(mail($row["mail"],$subject , return_sidur_table($row), $GLOBALS["headers"]))
    {
        if(!strcmp($row["mail"],"")){echo 'מאחר ולא מעודכן מייל במערכת לא תקבל העתק של הסידור ששלחת במייל ';}
        else {echo 'נשלח מייל ל '.$row['mail']
        . '<br>'.'<br>';
        }
    }
    else 
    {
        echo 'מייל לא נשלח בהצלחה<br>';
    }
}
else {
    echo $sql;
}
mysqli_close($conn);

?>


<form action="form.php" method="post">
<input style="padding:18px;border-radius: 16px;display: block;clear: both;margin-top: 10px;"type="submit" value="לשליחת סידור נוסף לחץ כאן">
</form>
<form action="kill.php" method="post">
<input style="padding:18px;border-radius: 16px;display: block;clear: both;margin-top: 10px;"type="submit" value="להתנתק לחץ כאן">
</form>
</body>
</html>