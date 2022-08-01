<?php
/** Allow the authenticated user to upload a malware file and saved into database with a malware name.

saved_bytes table schema:
CREATE TABLE saved_bytes (
        Name VARCHAR(128) NOT NULL,
        saved_bytes CHAR(40) NOT NULL, 
        PRIMARY KEY (Name)
    );
*/
    require_once 'login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die("Sorry, an error occurs!");


    echo <<<_END
            <form action="adminupload.php" method="post" enctype='multipart/form-data'><pre>
                Select File to Upload:                 
                        <input type='file' name='filename'><br>                  
                Malware name:
                        <input type='text' name='name'>
                        <input type='submit' value='Upload' name = 'submit_upload'>
            </pre></form>
    _END;

    if ($_FILES) 
    {
        $name = $_FILES['filename']['name'];
        $name = strtolower(preg_replace("/[^A-Za-z0-9.]/", "", $name));
            
        move_uploaded_file($_FILES['filename']['tmp_name'], "/var/www/html/$name");
            
        // get 20 bytes of the file
        $length = 20; 
        $handle = fopen("/var/www/html/$name", "rb");
        $file_bytes = fread($handle, $length);
        fclose($handle);

        // sanitize the 20 bytes file content
        $hex_pattern = bin2hex($file_bytes);

        // insert the sanitized 20 bytes into the table
        if (isset($_POST['name']))
        {
            $filename = clean_filename($conn, 'filename');
            $a_name = clean_filename($conn, 'name');
            $query = insert_file_info($a_name, $hex_pattern);

            $result = $conn->query($query);

            if (!$result) echo "File handling failed: duplication file or check if you had chosen a file to upload.<br><br>";
            else echo "Your file had been uploaded successfully!<br><br> $name had been saved with the malware name: '$a_name'<br>";
        }
    }
    else echo "No file has been uploaded";

    $result->close();
    $conn->close();

    /* Sanitize filename */ 
    function clean_filename($conn, $var)
    {
        return $conn->real_escape_string($_POST[$var]);
    }

    /* Insert file name into the saved_bytes table */
    function insert_file_info($n, $c){
        return "INSERT INTO saved_bytes VALUES('$n', '$c')";
    }

?>