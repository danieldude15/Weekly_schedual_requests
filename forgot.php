<?php
include 'header.php';
if(isset($_POST['inerid'])) {
    if (array_key_exists('capcha',$_POST) && $_POST['capcha']!='capcha'){
echo "לא עדכנת את שמו הנכון של מנהל האשכול, השאלה באה למנוע מכל מיני רובוטים שמטיילים ברשת לפגוע המערכת היקרה שלנו . מלאו את השדה בשמו הפרטי"."<br><a href='".$GLOBALS["home_url"]."'>לדף התחברות  </a>";
exit;
}
$conn=connect_sql();
    if($row=userexist($_POST['inerid'],$conn)){
    if(empty($row['mail'])){echo 'אי אפשר לאפס סיסמה כי אין לך כתובת מייל מעודכנת בכרטיס עובד';}
    else{
    $pass=magic_password_generator_function();//implement yourself
    $sql = "UPDATE `emp` SET `pass` = '$pass' WHERE `emp`.`inerid` = '".$_POST['inerid']."'";
    $result=mysqli_query($conn, $sql);
    if(!$result){died("שגיאה בהרצת פקודת SQL".mysqli_error($conn));exit;}
    $subject="איפוס סיסמה לסידור עבודה";
    if(mail($row["mail"]." ,".$GLOBALS["adminmail"], $subject, "הסיסמה החדשה שלך היא:  ".$pass, $headers))
    {
        echo "<br> בוצע איפוס לסיסמתך. סיסמה חדשה שנלחה במייל!!".'<a href="'.$home_url.'">לדף הבית</a>';
    }
}

}
    }
else {
    echo '<h1>דף איפוס סיסמה</h1><form method="post"><table><tr><td>
שם משתמש:</td>
<td> 
<input type="text" name="inerid" value="" required>
</td></tr>
<tr><td>
מהו שמו הפרטי של מנהל האשכול שלך(בעברית):</td>
<td> 
<input type="text" name="capcha" value="" required>
</td></tr>
</table>'
. '<input type="submit" value="אפס סיסמה"></form>'
            . '<a href="'.$GLOBALS["home_url"].'">לדף התחברות</a>';
}
