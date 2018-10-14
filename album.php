<html>
<head>
    <link rel="stylesheet" type="text/css" href="bootstrap.css">
    <script src="jquery.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<!-- 
Name: Vinayak Ravindra Tare
Student id: 1001453869
-->

<?php
/** 
 * DropPHP sample
 *
 * http://fabi.me/en/php-projects/dropphp-dropbox-api-client/
 *
 * @author     Fabian Schlieper <fabian@fabi.me>
 * @copyright  Fabian Schlieper 2012
 * @version    1.1
 * @license    See license.txt
 *
 */


// if there are many files in your Dropbox it can take some time, so disable the max. execution time
set_time_limit(0);

require_once("DropboxClient.php");

// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
$dropbox = new DropboxClient(array(
	'app_key' => "pgpe92dzcwkq0q0",      // Put your Dropbox API key here
	'app_secret' => "uicl3gwhfzhh5n5",   // Put your Dropbox API secret here
	'app_full_access' => false,
),'en');


// first try to load existing access token
$access_token = load_token("access");
if(!empty($access_token)) {
	$dropbox->SetAccessToken($access_token);
	//echo "loaded access token:";
	//print_r($access_token);
}
elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
{
	// then load our previosly created request token
	$request_token = load_token($_GET['oauth_token']);
	if(empty($request_token)) die('Request token not found!');
	
	// get & store access token, the request token is not needed anymore
	$access_token = $dropbox->GetAccessToken($request_token);	
	store_token($access_token, "access");
	delete_token($_GET['oauth_token']);
}

// checks if access token is required
if(!$dropbox->IsAuthorized())
{
	// redirect user to dropbox auth page
	$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
	$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
	$request_token = $dropbox->GetRequestToken();
	store_token($request_token, $request_token['t']);
	die("Authentication required. <a href='$auth_url'>Click here.</a>");
}

//echo "<pre>";
//echo "<b>Account:</b>\r\n";
//print_r($dropbox->GetAccountInfo());

$files = $dropbox->GetFiles("",false);

if(!empty($files)) {
   $dropbox->UploadFile("leonidas.jpg");
   $files = $dropbox->GetFiles("",false);
 }

if(!empty($files)) {
        $file1 = reset($files);
	$test_file = "test_download_".basename($file1->path);
	
//	echo "<img src='".$dropbox->GetLink($file1,false)."'/></br>";
	//echo "\r\n\r\n<b>Meta data of <a href='".$dropbox->GetLink($file1)."'>$file1->path</a>:</b>\r\n";
	//print_r($dropbox->GetMetadata($file1->path));
	
	//echo "$test_file</br>";
	//echo "\r\n\r\n<b>Downloading $file1->path:</b>\r\n";
	//print_r($dropbox->DownloadFile($file1, $test_file));
		
	//echo "\r\n\r\n<b>Uploading $test_file:</b>\r\n";
	//print_r($dropbox->UploadFile($test_file));
	//echo "\r\n done!";	
	
	//echo "\r\n\r\n<b>Revisions of $test_file:</b>\r\n";	
	//print_r($dropbox->GetRevisions($test_file));
}
	
//echo "\r\n\r\n<b>Searching for JPG files:</b>\r\n";	
$jpg_files = $dropbox->Search("/", ".jpg", 5);
if(empty($jpg_files))
	echo "Nothing found.";
else {
//	print_r($jpg_files);
	$jpg_file = reset($jpg_files);

	//echo "\r\n\r\n<b>Thumbnail of $jpg_file->path:</b>\r\n";	
	$img_data = base64_encode($dropbox->GetThumbnail($jpg_file->path));
	//echo "<img src=\"data:image/jpeg;base64,$img_data\" alt=\"Generating PDF thumbnail failed!\" style=\"border: 1px solid black;\" />";
}
function store_token($token, $name)
{
	if(!file_put_contents("tokens/$name.token", serialize($token)))
		die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
}
function load_token($name)
{
	if(!file_exists("tokens/$name.token")) return null;
	return @unserialize(@file_get_contents("tokens/$name.token"));
}
function delete_token($name)
{
	@unlink("tokens/$name.token");
}

function enable_implicit_flush()
{
	@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
	ob_implicit_flush(1);
	echo "<!-- ".str_repeat(' ', 2000)." -->";
}

if(isset($_GET['del'])){
	//echo"<br>Hello!!";
	$files = $dropbox->GetFiles("",false);
	//print_r($files);
	//echo "Hi";
	foreach($files as $key=>$value){
		
		if((string)$_GET['del'] == (string)$value->path){
			$dropbox->Delete($value);
		}
	}
}


if(isset($_GET['dd1'])){
	//echo"<br>Hello!!";
	$files = $dropbox->GetFiles("",false);
	//print_r($files);
	//echo "Hi";
	foreach($files as $key=>$value){
		$dest_file = "test_download_".basename($value->path);
		if((string)$_GET['dd1'] == (string)$value->path){
			$dropbox->DownloadFile($value,$dest_file);
		}
	}
}

echo"
<h1 align = 'center'>Dropbox Cloud Storage for Photos</h1> 
<form class='form-inline' align = 'center' action = 'album.php' method = 'POST' enctype='multipart/form-data'>
    <div class='form-group'>
			
			<input type = 'file' name ='image' class='form-control' id='img'>
			<input type='Submit' name='Submit' class='form-control' id='submit'>
    </div>
</form>";

if(isset($_FILES['image']['tmp_name'])){
	
	$file_name1 = $_FILES['image']['tmp_name'];
	$og_name = $_FILES['image']['name'];
	//print_r($file_name1);
	$dropbox->UploadFile($file_name1,$og_name);
	$file = $dropbox->GetFiles("",false);
	//print_r($file)."<br>";
}
echo"<br><br> 
<table class='table table-striped table-bordered table-hover table-condensed' border='5' align='center'>
<caption> List of Images </caption><br>
<tr>
<th> Image Name </th>
<th> View Image </th>
<th> Download  </th>
<th> Action </th>";

$getImg = array();
$file = $dropbox->GetFiles("",false);
foreach($file as $key=>$value){
	$getImg = $dropbox->GetLink($value,false);
	echo "<tr>
	<td>".$key."</a></td>";//Name
	echo "<td>"?>
	<a class="btn-lg" href = "#" data-toggle="modal" data-target="#myModal" onclick = "showImg('<?php echo $getImg; ?>')"> Click here! </a>
	<?php
	echo"</td><td>";
	?>
        <form class="form-inline" method = 'get' align='center' action='album.php'>
	<input type= 'submit'  value='Download' >
	<input type= 'hidden' value = '<?php echo $value->path; ?>' name = 'dd1'>
	</form>
	<?php echo"</td>
	<td>";
	?>
	<form method = 'get' align='center' action='album.php'>
	<input type= 'submit'  value='Delete' >
	<input type= 'hidden' value = '<?php echo $value->path; ?>' name = 'del'>
	</form>
<?php	
"</td>";
}
echo "</tr></table><br><br></form>";

?>


<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-body" align='center'>
            <img id = "photo" class="img-responsive">
        </div>
    </div>
  </div>
</div>


<script type="text/javascript">

function showImg(id){
	
  document.getElementById("photo").src = id;
}
</script>
</body>
</html>