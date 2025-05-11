<?php
    $url = trim(addslashes(strip_tags($_GET['url'])));
?>

<form action="" method="GET" name="form">
    <input type="text" name="url" placeholder="Profile URL..." value="<?php echo $url; ?>" size="100"><br><br>
    <button type="submit">Get Followers/Following</button>
</form>

<?php
    if(empty($url))
    {
        exit;
    }
    else
    {
        $explode_url = explode("@", $url);
        $mastodon_domain = $explode_url[0];
        $mastodon_username = $explode_url[1];
        $check = '/^[a-zA-Z0-9_]+/';

        if(filter_var($mastodon_domain, FILTER_VALIDATE_URL) AND preg_match($check, $mastodon_username))
        {
            $profile_url = $url;
        }
        else
        {
            echo "Forbidden value of GET variable";
            exit;
        }
    }


    // GET ACCOUNT ID
    $api_url = $mastodon_domain."/api/v1/accounts/lookup?acct=".$mastodon_username;

    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $json = curl_exec($curl);
    $api_result = json_decode($json, true);
    $mastodon_id = $api_result['id'];

    if(empty($mastodon_id))
    {
        echo "Error while getting account ID, failed to connect to API";
        exit;
    }


    // FUNCTIONS
    function HeaderLink($curl, $header_line) {
         if(str_contains($header_line, "link:") || str_contains($header_line, "Link:"))
        {
            $GLOBALS['link'] = $header_line;
        }
        return strlen($header_line);
    }


    // GET LIST OF FOLLOWERS
    $followers_counter = 0;
    $followers = array();
    $followers_ids = array();
    
    $api_url = $mastodon_domain."/api/v1/accounts/".$mastodon_id."/followers?limit=80";
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
    $json = curl_exec($curl);
    $api_result = json_decode($json, true);

    foreach($api_result as $follow)
    {
        if(!in_array($follow['id'], $followers_ids))
        {
            $followers_ids[] = $follow['id'];
            $followers[] = array(
                "id" => $follow['id'], 
                "acct" => $follow['acct'],  
                "display_name" => $follow['display_name'],  
                "url" => $follow['url'],  
                "followers_count" => $follow['followers_count'],  
                "following_count" => $follow['following_count'],  
                "statuses_count" => $follow['statuses_count'],
				"last_status_at" => $follow['last_status_at'],
				"created_at" => $follow['created_at'],
				"note" => $follow['note']
            );
            $followers_counter++;
        }
    }
    preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);
    
	if ($temp[1] && $api_url !== $temp[1]) {
		$api_url = $temp[1];
	} else {
		$api_url = null;
	}

    while(!empty($api_url))
    {
        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
        $json = curl_exec($curl);
        $api_result = json_decode($json, true);

        foreach($api_result as $follow)
        {
            if(!in_array($follower['id'], $followers_ids))
            {
                $followers_ids[] = $follow['id'];
                $followers[] = array(
                    "id" => $follow['id'], 
                    "acct" => $follow['acct'],  
                    "display_name" => $follow['display_name'],  
                    "url" => $follow['url'],  
                    "followers_count" => $follow['followers_count'],  
                    "following_count" => $follow['following_count'],  
                    "statuses_count" => $follow['statuses_count'],
					"last_status_at" => $follow['last_status_at'],
					"created_at" => $follow['created_at'],
					"note" => $follow['note']
                );
                $followers_counter++;
            }
        }
        preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);

	if ($temp[1] && $api_url !== $temp[1]) {
		$api_url = $temp[1];
	} else {
		$api_url = null;
	}
	
    }


    // GET LIST OF FOLLOWING
	sleep(1);
    $following_counter = 0;
    $following = array();
    $following_ids = array();
    
    $api_url = $mastodon_domain."/api/v1/accounts/".$mastodon_id."/following?limit=80";
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
    $json = curl_exec($curl);
    $api_result = json_decode($json, true);

    foreach($api_result as $follow)
    {
        if(!in_array($follow['id'], $following_ids))
        {
            $following_ids[] = $follow['id'];
            $following[] = array(
                "id" => $follow['id'], 
                "acct" => $follow['acct'],  
                "display_name" => $follow['display_name'],  
                "url" => $follow['url'],  
                "followers_count" => $follow['followers_count'],  
                "following_count" => $follow['following_count'],  
                "statuses_count" => $follow['statuses_count'],
				"last_status_at" => $follow['last_status_at'],
				"created_at" => $follow['created_at'],
				"note" => $follow['note']
            );
            $following_counter++;
        }
    }
    preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);

	if ($temp[1] && $api_url !== $temp[1]) {
		$api_url = $temp[1];
	} else {
		$api_url = null;
	}

    while(!empty($api_url))
    {
        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36');
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, "HeaderLink");
        $json = curl_exec($curl);
        $api_result = json_decode($json, true);

        foreach($api_result as $follow)
        {
            if(!in_array($follow['id'], $following_ids))
            {
                $following_ids[] = $follow['id'];
                $following[] = array(
                    "id" => $follow['id'], 
                    "acct" => $follow['acct'],  
                    "display_name" => $follow['display_name'],  
                    "url" => $follow['url'],  
                    "followers_count" => $follow['followers_count'],  
                    "following_count" => $follow['following_count'],  
                    "statuses_count" => $follow['statuses_count'],
					"last_status_at" => $follow['last_status_at'],
				    "created_at" => $follow['created_at'],
					"note" => $follow['note']
                );
                $following_counter++;
            }
        }
        preg_match("(link: <(.+?)>; rel=\"next\", <.+?>; rel=\"prev\")is", $GLOBALS['link'], $temp);

	if ($temp[1] && $api_url !== $temp[1]) {
		$api_url = $temp[1];
	} else {
		$api_url = null;
	}
	
    }
