<?php
/** Authenticating admin by checking user name and user password
If the user information is valid, redirect to upload file page.
*/
    require_once 'login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die("Sorry, an error occurs!");


    echo <<<_END
            <form action="adminlogin.php" method="post" enctype='multipart/form-data'><pre>
                Sign in:<br>
                        User name <input type="text" name="user_name"><br>
                Enter your password:
                        Password <input type="text" name="password"><br>
                        <input type='submit' value='Submit' name = 'submit'> 
            </pre></form>
_END;

    $valid_user = "";
    if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['submit'])) 
    {
        
        $user_name = clean_user_info($conn, 'user_name');
        $password = clean_user_info($conn, 'password');
        $salt1 = substr($password, 0, 4); // start, length index 0123
        $salt2 = substr($password, 1, 4); // index 1234
        $token = hash('ripemd128', "$salt1$password$salt2");
        $query = query_user_info($user_name);
        $result = $conn->query($query);
    
        $rows = $result->num_rows;
        for ($j = 0 ; $j < $rows ; ++$j)
        {
            $result->data_seek($j); // seek to $j rows
            $row = $result->fetch_array(MYSQLI_NUM); // fetch a result row as an associative, a numeric array, or both
            if ($token == $row[1]) 
            {
                echo "Hi $row[0], you had successfully logged in! <br><br>";
                $valid_user = "ok";
            }
            else echo "Incorrect username or password!";
       }   
       
    }  // end if
    if ($valid_user == "ok") 
       {
        header( "Location: http://localhost/adminupload.php" );
        exit ;    
       }

    $result->close();
    $conn->close();

    /* Select information from users table*/
    function query_user_info($user_name){
        return "SELECT * FROM users WHERE user_name = '$user_name'";
    }

    /* Sanitize user info*/
    function clean_user_info($conn, $var)
    {
        if (get_magic_quotes_gpc()) 
            $string = stripslashes($var);

        return $conn->real_escape_string($_POST[$var]);
    }

?>