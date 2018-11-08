<?php
session_start();
include_once  'header.php';
$conn=connect_sql();
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
	exit();
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
{
include_once 'admin.php';
if(!strcmp($_POST["action"],"send_mail")){
email_all($_POST,$conn);
exit;
}
?>
    <form method="post" id="email">
    <input type="hidden" name="action" value="send_mail">
    <table class="email-table" border="1">
        <tr>
            <th width="10%">
                נושא
            </th>
            <td>
                <input type="text" name="title" style="width: 100%;">
            </td>
        </tr>
        <tr>
            <th width="10%"valign="top">
                תוכן ההודעה
            </td>
            <td style="width: 100%;height:400px;">
                <textarea name="msg" form="email" style="margin: 0px; height: 400px; width: 100%;">תוכן המייל...</textarea>
            </td>
        </tr>
    </table>
<input type="submit" value="שלח מייל">
<?php
}

?>