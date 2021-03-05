<?php
/**
 * img2rubiks
 *
 * This script takes an image and spits out the necessary framework to make
 * uploaded image with rubiks cubes, specifically the 3x3 kind
 */

/* Detect file upload and handle */
$rubiks_json = null;
if (false)
{

}
else
{
	/* Detect json data and handle */
	$rubiks_json = array_key_exists('json_raw', $_POST) ? $_POST['json_raw'] : null;
}

?><!doctype html>
<html>
  <head>
    <title>img2rubiks</title>
  </head>
  <!-- I get paid to code. If I got paid to draft pretty layouts you wouldn't scoff at how bad this page looks -->
  <body>
    <div>Source code at <a href="https://github.com/mjheick/img2rubiks" target="_blank">github/mjheick/img2rubiks</a></div>
<?php
if (!is_null($rubiks_json))
{
	?>
    <div><hr /></div>
<?php
}
?>
    <div><hr /></div>
    <div>
      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
      <div>json: <textarea name="json_raw"><?php
if (!is_null($rubiks_json))
{
	echo $rubiks_json;
}
?></textarea></div>
      <div>image file: <input type="file" name="file_image" /></div>
      <div>restraint in rubiks blocks: <input type="text" name="restraint" value="30" placeholder="30" /><select name="direction"><option>wide</option><option>high</option></select></div>
      <div><input type="submit" name="submit-btm" value="Submit" /></div>
      </form>
    </div>
    <div><hr /></div>
  </body>
</html>
