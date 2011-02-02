<h1>Pulling a PDF from Box.net and Proofing with Mimeo</h1>
<p>This is a sample / training document on how to pull a PDF file from Box.net then Proof with the Mimeo Proof Web Service.</p>
<p>To use this you will need:</p>
<ol>
<li>Mimeo Connect API Account</li>
<li>Box.net Account</li>
</ol>
<?php
$Level = "";

// Mimeo REST Client
require_once "libraries/mimeo-rest-client.php";

// Utility for Converting XML String to Array
require_once "libraries/xml_string_to_array.php";

// This should be your Mimeo Sandbox Account Info
$root_url = "connect.sandbox.mimeo.com/2010/09/";
//$user_name = "[mimeo user]";
//$password = "[mimeo password]";

// This should be your Mimeo Sandbox Account Info
//$root_url = "connect.mimeo.com/2010/09/";
//$user_name = "[mimeo user]";
//$password = "[mimeo password]";

// Create a REST Client Object
$rest = new MimeoRESTclient($root_url,$user_name,$password);

//Replace with your Document
$BoxNet_Document_URL = "http://www.box.net/download/external/f_498630454/0/New_FacStaff2010.pdf?embed_hash=jkknnv5ohjq42ld";

//We have a URL.  What do we know about it?
$Source_URL_Array = parse_url($BoxNet_Document_URL);

// Lets break up the incoming URL
$Host = $Source_URL_Array['host'];
//echo "Host: " . $Host . "<br />";
$Path = $Source_URL_Array['path'];
//echo "Path: " . $Path . "<br />";
$File_Array = explode("/",$Path);
$File_Name = $File_Array[sizeof($File_Array)-1];	
//echo "File Name: " . $File_Name . "<br />";

$Temp_Save_File = 'files/' . $File_Name;
file_get_contents($BoxNet_Document_URL);
file_put_contents($Temp_Save_File, file_get_contents($BoxNet_Document_URL));

$Temp_Save_URL = 'http://nimbus2.laneworks.net/files/' . $File_Name;
$BoxNet_Document_URL = $Temp_Save_URL;
?>
<p>My goal is to pull a public PDF from Box.net, I have chosen: <a href="<?php echo $BoxNet_Document_URL;?>" target="_blank"><?php echo $BoxNet_Document_URL;?></a></strong></p>
<?php

//We have a URL.  What do we know about it?
$Source_URL_Array = parse_url($BoxNet_Document_URL);

// Lets break up the incoming URL
$Host = $Source_URL_Array['host'];
//echo "Host: " . $Host . "<br />";
$Path = $Source_URL_Array['path'];
//echo "Path: " . $Path . "<br />";
$File_Array = explode("/",$Path);
$File_Name = $File_Array[sizeof($File_Array)-1];	
//echo "File Name: " . $File_Name . "<br />";	
$File_Name2 = $File_Array[sizeof($File_Array)-2];	
//echo "File Name 2: " . $File_Name2 . "<br />";	

$url = "ProofService/PrepareProof?documentSource=" . $BoxNet_Document_URL;
echo "URL: " . $url . "<br />";
$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();

$XMLArray = xmlstr_to_array($output);

?>
<p>Using the Box.net PDF Download URL we pass to Mimeo Connect RESTful Cloud Print Proof Service and get the following XML:</p>
<textarea rows="15" cols="55"><?php echo $output;?></textarea><br />
<p>We pull the following values from the Mimeo return XML:</strong></p>
<?php

$Mimeo_Product_ID = $XMLArray['ProductId'];
echo "Mimeo Product ID: " . $Mimeo_Product_ID . "<br />";

$Mimeo_Name = $XMLArray['Name'];
echo "Mimeo Name: " . $Mimeo_Name . "<br />";

$Mimeo_Description = $XMLArray['Description'];
echo "Mimeo Description: " . $Mimeo_Description . "<br />";

$Mimeo_IsRipped = $XMLArray['IsRipped'];
echo "Mimeo IsRipped: " . $Mimeo_IsRipped . "<br />";

$Mimeo_PageCount = $XMLArray['PageCount'];
echo "Mimeo Page Count: " . $Mimeo_PageCount . "<br />";

$Mimeo_HasErrors = $XMLArray['HasErrors'];
echo "Mimeo HasErrors: " . $Mimeo_HasErrors . "<br />";

$Mimeo_MaxPageHeight = $XMLArray['MaxPageHeight'];
echo "Mimeo MaxPageHeight: " . $Mimeo_MaxPageHeight . "<br />";

$Mimeo_MaxPageWidth = $XMLArray['MaxPageWidth'];
echo "Mimeo MaxPageWidth: " . $Mimeo_MaxPageWidth . "<br />";

$Mimeo_IsBroken = $XMLArray['IsBroken'];
echo "Mimeo IsBroken: " . $Mimeo_IsBroken . "<br />";
?>
<p>Then we send that ID to Mimeo ProofService/Proof/</strong></p>
<?php
//Send Request to Mimeo Proof Service to Get Document Proof Information
$url = "ProofService/Proof/" . $Mimeo_Product_ID;
$rest->createRequest($url,"GET","");
$rest->sendRequest();
$output = $rest->getResponseBody();
?>
<textarea rows="15" cols="55"><?php echo $output;?></textarea><br />
<?php
$XMLArray = xmlstr_to_array($output);
?>

<p>With the Mimeo Proof Document ID we make another call to Mimeo RESTful Cloud Print API Proof Serice to get document proofed images.</p>
<?php 
for ($Page = 0; $Page < $Mimeo_PageCount; $Page++)
//for ($Page = 0; $Page < 2; $Page++)
	{
	?>
	<?php
	$Small_Image_URL = $XMLArray['ProofPages']['ProofPage'][$Page]['SmallImage'];

	//This is temporary fix.  I was getting image paths back without version in URL.
	$Small_Image_URL = str_replace("http://connect.sandbox.mimeo.com/ProofService/","http://connect.sandbox.mimeo.com/2010/09/ProofService/",$Small_Image_URL);
	$Small_Image_URL = str_replace("http://connect.mimeo.com/ProofService/","https://connect.mimeo.com/2010/09/ProofService/",$Small_Image_URL);

	?><img src="<?php echo $Small_Image_URL;?>" width="100" /><?php
	
	$Large_Image_URL = $XMLArray['ProofPages']['ProofPage'][$Page]['LargeImage'];
	
	//This is temporary fix.  I was getting image paths back without version in URL.  We are working on
	$Large_Image_URL = str_replace("http://connect.sandbox.mimeo.com/ProofService/","http://connect.sandbox.mimeo.com/2010/09/ProofService/",$Large_Image_URL);
	$Large_Image_URL = str_replace("http://connect.mimeo.com/ProofService/","https://connect.mimeo.com/2010/09/ProofService/",$Large_Image_URL);


	?><img src="<?php echo $Large_Image_URL;?>" width="250" /><br /><br />
	<?php
	}
?>
<p>Now these small and large PDF Proof Images can be used in a Flibook, Image Slideshow or other preview or full view format.</p>
<p>The goal of this is to show how to pull PDF from Box.net, Proof with Mimeo Cloud Print API and display to user prior to ordering print copies of a document.</p>

