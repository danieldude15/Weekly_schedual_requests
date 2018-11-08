<?php
if (isset($_POST["taz"])==FALSE){echo 'איך הגעת לדף הזה?';
$loginmsg="נסיון גישה ישיר לדף יצירת סידור עבודה";
mail('your_email@domain.com',"נסיון כניסה לדף יצירת סידור",$loginmsg,$GLOBALS["headers"]);
exit;}
include 'functions.php'; include 'sidur_funcs.php';
session_start();
$conn=  connect_sql();
$_SESSION["taz"]=$_POST["taz"];
$_SESSION['pass']=$_POST["pass"];
if (login($_SESSION['taz'],$_SESSION['pass'],$conn)==2) 
{
    echo '<link rel="stylesheet" href="style.css">';
    $_SESSION["week"]=$_POST["week"];
    echo '<form action="comp_sidur.php" method="post">';
    $_SESSION["archivebdate"]=convert_sidur_to_arr($_POST["week"],$conn);//convert sidur from DB to array
    get_demand_table();//print to user demand table to later convert to array
    $_SESSION['counter']=availability_by_arr($_SESSION["archivebdate"],0,1);//prints and returns availability to array
    $_SESSION["sidur"]=emp_list_to_arr($conn);//saves emp list for later sidur creation
    echo '<input type="submit" value="יאאלה סידור..."><br>';
}
else
{
   echo ' משתמש וסיסמא לא תקין! נסה שנית';exit; 
}
?>