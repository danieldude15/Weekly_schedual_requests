<?php session_start();
include_once  'header.php';
if (array_key_exists('capcha',$_POST) && $_POST['capcha']!='capcha'){
echo "לא עדכנת את שמו הנכון של מנהל האשכול, השאלה באה למנוע מכל מיני רובוטים שמטיילים ברשת לפגוע המערכת היקרה שלנו . מלאו את השדה בשמו הפרטי "."<br><a href='".$GLOBALS["home_url"]."'> לדף התחברות </a>";
exit;
}
$conn=connect_sql();
$stat=  session_status();
if (array_key_exists('taz',$_POST))
{
    if($_POST['taz']=='')
    {
        echo 'איך הגעת לדף הזה? <a href="'.$GLOBALS["home_url"].'">לדף התחברות</a>';
        $loginmsg="נסיון גישה ישיר לדף טופס שליחת הסידור של עובדים";
        if($GLOBALS["sendmail"])
        {
            mail('your_email@domain.com',"נסיון כניסה לדף הטופס ללא משתמש",$loginmsg,$GLOBALS["headers"]);
        }
        exit;
    }
    $_SESSION["taz"]=$_POST["taz"];
    if (array_key_exists('pass',$_POST))
    {
        $_SESSION["pass"]=$_POST["pass"];
    }
}
if(isset($_SESSION["taz"]))
{
    $user=$_SESSION["taz"];
    $pass=$_SESSION["pass"];
}
else 
{
    $user=$pass=FALSE;
}

