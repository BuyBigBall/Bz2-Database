<?php 

    $servername = "localhost";
    $username   = "root";
    $password   = "";
    $dbname     = "reddit";

    // /**
    @ini_set('zlib.output_compression',0);
    @ini_set('implicit_flush',1);
    @ob_end_clean();
    @set_time_limit(0);
    @ini_set('memory_limit'         , -1);    // -1 is unlimited
    @ini_set('upload_max_filesize'  , 4000);  // 4G
    @ini_set('max_execution_time'   , 3600);  // 1 hour
    @error_reporting(E_ALL & ~E_NOTICE); # = ini_set('error_reporting', E_ALL);

    // */
    
     if(!empty($_FILES))
    {
        //print_r($_FILES); die;
        $uploaded_filename = $_FILES['bz2file']['tmp_name'];
        //print_r($_FILES); die;
        $bz_fptr = bzopen($uploaded_filename, "r") or die("Couldn't open $uploaded_filename");
        $decompressed_file = "";
        $jsonfilename = date('YndHis').'.json';

        $fp = fopen($jsonfilename, 'a');
        while(!feof($bz_fptr)) {
            $decompressed_file = bzread($bz_fptr, 4096);    
            fwrite($fp, $decompressed_file);              fflush($fp);
        }

        fclose($fp); 
        bzclose($bz_fptr);
        echo "The Upload file name is " . $_FILES['bz2file']['name'] . "\n"; 
        flush();
       
        $cnt = 0;
        # for test 
        # $jsonfilename = '20211214051743.json';
        
        $start_time = time(); 
        print("<br> current utc time is : $start_time ");
        print('<br>bz2 file size is : ' . $_FILES['bz2file']['size'] . 'bytes.<br>');
        flush();
        
        $handle = fopen($jsonfilename, "r");
        $subreddits = [];
        $links      = [];
        $comments   = [];
        if ($handle) {
            $comments_count = 0;
            while (($line = fgets($handle)) !== false) {

                $_object = json_decode($line);
                if(!array_key_exists($_object->subreddit_id , $subreddits))
                    $subreddits[$_object->subreddit_id] = $_object->subreddit;
                if(!array_key_exists($_object->link_id , $links))
                    $links[$_object->link_id] = $_object->subreddit_id;
            
                if(!!empty( $test ))
                # to except inserting into comments table.
                {
                    $comments_count++;
                    $comments[$_object->id] = [
                        'parent_id'  => !empty($_object->parent_id) ? $_object->parent_id : '',
                        'link_id'    => !empty($_object->link_id) ? $_object->link_id : '',
                        'author_id'  => !empty($_object->author) ? $_object->author : '',
                        'created_utc'=> !empty($_object->created_utc) ? date('Y-n-d H:i:s', $_object->created_utc) : '',
                        'body'       => !empty($_object->body) ? $_object->body : '',
                        'score'      => !empty($_object->score) ? $_object->score : '0',
                        'ups'        => !empty($_object->ups) ? $_object->ups : '0',
                        'downs'      => !empty($_object->downs) ? $_object->downs : '0',
                    ];
                }
                if( count($comments)>=2000)
                {
                    Cmmentsave($comments, $cnt++);
                    if($cnt%10==0)
                    {
                        print("<br>comments batch insert " . ($comments_count). " records performed at : " . time() . " <br>"); 
                        flush();
                    }
            
                    $comments = [];
                }
            }

            fclose($handle);
        } else {
            # error opening the file.
            print("json file open failed.");
            die();
        } 
        if( count($comments)>0)
        {
            Cmmentsave($comments, $cnt++);
            print("<br>comments batch insert " . ($comments_count). " records performed at : " . time() . " <br>"); 
            flush();
            $comments = [];
        }
        print('<br> comments table data importing has been finished. the count of records =' . $comments_count . "<br>" ); flush();

        print("<br> now start to insert the data into subreddits and links table."); flush();
        print("<br> current utc time is : " .time(). ""); flush();
        print('<br> subreddits records is : ' . count($subreddits) ); flush();
        print('<br> links records is : ' . count($links) ); flush();
       
        Datasave($subreddits, $links);

        $end_time = time(); 
        print("<br> ending utc time is : $end_time <br>"); flush();
        print("<br> spending seconds is : " . ($end_time - $start_time) . " <br>"); flush();
    }
    else
    {
        ?>
        <div style='width:400px; height:300px; margin:0 auto; padding:200px 0;'>
            <div style='border:solid 1px #ccc;padding:0 20px;'>
                <form method='post' enctype='multipart/form-data'>
                    <p><label for='bz2file'>Please select bz2 database file.
                        <br />
                            <input type='file' id='bz2file' name='bz2file'>
                    </label></p>
                    <input type='submit' value='upload'>
                </form>
            </div>
        </div>
        <?php 
    }

    function Cmmentsave($comments, $cnt )
    {
        global $servername;
        global $username;
        global $password;
        global $dbname;
            
        # Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        # Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $count = 0;
        $batch = true;

        if($cnt==0) 
        {
            $conn->query("TRUNCATE TABLE comments");
        }

        $sql = "INSERT INTO comments (id, parent_id, link_id, author_id, created_utc, body, score, ups, downs ) VALUES ";
        $sql_values = [];
        foreach($comments as $id=>$item)
        {
            $item['body'] = str_replace("\\", "\\\\", $item['body']);
            $item['body'] = str_replace("'", "\'", $item['body']);
            $sql_values[] = "('$id', '".$item['parent_id']."', '".$item['link_id']."', '".$item['author_id']."', '".$item['created_utc']."', '".$item['body']."', '".$item['score']."', '".$item['ups']."', '".$item['downs']."')";
        }
        if ($conn->query($sql . implode(',', $sql_values)) !== TRUE) 
        {
            print($conn->error . ".<br>"); flush();
            print($sql . implode(',', $sql_values)); die();
        }
            
        $conn->close();
        
    }
    function Datasave($subreddits, $links )
    {
        global $servername;
        global $username;
        global $password;
        global $dbname;

        # Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        # Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $conn->query('TRUNCATE TABLE subreddits');
        $sql = "INSERT INTO subreddits (subreddit_id, subreddit) VALUES ";
        
        $count = 0; $sql_values = [];
        foreach($subreddits as $subreddit_id=>$item)
        {
            $sql_values[] = " ('$subreddit_id', '$item')";
            if(count($sql_values)>=1000)
            {
                $conn->query($sql . implode(',', $sql_values));
                $sql_values = [];
            }

        }
        if ($conn->query($sql . implode(',', $sql_values)) !== TRUE) 
        {
            print(" data insert into the subreddit table has error .<br>"); flush();
        }
        print("<br><br>subreddits table insert completed at : " . time() . " <br>"); flush();

        $conn->query('TRUNCATE TABLE links');
        $sql = "INSERT INTO links (link_id, subreddit_id) VALUES ";
        $sql_values = [];
        foreach($links as $link_id=>$item)
        {
            $sql_values[] = "('$link_id', '$item')";
            if(count($sql_values)>=1000)
            {
                $conn->query($sql . implode(',', $sql_values));
                $sql_values = [];
            }
        }
        if ($conn->query($sql . implode(',', $sql_values)) !== TRUE) 
        {
            print(" data insert into the links table has error .<br>"); flush();
        }
        print("<br>link table insert completed at : " . time() . " <br>"); flush();
        $conn->close();
    }