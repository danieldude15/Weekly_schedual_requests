<?php 
session_start();
include_once  'header.php';
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
	exit();
}
if (array_key_exists('taz',$_POST)){
    $_SESSION["taz"]=$_POST["taz"];
    $_SESSION["pass"]=$_POST["pass"];
}
elseif (isset($_SESSION)==FALSE){echo 'איך הגעת לדף הזה?';
$loginmsg="נסיון גישה ישיר לדף טופס שליחת הסידור של עובדים";
if($GLOBALS["sendmail"]){mail('your_email@domain.com',"נסיון כניסה לדף הטופס ללא משתמש",$loginmsg,$GLOBALS["headers"]);}
exit;}
if(isset($_SESSION["taz"])){$user=$_SESSION["taz"];$pass=$_SESSION["pass"];}
else {$user=$pass=FALSE;}
$check=login($user,$pass,$conn);
if ($check<=1)
{echo 'אין לך גישה לעמוד זה חזור <a href="'.$GLOBALS["home_url"].'">לדף הבית</a>';

exit;}
else if ($check==2)
{include 'admin.php';
if(isset($_POST["action"])){
echo '<div style="overflow: auto;">';
if(!strcmp($_POST["action"],"add_emp")){
add_emp($_POST,$conn);
}
else if(!strcmp($_POST["action"],"edit_emp")){
edit_emp($_POST,$conn);
}
else if(!strcmp($_POST["action"],"remove_emp")){
remove_emp($_POST,$conn);
}
else if(!strcmp($_POST["action"],"recover_emp")){
recover_emp($_POST,$conn);
}
else if(!strcmp($_POST["action"],"add_ship")){
add_ship_notice($_POST,$conn);
}
else if(!strcmp($_POST["action"],"remove_ship")){
remove_ship($_POST,$conn);
}
else if(!strcmp($_POST["action"],"printone")){
printone($_POST,$conn);
}
else if(!strcmp($_POST["action"],"check_availability")){
check_availability($_POST,$conn,1);
exit;
}
else if(!strcmp($_POST["action"],"edit_emp_misra")){
edit_emp_misra($_POST,$conn);
}
else if(!strcmp($_POST["action"],"edit_DL")){
edit_DL($_POST,$conn);
}
else if(!strcmp($_POST["action"],"edit_superviser_night")){
edit_superviser_night($_POST,$conn);
}
else if(!strcmp($_POST["action"],"edit_superviser")){
edit_superviser($_POST,$conn);
}
}
echo '</div>';
?>
<!--                      הוספת עובד חדש!!!                       -->
<div class="admin_box">
<h3>הוסף עובד/ת</h3>
<form method="post">
<input type="hidden" name="action" value="add_emp">
משתמש: <input type="text" name="inerid" value="" required> <br>
סיסמה: <input type="text" name="newpass" value="" required><br>
שם פרטי: <input type="text" name="f_name" value="" required> <br>
שם משפחה: <input type="text" name="l_name" value="" required> <br>
קבוצה: <input type="text" name="teamnum" value="" required><br>
מספר ברשימה: <input  type="text" name="indexG" value="" required><br>
משמרות בשבוע: <input  type="text" name="misra" value="" required><br>
רשיון נהיגה:<input type="radio" name="driver"
<?php if (isset($driver) && $driver==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="driver"
<?php if (isset($driver) && $driver==="0") {echo "checked";}?>
value="0">לא</br>
אחמש:<input type="radio" name="super"
<?php if (isset($super) && $super==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="super"
<?php if (isset($super) && $super==="0") {echo "checked";}?>
value="0">לא&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
אחמש לילה:<input type="radio" name="nightsuper"
<?php if (isset($nightsuper) && $nightsuper==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="nightsuper"
<?php if (isset($nightsuper) && $nightsuper==="0"){ echo "checked";}?>
value="0">לא&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
דתי:
<input type="radio" name="religion"
<?php if (isset($religion) && $religion==="0"){ echo "checked";}?>
value="0" >לא
<input type="radio" name="religion"
<?php if (isset($religion) && $religion==="1"){ echo "checked";} ?> 
value="1">כן<br>
עובד לילות:<input type="radio" name="nights"
<?php if (isset($nightsuper) && $nightsuper==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="nights"
<?php if (isset($nightsuper) && $nightsuper==="0"){ echo "checked";}?>
value="0">לא
<input type="submit" value="הוסף עובד\ת">
</form>
<p>
    הסבר קצר על אפשרות הוספה של עובדים.<br>
  המערכת תיצור משתמש חדש עם הפרטים שתמלאי בשדות הנ"ל<br>
  יש למלא את כל השדות!
</p>
</div>
<!--			     עריכת עובד קיים!!!  					-->
<div class="admin_box">
<h3>ערוך פרטי עובד</h3>
<form method="post">
<input type="hidden" name="action" value="edit_emp">
שם עובד: <select  name="inerid">
<option value="null">בחר</option>
<?php
$sql = "SELECT * FROM emp ORDER BY team, indexG;";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) 
{
	echo "<option value=".$row['inerid'].">".$row['firstname_rash']." ".$row['lastname_rash']."</option>";
}
?>
</select>
סיסמה: <input type="text" name="newpass" value="" ><br>
שם פרטי: <input type="text" name="f_name" value="" > <br>
שם משפחה: <input type="text" name="l_name" value="" > <br>
קבוצה: <input type="text" name="teamnum" value="" ><br>
מספר ברשימה: <input  type="text" name="indexG" value="" ><br>
משמרות בשבוע: <input  type="text" name="misra" value="" ><br>
רשיון נהיגה:<input type="radio" name="driver"
<?php if (isset($driver) && $driver==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="driver"
<?php if (isset($driver) && $driver==="0") {echo "checked";}?>
value="0">לא</br>
אחמש:<input type="radio" name="super"
<?php if (isset($super) && $super==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="super"
<?php if (isset($super) && $super==="0") {echo "checked";}?>
value="0">לא&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
אחמש לילה:<input type="radio" name="nightsuper"
<?php if (isset($nightsuper) && $nightsuper==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="nightsuper"
<?php if (isset($nightsuper) && $nightsuper==="0"){ echo "checked";}?>
value="0">לא&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
דתי:
<input type="radio" name="religion"
<?php if (isset($religion) && $religion==="0"){ echo "checked";}?>
value="0" >לא
<input type="radio" name="religion"
<?php if (isset($religion) && $religion==="1"){ echo "checked";} ?> 
value="1">כן<br>
עובד לילות:<input type="radio" name="nights"
<?php if (isset($nightsuper) && $nightsuper==="1"){ echo "checked";} ?>
value="1" >כן
<input type="radio" name="nights"
<?php if (isset($nightsuper) && $nightsuper==="0"){ echo "checked";}?>
value="0">לא
<input type="submit" value="עריכת עובד/ת">
<p>
    כאן עלייך לבחור עובד מהרשימה אותו את רוצה לערוך ולעדכן את הפרטים שלו.
</p>
</form>
</div>
<!--			     		הסרת עובד קיים!!!  					-->
<div class="admin_box">
<h3>הסר עובד</h3>
<form method="post">
<input type="hidden" name="action" value="remove_emp">
משתמש: 
<select  name="inerid">
<option value="null">בחר</option>
<?php
$sql = "SELECT * FROM emp ORDER BY team, indexG;";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) 
{
	echo "<option value=".$row['inerid'].">".$row['firstname_rash']." ".$row['lastname_rash']."</option>";
}
?>
</select><br>

<input type="submit" value="הסר עובד/ת">
<p>
    אפשרות זו מסירה עובדים מהמערכת. האפשרות לא מוחקת את הסידורים האכיונים שנמסרו על ידי אותו עובד.
</p>
</form>
</div>
<!--			     		הדפסת סידור ספציפי!!!  					-->
<div class="admin_box">
<h3>הצג סידור ספציפי</h3>
<form method="post">
<input type="hidden" name="action" value="printone">
משתמש: <input  type="text" name="inerid" value="">
לשבוע שמתחיל ב: <input  type="date" name="week" value="" required>
<input type="submit" value="הדפס סידור" >
<p>
    אפשרות זו מציגה לך על המסך את הסידור של אותו שם משתמש שתזיני בשדה משתמש.
</p>
</form>
</div>
<!--			     		בדיקת זמינות סידור!!!  					-->
<div class="admin_box">
<h3>בדיקת זמינות לשבוע נבחר </h3>
<form method="post">
<input type="hidden" name="action" value="check_availability">
    לשבוע שמתחיל ב: <input  type="date" name="week" value="" required>
<input type="submit" value="בדוק זמינות" >
</form>
<p>
    אפשרות זו רק אוספת את הסידורים שנשלכו עד כה לתאריך הנבחר ומציגה אותם בקצרה במסך<br>
(לא כמו האפשרות "הדפסת סידור" שבנוסף להצגת הסידורים גם שולחת סידורים פתוחים למי שטרם הגיש סידור.)

</p>
</div>
<div class="admin_box">
    <h3>הדפסת סידור והשלמה לשבוע נבחר <font color="red"> מיידע את כולם באימייל!</font></h3>
	<form method="post">
        <input type="hidden" name="admin_action" value="printit">
	הדפיסי סידור לשבוע :<br>
	<input type="date" name="week" required>
        <br><input type="hidden" name="mailall" value="1"><input type="checkbox" name="mailall" value="0">נא לא לידע את כולם שהדפסתי סידור<br>
        <br><input type="hidden" name="updateall" value="1"><input type="checkbox" name="updateall" value="0">נא לא לעדכן סידור פתוח לאלו שטרם הגישו סידור<br>
	<input type="submit" value="הדפס">
	</form>
    <p>
        שימי לב! אם לא סימנת לא לשלוח אימייל לכולם, כולם ידעו שבעצם "שהוצאת את פתקי הסידור של כולם מהמגירה ואת מתחילה לעבוד על סידור" מי שלא שלח סידור עד כה יעודכן לו אפשרויות פתוחות לכל השבוע.
    </p>
</div>
<!--			     		יצירת סידור עבודה לעובד!!!     -->					
<div class="admin_box">
<h3>התחבר בתור עובד</h3>
<form action="make_sidur.php" method="post">
שם עובד: 
<select  name="taz">
<option value="null">בחר</option>
<?php
$sql = "SELECT * FROM emp ORDER BY team, indexG;";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) 
{
	echo "<option value=".$row['inerid'].">".$row['firstname_rash']." ".$row['lastname_rash']."</option>";
}
?>
</select><br>
<input  type="hidden" name="special" value="StupidSecurityBreachButWhatever" required>
<input type="submit" value="לדף הגשה" >
</form>
</div>


<div class="admin_box">
<h3>הוספת הערות לבקרים</h3>
<form method="post">
<input type="hidden" name="action" value="add_ship">
בקר יקר שים לב לצורך במשמרות הבאות:
ביום: <input  type="date" name="week" value="" required><br>
נדרש זמינות למשמרת:
א<input type="hidden" name="shift0" value="0"><input type="checkbox" name="shift0" value="1">
ב<input type="hidden" name="shift1" value="0"><input type="checkbox" name="shift1" value="1">
ג<input type="hidden" name="shift2" value="0"><input type="checkbox" name="shift2" value="1">
בעקבות:<input  type="text" name="ship" value=""><br> 
<input type="submit" value="הוסף הערה לבקרים" >
</form>
<p>
    אפשרות זו מוסיפה לבקרים המגישים סידור לשבוע בו נמצא תאריך ההערה וזה יראה כך:<br>
    <font color="red">ביום (היום של התאריך) נדרש זמינות למשמרת (בוקר\צהריים\ערב) בעקבות (הערה שהזנת "בעקבות").</font>
</p>
</div>

<div class="admin_box">
<h3>הסר אוניה מהלוז</h3>
<form method="post">
<input type="hidden" name="action" value="remove_ship">
מספר מזהה בלוז(#): <input  type="text" name="id" value="">
<input type="submit" value="הסר אוניה">
</form>
<p>
    באפשרות זו ניתן להסיר הערות שהוספנו ב"הערות לבקרים".<br>
    אם נרד למטה נמצא טבלה בא ניתן לראות את 30 ההערות האחרונות שהכנסנו למערכת.<br>
    לכל הערה יש מספר מזהה יחודי משלו. אם נכתוב את מספר ההערה היחודי הוא ימחק ולא יוצג לבקרים בעת הגשת סידור שבועי.
</p>
</div>
<div class="admin_box">
<h3>שליחת מייל לעובדים</h3>
<a href="mail-all.php">לחץ כאן למעבר לעמוד שליחת מייל לכל העובדים</a>
<p>
    אפשרות זו מעבירה אותך לעמוד בו תדרשי לכתוב נושא הודעה למייל ותוכן הודעה במייל.
    לאחר לציחת שלח מייל בתחתית העמוד הפרטים ישלחו לכל העובדים שעדכנו מייל במערכת.
</p>
</form>
</div>
<p>
<?php 
$sql = "SELECT * FROM emp ORDER BY team, indexG;";
$result = mysqli_query($conn, $sql);

if ($qty = mysqli_num_rows($result) > 0) { 
        print_emp_table($result);
} 
else {
    echo "0 results";
}
$sql = "SELECT * FROM notice ORDER BY update_time DESC";
$result = mysqli_query($conn, $sql);

if ($row = mysqli_num_rows($result) > 0) { 
        print_expected_ships($row,$result);
} 
else {
    echo "0 results";
}
mysqli_close($conn);
}
?>
</p>
</body>
</html>