?>

<h1>Followers</h1>
<b>Number of followers found:</b> <?php echo $following_counter; ?><br><br>
<table>
    <tr>
        <th>Lp.</th>
        <th>ID</th>
        <th>Handle</th>
        <th>Name</th>
        <th>Followers</th>
        <th>Following</th>
        <th>Toots</th>
        <th>URL</th>
		<th>Last Status</th>
		<th>Created at</th>
		<th>Note</th>			
    </tr>
<?php
    $i = 1;
    foreach($following as $follow)
    {
        echo "<tr>";
        echo "<td>".$i."</td>";
        echo "<td>".$follow['id']."</td>";
        echo "<td>".$follow['acct']."</td>";
        echo "<td>".$follow['display_name']."</td>";
        echo "<td>".$follow['followers_count']."</td>";
        echo "<td>".$follow['following_count']."</td>";
        echo "<td>".$follow['statuses_count']."</td>";
        echo "<td><a href=\"".$follow['url']."\">".$follow['url']."</a></td>";
		echo "<td>".$follow['last_status_at']."</td>";
		echo "<td>".$follow['created_at']."</td>";
		echo "<td>".$follow['note']."</td>";
        echo "</tr>";

        $i++;
    }
	$random_file = fopen("output.html", "w");
		fwrite($random_file, "<table>");
		fwrite($random_file, "<tr>");
        fwrite($random_file, "<th>Lp.</th>");
        fwrite($random_file, "<th>ID</th>");
        fwrite($random_file, "<th>Handle</th>");
        fwrite($random_file, "<th>Name</th>");
        fwrite($random_file, "<th>Followers</th>");
        fwrite($random_file, "<th>Following</th>");
        fwrite($random_file, "<th>Toots</th>");
        fwrite($random_file, "<th>URL</th>");
		fwrite($random_file, "<th>Last Status</th>");
		fwrite($random_file, "<th>Created at</th>");
		fwrite($random_file, "<th>Note</th>");	
		fwrite($random_file, "</tr>");
	
	
	
	
    $i = 1;
    foreach($following as $follow)
    {
		 fwrite($random_file, "<tr>");
         fwrite($random_file, "<td>".$i."</td>");
         fwrite($random_file, "<td>".$follow['id']."</td>");
         fwrite($random_file, "<td>".$follow['acct']."</td>");
         fwrite($random_file, "<td>".$follow['display_name']."</td>");
         fwrite($random_file, "<td>".$follow['followers_count']."</td>");
         fwrite($random_file, "<td>".$follow['following_count']."</td>");
         fwrite($random_file, "<td>".$follow['statuses_count']."</td>");
         fwrite($random_file, "<td><a href=\"".$follow['url']."\">".$follow['url']."</a></td>");
		 fwrite($random_file, "<td>".$follow['last_status_at']."</td>");
		 fwrite($random_file, "<td>".$follow['created_at']."</td>");
		 fwrite($random_file, "<td>".$follow['note']."</td>");
         fwrite($random_file, "</tr>");

        $i++;
	
		 
    }
	fwrite($random_file, "</table>");
	fclose($random_file);
?>
</table>