$check=login($user,$pass,$conn);
if ($check>0)
{
$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".emp WHERE inerid='$user'";
$row = mysqli_fetch_assoc(mysqli_query($conn, $sql));
if($check==2){include_once 'admin.php';}
echo '<div style="clear:both;">שם משתמש: '. $row["inerid"] .' שלום - '. $row["firstname_rash"] .' '. $row["lastname_rash"];
if(!strcmp($row["mail"],""))
{
    echo '<h2>שלום '.$row["firstname_rash"].' נא לעדכן מייל לפני שליחת הסידור.</h2>';
}
}
else if ($check==0)
{
    echo ' משתמש וסיסמא לא תקין! <a href="'.$GLOBALS["home_url"].'">'
    . 'נסה שנית'
    . '</a>';
    if($GLOBALS["sendmail"])
    {
        $loginmsg="התחברות שגויה עם משתמש:".$user."<br>וסיסמה:".$pass;
        mail('your_email@domain.com',"התחברות שגויה",$loginmsg,$GLOBALS["headers"]);
    }
    session_destroy();
    exit;
}
if(isset($_POST["week"]))
{
    $_SESSION["week"]=$_POST["week"];
    $notice=important_shifts($_SESSION["week"],$conn);
?>

<form action="action_page.php" method="post">
<input type="hidden" name="taz" value="<?php echo $_SESSION["taz"]; ?>">
<input type="hidden" name="week" value="<?php echo $_SESSION["week"]; ?>">
<input type="hidden" name="empemail" value="<?php echo $row["mail"]; ?>">
<input type="radio" name="cando"
<?php if (isset($cando) && $cando==="can"){ echo "checked";} ?>
value="can" checked> נא לשבץ במשמרות הנ"ל בלבד (אם מתאפשר כמובן).<br>
<input type="radio" name="cando"
<?php if (isset($cando) && $cando==="cant") echo "checked";?>
value="cant"> נא לא לשבץ במשמרות הנ"ל</br>
סידור לשבוע: <?php
$week=date('d-m-Y', strtotime($_POST["week"]));
$end=date('d-m-Y', strtotime($_POST["week"]. ' + 6 days'));
if(date('D', strtotime($week))!='Sun'){echo 'התאריך הנבחר אינו יום ראשון. יש לבחור בתאריך שבו מתחיל השבוע. תמיד יוצא יום ראשון...<br><a href="'.$GLOBALS["home_url"].'/form.php">בחר מחדש</a>';exit;}
echo $week.' עד '.$end;
?>
<table class="responstable" cellpadding="10">
<tbody>
<tr>
<th class="form_td">משמרת \ יום</td>
<th class="form_td">א</td>
<th class="form_td">ב</td>
<th class="form_td">ג</td>
<th class="form_td">פגרה</td>
<th class="form_td">חש</td>
<th class="form_td">פתוח</td>
<th style="width: 45%;">הערות</td>
</tr>
<?php
$days = array("ראשון","שני","שלישי","רביעי","חמישי","שישי","שבת"); 
for($i=0;$i<7;$i++){
	echo '<tr>
	<td class="form_day"';
        if(isset($notice[$i]) && $notice[$i]==1) echo 'style="background: #4aff4a;"';
        echo '>'.$days[$i].'</td>';
	for($j=0;$j<6;$j++){
		echo '<td class="form_cell"';
                if(isset($notice[$i][$j]) && $notice[$i][$j]==1) echo 'style="background: red;"';
                echo '><input type="hidden" value="0" name="shift'.$i.$j.'"><input type="checkbox" name="shift'.$i.$j.'" value="1"></td>';
	}
	echo '<td class="form_cell"><input style="width: 100%;" type="text" name="shift'.$i.'6" value=""></td>';
	echo '</tr>';
}
echo '</tbody></table>';
?>
<input style="clear:both;float:right;padding:10px;margin:10px;" type="submit" value="שלח\י">
</form>
<?php
}
else {
?>
<table class="responstable emp_table">
    <tr>
        <th colspan="3">
            <form method="post">
            <h2>לשליחת סידור</h2>
        </th>
    </tr>
    <tr style="line-height: 4;">
        <td>
            יש לבחור תאריך בו מתחיל שבוע הסידור:
        </td>
        <td>
            <input style="direction:rtl;" type="date" name="week" required >
        </td>
        <td>
            <input type="submit" value="המשך"></form>
        </td>
    </tr>
    <tr>
        <th colspan="3">
            <form action="recovery.php" method="post" >
            <h2>לשינוי סיסמא</h2>
        </th>
    </tr>
    <tr style="line-height: 4;">
        <td>
            סיסמא חדשה:
        </td>
        <td>
            <input type="text" name="newpass" value="">
            <input type="hidden" name="action" value="password">
        </td>
        <td>
            <input type="submit" value="לשינוי סיסמא">
            </form>
        </td>
    </tr>
    <tr>
        <th colspan="3">
            <form action="recovery.php" method="post">
            <h2>לעדכון מייל</h2>
            <?php
if(strcmp($row["mail"],"")){echo 'המייל שמעודכן במערכת הוא '.$row["mail"];}
?>
        </th>
    </tr>
    <tr style="line-height: 4;">
        <td>
           כתובת מייל לעדכון: 
        </td>
        <td>
            <input type="email" name="email" value="">
            <input type="hidden" name="action" value="email">
        </td>
        <td>
            <input type="submit" value="עדכון מייל">
            </form>
        </td>
    </tr>
	<tr>
        <th colspan="3">
            <form method="post">
            <h2>ארכיון הגשות</h2>
        </th>
    </tr>
    <tr style="line-height: 4;">
        <td>
           שבוע הגשה: 
        </td>
        <td>
            <input type="date" name="date" value="">
            <input type="hidden" name="get_sidur" value="">
        </td>
        <td>
            <input type="submit" value="קבל סידור ארכיוני">
            </form>
        </td>
    </tr>
</table>
<h2>
    סידור אחרון שהגשת נראה כך:
</h2>
<?php
$sql = "SELECT * FROM  `archive` WHERE inerid =  '".$user."' ORDER BY  `date_range_rash` DESC ";
$result = mysqli_query($conn, $sql);
$r = mysqli_fetch_assoc($result);
print_sidur_table($r);    
}
mysqli_close($conn);?>
<form action="kill.php" method="get">

<input type="submit" value="התנתק\י">
</form>
<?php
if(isset($_POST["get_sidur"])){
	$conn=connect_sql();
	$sql = "SELECT * FROM ".$GLOBALS["sidurdatabase"].".archive WHERE inerid='".$_SESSION["taz"]."' AND date_range_rash='".$_POST["date"]."'";
	$result = mysqli_query($conn, $sql);
	$r = mysqli_fetch_assoc($result);
	echo return_sidur_table($r);
}
?>
</body>
</html>