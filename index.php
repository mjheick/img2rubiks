<?php
/**
 * img2rubiks
 *
 * This script takes an image and spits out the necessary framework to make
 * uploaded image with rubiks cubes, specifically the 3x3 kind
 */

/* RGB colors of the squares to compare against */
$rubiks_colors = [
	[0, 0, 0], /* white */
	[255, 255, 0], /* yellow */
	[0, 255, 0], /* green */
	[0, 0, 255], /* blue */
	[255, 0, 0], /* red */
	[255, 165, 0], /* orange */
];

/* Our "format" */
$rubiks_json = [
	"width" => 0, /* in squares, not cubes */
	"height" => 0, /* in squares, not cubes */
	"art" => [ /* A demo 3x3 of rgb colors */
		[ [0,0,0], [0,0,0], [0,0,0] ],
		[ [0,0,0], [0,0,0], [0,0,0] ],
		[ [0,0,0], [0,0,0], [0,0,0] ],
	],
];

/* Detect file upload and handle */
$rubiks_json = null;
if (array_key_exists('file_image', $_FILES))
{
	/* Verify we actually have an image with basic stuff */
	$imgdata = getimagesize($_FILES['file_image']['tmp_name']);
	if ($imgdata !== false)
	{
		$i = imagecreatefromstring(file_get_contents($_FILES['file_image']['tmp_name']));
		if ($i === false)
		{
			/* We have an image that works with getimagesize but we can't load. fail this out */
			die();
		}
		$restraint_amt = array_key_exists('restraint', $_POST) ? $_POST['restraint'] : 30;
		$restraint_direction = array_key_exists('direction', $_POST) ? $_POST['direction'] : 'wide';
		/* We need to calculate resizing this image so we can better work with it */
		$restraint_amt = $restraint_amt * 3; /* 3 blocks per rubiks cube * restraint = "pixels" */
		$image_width = $imgdata[0];
		$image_height = $imgdata[1];
		$resize_width = 0;
		$resize_height = 0;
		if ($restraint_direction == 'wide')
		{
			$resize_width = $restraint_amt;
			$resize_height = intval(($image_height / $image_width) * $resize_width);
		}
		if ($restraint_direction == 'high')
		{
			$resize_height = $restraint_amt;
			$resize_width = intval(($image_width / $image_height) * $resize_height);
		}
		if (($resize_height == 0) || ($resize_width == 0))
		{
			/* We have some parameter we don't like, or some math doesn't work out. fail this out */
			die();
		}
		$r = imagecreatetruecolor($resize_width, $resize_height);
		if ($r === false)
		{
			/* We have some memory problem with creating our destination image. fail this out */
			die();
		}
		if (imagecopyresized($r, $i, 0, 0, 0, 0, $resize_width, $resize_height, $image_width, $resize_height) === false)
		{
			/* Shrinking operation couldn't be done. fail this out */
			die();
		}
	}
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
      <div>restraint in rubiks cubes: <input type="text" name="restraint" value="30" placeholder="30" /><select name="direction"><option>wide</option><option>high</option></select></div>
      <div><input type="submit" name="submit-btm" value="Submit" /></div>
      </form>
    </div>
    <div><hr /></div>
  </body>
</html>
