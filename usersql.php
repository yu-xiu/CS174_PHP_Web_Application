<?php
/**CS174 Midterm2 Yu Xiu*/ 
/**Allow normal user to upload a putative infected file and shows if it is infected or not.*/
    require_once 'login.php';
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die("Sorry, an error occurs!");


    echo <<<_END
            <form action="usersql.php" method="post" enctype='multipart/form-data'><pre>                                                                              
                Select File to Upload:                                                    
                        <input type='file' name='filename'><br> 
                        <input type='submit' value='Submit' name = 'submit_upload'>
            </pre></form>
_END;    

    if (isset($_POST['submit_upload'])) 
    {
        if ($_FILES)                                                                                                                                          
        {                         
            $name = $_FILES['filename']['name'];  

            //sanitize file name   
            $name = strtolower(preg_replace("/[^A-Za-z0-9.]/", "", $name));                                                                                                                                 
            move_uploaded_file($_FILES['filename']['tmp_name'], "/var/www/html/$name");
        
            $handle = fopen("/var/www/html/$name", "rb");
            
            // get entire file by bytes
            $file_bytes = fread($handle, filesize("/var/www/html/$name"));
            
            // sanitize entire file
            $hexdata = bin2hex($file_bytes);
            fclose($handle);

            $query = "SELECT * FROM saved_bytes";
            $result = $conn->query($query);
            if (!$result) die ("Database access failed.");

            $contain_virus = "";
            $rows = $result->num_rows;
            for ($j = 0 ; $j < $rows ; ++$j)
            {
                $result->data_seek($j);
                $row = $result->fetch_array(MYSQLI_NUM);
                $pos = 0;
                while (true) {
                    $pos = strpos($hexdata, $row[1], $pos);
                    if ($pos === false) {   // no virus
                        break;
                    } elseif ($pos % 2 == 1) {  // half byte
                        $pos += 1;
                        continue; 
                    } else {
                        $contain_virus = "ok";
                        break;
                    }
                }
            }  
            if ($contain_virus == "ok") {
                echo "Your file '$name' contains VIRUS!";
            }
            else {
                echo "Your file '$name' is safe!";
            } 
        }
        else echo "No uploads!";
    }

    $result->close();
    $conn->close();
?>
