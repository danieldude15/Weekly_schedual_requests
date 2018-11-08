<?php 
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
if ($check<=1)
{echo 'אין לך גישה לעמוד זה חזור <a href="'.$home_url.'">לדף הבית</a>';exit;}
else if ($check==2)
{
    if(isset($_POST["admin_action"]) && !strcmp($_POST["admin_action"],"printit")){
        if($_POST["mailall"]=='1' && $_POST["updateall"]=="0"){echo "<font color='red'>שימי לב רק העובדים המופיעים לך פה בטבלאות יקבלו מייל שהדפסת סידור!</font>";}
    $archive = date('Y-m-d', time() - 60 * 60 * 24 * 4);
if($_POST["week"] < $archive) {
    check_availability($_POST, $conn,1);
    exit;
}
else {
    if($_POST["updateall"]=='1' && update_all_empty_sidurs($conn,$_POST["week"])==TRUE)
    {
    if($GLOBALS["debugmod"]){echo 'update all empty true<br>';}
    }
    else 
    {
    if($GLOBALS["debugmod"]){echo 'update all empty false<br>';}
    }

    $sql = "SELECT * FROM archive WHERE date_range_rash='".$_POST['week']."' ORDER BY team, indexG";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
        print_sidur_table($row);
        if($_POST["mailall"]=='1'){
            tell_emp_printed($row,$user,$_POST['week']);
        }
    }
    check_availability($_POST,$conn,0);
    }
    else 
    {
    echo "0 results";
    }
    
}
}  
?>
<div class="admin_menu">
    <ul> תפריט מנהלים <br>
 <li>
     <form action="add-remove-emp.php" method="post">
	<input type="submit" value="ניהול עובדים">
	</form>
 </li>
 <li>
	<form action="<?php echo $GLOBALS["home_url"] ?>kill.php" method="post">
	<input type="submit" value="התנתקי">
	</form>
 </li>
</ul>
</div>
<?php 
    if(isset($_POST["admin_action"]) && !strcmp($_POST["admin_action"],"printit")){
        exit;
    }
} ?>