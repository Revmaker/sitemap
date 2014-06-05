<!--
This generates a sitemap for any of our websites from the database schema newcars.
It assumes you have an updated copy of the database available locally.
It has options for setting the relative priority of make- and model-specific pages.
It can generate a new robots.txt.

TO-DO: add "auto-submit to google" option (first need to submite manually once).
-->
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<!-- While reading about CSS, I came across the following three meta tags,
	which I think I should get in the habit of using. --> 
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="This generates a sitemap for any of our websites from the database schema newcars. It can generate a new robots.txt.">
    <meta name="author" content="Sam and Sandy">

	<!-- I use the Twitter Bootstrap CSS. I hope this isn't cheating... It just looks so clean! -->
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">

	<title>XML Sitemap Generator</title>
    <script type="text/javascript">

	    function showHide()
	    {
	        if(document.getElementById('checkRobots').checked)
	        {
	            document.getElementById('disallowed').style.visibility = 'visible';
	        }
	        else
	        {
	            document.getElementById('disallowed').style.visibility = 'hidden';
	        }
	    }

	</script>
</head>
<body>

	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
          </button>
          <a class="navbar-brand" href="#">Sitemap Generator</a>
        </div>
      </div>
    </div>


    <div class="jumbotron">
    <h1>Sitemap Generator 1.1</h1>
 	<p>New fancy look, <em>same great <s>taste</s> functionality!</em></p>
    <p>This site generates a sitemap.xml file for submitting to search engines.</p>
 	<p>It can also generate a new robots.txt file.</p>
    </div>


	<form class="form-horizontal" role="form" id="urlform" method="post">
	  <div class="form-group">
	    <label for="baseurl" class="col-sm-2 control-label">Base URL</label>
	    <div class="col-sm-10">
	      <input type="text" class="col-md-4" id="baseurl" name="baseurl" value="www.gtvintage.com">
	    </div>
	  </div>
	  <div class="form-group">
	    <label for="makepriority" class="col-sm-2 control-label">Make Priority</label>
	    <div class="col-sm-10">
	      <input type="text" class="col-md-1" id="makepriority" name="makepriority" value="0.8">
	    </div>
	  </div>
	  <div class="form-group">
	    <label for="modelpriority" class="col-sm-2 control-label">Model Priority</label>
	    <div class="col-sm-10">
	      <input type="text" class="col-md-1" id="modelpriority" name="modelpriority" value="0.5">
	    </div>
	  </div>	  
	  <div class="form-group">
	    <div class="col-sm-offset-2 col-sm-10">
	      <div class="checkbox">
	        <label>
	          <input type="checkbox" name="robots" id="checkRobots" checked="checked" value="Yes" onclick="showHide();"> Generate new robots.txt
	        </label>
	      </div>
	    </div>
	  </div>
	  <div class="form-group" id="disallowed">
	    <label for="disallowed" class="col-sm-2 control-label">Disallowed Directories (leave blank if none)</label>
	    <div class="col-sm-10">
	      <input type="text" class="col-sm-2" name="disallowed">
	    </div>
	  </div>
	  <div class="form-group">
	    <div class="col-sm-offset-2 col-sm-10">
	      <button type="submit" class="btn btn-primary">Create sitemap!</button>
	    </div>
	  </div>
	</form>
	<br>


	<?php
	
		/*
		* This gets used a few times, easier to make a call to it that 
		* can do most of the work and keep any error localized to one place.
		*
		* Kinda' cheat to make variable arguments work without array
		*/
		
		function GenURLXML($base_url, $priority, $make=NULL, $model=NULL)
		{
			$output_str = "<url>\n<loc>http://{$base_url}";

			if(isset($make))
				$output_str .= "/{$make}";
			if(isset($model))
				$output_str .= "/{$model}";
			
			$output_str .= "</loc>\n<priority>{$priority}</priority>\n</url>\n\n";
			return $output_str;
		}

		/*
		* Generate the disallowed portion of robots.txt
		*/

		function GenRobots($disallowed)
		{
			$robot_string = "Sitemap: http://{$_POST['baseurl']}/sitemap.xml\nUser-agent:*";

			if (empty($_POST['disallowed'])) 
			{
				$robot_string .= "\nDisallow:";
			}
			else
			{
				$disarray = explode(', ', $_POST['disallowed']);
				foreach ($disarray as $rob_key => $rob_value) 
				{
					$robot_string .= "\nDisallow:/{$rob_value}/";
				}				
			}

			return $robot_string;
		}
	
		/*
		* Do all string fixin' here in once place, kinda url encode
		*/
		
		function FixChars($str)
		{
			if(empty($str))
				return "";
				
			$str = str_replace(' ', '-', $str);		// Special - map space to dash as we can handle this
			$str = str_replace('&', '%26', $str);	// URL Encode
			return str_replace('!', '%21', $str);	// URL Encode
		}
		
		try 
		{
			$db = new PDO('mysql:host=localhost;dbname=newcars;charset=utf8', 'root', 'root');
		} 
		catch(PDOException $ex) 
		{
			echo '<div class="alert alert-danger"><strong>An Error occured!</strong>' . $ex . '</div>';	// Dump exception too!
		}

		//if requested, make a new robots.txt
		
		if (isset($_POST['robots']) && $_POST['robots']=='Yes') 
		{
			$roboto = fopen($_SERVER['DOCUMENT_ROOT'] . '/robots.txt', 'w+');

			if($roboto === false)
			{
				echo '<div class="alert alert-warning"><strong>Can\'t open robots.txt file for writing</strong>, check permissions in ' .  getcwd() . '</div>';
				return;
			}


			if(fwrite($roboto, GenRobots($_POST['disallowed'])) === false)
			{
				echo '<div class="alert alert-danger"><strong>Something is botched up,</strong> failed to fwrite robots.txt to file, terminating.</div>';
				fclose($roboto);
				unlink(realpath($_SERVER['DOCUMENT_ROOT'] . '/robots.txt'));	// delete invalid file
				return;
			}

			fclose($roboto);	// don't forget this			
			echo '<div class="alert alert-success"><strong>New robots.txt was created to</strong> ' . realpath($_SERVER['DOCUMENT_ROOT'] . '/robots.txt') . '</div>';
		}

		if (!empty($_POST['baseurl'])) 
		{
			// make the entry for the base url (www.gtvintage.com or the like)
			
			if(($smap = fopen($_SERVER['DOCUMENT_ROOT'].'sitemap.xml', 'w+')) === false)
			{
				echo '<div class="alert alert-warning"><strong>Can\'t open sitemap.xml file for writing,</strong> check permissions in ' .  getcwd() . '</div>';
				return;
			}
			
			// write XML file headers
			
			$out_str = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
			$out_str .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
			$out_str .= GenURLXML($_POST['baseurl'], '1.0');
			
			fwrite($smap, $out_str);
			
			//Get all needed info from database, doing a crazy join will give make, model results
			
			$sql = $db->query('SELECT fab_id, fab_bez, mod_fabrikat, mod_bez FROM br_fabrikate, br_modelle WHERE fab_status=0 AND mod_status=0 AND fab_id=mod_fabrikat ORDER BY fab_id, mod_bez;'); 
			$results_array = $sql->fetchAll(PDO::FETCH_ASSOC);

			if(($rec_cnt = count($results_array)) <=0)
			{
				echo '<div class="alert alert-warning"><strong>Something is wrong,</strong> no records found for query</div>';
				fclose($smap);
				unlink(realpath($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml'));	// delete invalid file
				return;
			}
			else
				echo 'Processed ' . $rec_cnt . ' Records<br><br>';

			// could also limit count here if too many results...

			$ids = array();
			$out_str = '';
			
			foreach($results_array as $row) 
			{	
				//give convenient names and make URL-encoded

				$id  = $row['fab_id'];
				$fab = FixChars($row['fab_bez']);
				$mod = FixChars($row['mod_bez']);

				// if we have not seen this make before, do just the make URL style
				
				if (!in_array($id, $ids)) 
					$out_str .= GenURLXML($_POST['baseurl'], $_POST['makepriority'], $fab);
				
				// we ALWAYS write this out so just one case for it
				
				$out_str .= GenURLXML($_POST['baseurl'], $_POST['modelpriority'], $fab, $mod);
				$ids[] = $id;	// ever record gets added here
			}
			
			$out_str .="</urlset>";
			
			if(fwrite($smap, $out_str) === false)
			{
				echo '<div class="alert alert-danger"><strong>Something is botched up,</strong> failed to fwrite URL to file, terminating.</div>';
				fclose($smap);
				unlink(realpath($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml'));	// delete invalid file
				return;
			}
			
			echo '<div class="alert alert-success"><strong>New sitemap was created to</strong> ' . realpath($_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml') . '</div>';
			fclose($smap);
		}
	?>
</body>
</html>
