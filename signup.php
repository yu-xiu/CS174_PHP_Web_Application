<?php
/** Admin sign up page. 
Since this project does not allow the admin to sign up, so this page is used for testing. 
Saved an admin into the database, then check if the admin exist in the database later. 

users schema:
CREATE TABLE users(
        user_name VARCHAR(64) NOT NULL,
        token CHAR(32) NOT NULL,
        salt1 CHAR(4) NOT NULL,
        salt2 CHAR(4) NOT NULL,
        PRIMARY KEY (user_name));
*/
    require_once 'login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die("Sorry, an error occurs!");


    echo <<<_END
            <form action="signup.php" method="post"><pre>
                Sign Up:
                Enter user name:
                        User name <input type="text" name="user_name">
                Enter a password:
                        Password <input type="text" name="password", value = "at least 8 characters">
                        <input type="submit" name="sign_up" value="submit"><br>
            </pre></form>
    _END;
    
    // $_POST['name'], always take name in the post method
    if (isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['sign_up'])) 
    {
        
        $stmt = $conn->prepare(insert_user_info());
        // add a hashed password into the database
        $stmt->bind_param('ssss', $user_name, $token, $salt1, $salt2);
        
        $user_name = clean_user_info($conn, 'user_name');
        $password = clean_user_info($conn, 'password');
        if (strlen($password) < 8) {
            echo "Sorry, password is too short. It should be at leaset 8 characters.";
        }
        else {
            // salt legnth is four, and then add before and after a user's input password
            $salt1 = substr($password, 0, 4);
            $salt2 = substr($password, 1, 4);
        
            $token = hash('ripemd128', "$salt1$password$salt2");
            echo  $user_name." Thank you for sign up!";
            $stmt->execute();
        }
        $stmt->close();
    }

    $conn->close();

    /*Insert into users table*/
    function insert_user_info(){
        return "INSERT INTO users VALUES(?,?,?,?)";
    }

    /*Sanitize user info*/
    function clean_user_info($conn, $var)
    {
        if (get_magic_quotes_gpc()) 
            $string = stripslashes($var);

        return $conn->real_escape_string($_POST[$var]);
    }

?>