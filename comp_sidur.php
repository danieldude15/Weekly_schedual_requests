<?php
session_start();
if (isset($_SESSION["taz"])==FALSE){echo 'איך הגעת לדף הזה?';
$loginmsg="נסיון גישה ישיר לדף יצירת סידור עבודה";
mail('your_email@domain.com',"נסיון כניסה לדף יצירת סידור",$loginmsg,$GLOBALS["headers"]);
exit;}
include 'functions.php'; include 'sidur_funcs.php';
echo '   <form action="add-remove-emp.php" method="post">
	<input type="hidden" name="taz" value="'.$_SESSION['taz'].'">
	<input type="hidden" name="pass" value="'.$_SESSION['pass'].'">
	<input type="submit" value="ניהול עובדים">
	</form>';
$conn=  connect_sql();
if (login($_SESSION['taz'],$_SESSION['pass'],$conn)==2) 
{
    echo '<link rel="stylesheet" href="style.css">';
    echo '<form method="post">';
    
    if(isset($_POST["demand00"])){//this should convert $_POST of demand table to array
        $_SESSION["demand"]=demand_table_2arr($_POST);        
    }
    
    if(isset($_POST["fixprob"])){//if fount conflict it general table
        reassign_emps($_POST,$_SESSION["archivebdate"],$_SESSION["fix"]);
    }
    
    $_SESSION['counter']=availability_by_arr($_SESSION["archivebdate"],0,1);//prints and returns availability to array
    $_SESSION['fix']=check_sidur_conflict($_SESSION['counter'],$_SESSION["demand"]);//this should print the DEMAND table with conflicts
    echo '<input type="submit" value="המשך"><br></form>';
    
    if($_SESSION["fix"]!=FALSE){//ask admin for employee assignment to availability
        fix_problem_table($_SESSION["fix"],$_SESSION["archivebdate"]);
    }
    else {//continue to assign shabbat shifts
        $_SESSION['fix']=$_SESSION["shabbat"]=assign_shabbat($_SESSION["archivebdate"],$_SESSION["counter"],$_SESSION["demand"]);
        if($_SESSION["shabbat"]==FALSE){//if shabat shifts are done
            assign_shift(5,0,$_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"]);
            assign_shift(5,1,$_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"]);
            assign_shift(5,2,$_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"]);
            assign_shift(6,0,$_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"]);
            assign_shift(6,1,$_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"]);
            assign_shift(6,2,$_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"]);
            if(assign_week($_SESSION["archivebdate"],$_SESSION["sidur"],$_SESSION["demand"])){
                print_comp_sidur($_SESSION["sidur"],$_SESSION["archivebdate"],$_SESSION["demand"]);
            }
        }
        else {
            echo 'description';
        }
    }
    
}
else
{
   echo ' משתמש וסיסמא לא תקין! נסה שנית';exit; 
}