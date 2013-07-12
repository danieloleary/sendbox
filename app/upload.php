<!-- Note, the API key and authentication are not present in this version. -->

<?php
/** Constants **/
$APIKEY = '55555559w8t5gd2d7b73zhqh1gn';
$API_ENDPOINT = 'https://api.box.com/2.0/';

/** Form parameters **/
$to_email = $_REQUEST['to_email'];
$from_email = $_REQUEST['from_email'];
$message = $_REQUEST['message'];
$filename = $_FILES['file']['name'];
$file_contents = file_get_contents($_FILES['file']['tmp_name']);

/** 1. Get instant mode token and folder **/
$auth_header = "Authorization: BoxAuth api_key=$APIKEY";
$result = make_req('POST',$API_ENDPOINT.'tokens',$auth_header,"{\"email\":\"$from_email\"}");

$token = $result['token'];
$folder_id = $result['item']['id'];
$auth_header .= "&auth_token=$token";

/** 2. Upload file **/
define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
$header = "$auth_header\r\nContent-Type: multipart/form-data; boundary=".MULTIPART_BOUNDARY;
$content =  "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"".'uploaded_file'."\"; filename=\"".$filename."\"\r\n".
            "Content-Type: application/zip\r\n\r\n".
            $file_contents."\r\n".
            "--".MULTIPART_BOUNDARY."\r\n".
            "Content-Disposition: form-data; name=\"folder_id\"\r\n\r\n".
            "$folder_id\r\n".
            "--".MULTIPART_BOUNDARY."--\r\n";
$result = make_req('POST',$API_ENDPOINT.'files/content',$header,$content);

$file_id = $result['entries'][0]['id'];

/** 3. Get share link to file **/
$result = make_req('PUT',$API_ENDPOINT."files/$file_id",$auth_header,'{"shared_link": {"access": "open"}}');

$link = $result['shared_link']['url'];

/** 4. Send link in an email **/
$message = wordwrap($message, 70, "\r\n");
mail($to_email, "Someone sent you a file with Box", $message . "\r\n\r\n$link", "From: $from_email");


function make_req($method, $endpoint, $header, $content) {
	$context = stream_context_create(array(
		'http' => array(
		'method' => $method,
		'header' => $header,
		'content' => $content
	)
	));

	$response = file_get_contents($endpoint, false, $context);
	return json_decode($response, true);
}

echo "<p>Email sent!</p><p>The link to your file is:<br/><a href=\"$link\" target=\"_blank\">$link</a></p>"

?>