<?php
/**
 * img2rubiks
 *
 * This script takes an image and spits out the necessary framework to make
 * uploaded image with rubiks cubes, specifically the 3x3 kind
 */

/* RGB colors of the squares to compare against */
$rubiks_colors = [
	[255, 255, 255], /* white */
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
	"png" => null, /* a base64-encoded image */
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
			die('We have an image that works with getimagesize but we can\'t load. fail this out');
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
			die('We have some parameter we don\'t like, or some math doesn\'t work out. fail this out');
		}
		$r = imagecreatetruecolor($resize_width, $resize_height);
		if ($r === false)
		{
			die('We have some memory problem with creating our destination image. fail this out');
		}
		if (imagecopyresized($r, $i, 0, 0, 0, 0, $resize_width, $resize_height, $image_width, $image_height) === false)
		{
			die('Shrinking operation couldn\'t be done. fail this out');
		}
		/* loop through the new image, find the closest rgb and store it */
		$rubiks_data = [];
		for ($py = 0; $py < $resize_height; $py++)
		{
			$rubiks_row = [];
			for ($px = 0; $px < $resize_width; $px++)
			{
				$pixel = imagecolorat($r, $px, $py);
				if ($pixel === false)
				{
					die('cannot get pixel at position ('.$px.','.$py.')');
				}
				$color_r = ($pixel >> 16) & 0xFF;
				$color_g = ($pixel >> 8) & 0xFF;
				$color_b = $pixel & 0xFF;
				/* Figure out which color we're close to */
				$closest_rgb = null;
				$closest_rgb_distance = null;
				foreach ($rubiks_colors as $clr)
				{
					$clr_dist = sqrt(($clr[0] - $color_r) * ($clr[0] - $color_r) + ($clr[1] - $color_g) * ($clr[1] - $color_g) + ($clr[2] - $color_b) * ($clr[2] - $color_b));
					if (is_null($closest_rgb_distance))
					{
						$closest_rgb_distance = $clr_dist;
						$closest_rgb = $clr;
					}
					else
					{
						if ($clr_dist < $closest_rgb_distance)
						{
							$closest_rgb_distance = $clr_dist;
							$closest_rgb = $clr;
						}
					}
				}
				$rubiks_row[] = $closest_rgb;
			}
			$rubiks_data[] = $rubiks_row;
		}
		ob_start();
		imagepng($r, null, 9);
		$png = ob_get_contents();
		ob_end_clean();
		$rubiks_array = [
			"width" => $resize_width, /* in squares, not cubes */
			"height" => $resize_height, /* in squares, not cubes */
			"art" => $rubiks_data,
			"png" => 'data:image/png;base64,' . base64_encode($png),
		];
		$rubiks_json = json_encode($rubiks_array);
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
	<style>
.col {
  margin: 1px;
  min-width: 16px;
  min-height: 16px;
  float: left;
}
	</style>
	<script>
function showMeARubiksArt()
{
	let rubiks = JSON.parse(document.getElementById('json_raw').value);
	let html_guide = '', x = 0, y = 0, rubiks_row, rgb;
	html_guide += '<div>';
	for (y = 0; y < rubiks.height; y++)
	{
		html_guide += '<div class="row">';
		rubiks_row = rubiks.art[y];
		for (x = 0; x < rubiks.width; x++)
		{
			rgb = rubiks_row[x];
			html_guide += '<div class="col" style="background-color: rgb(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ');">';
			html_guide += '</div>';
		}
		html_guide += '<div style="clear: both;"></div>';
		html_guide += '</div>';
	}
	html_guide += '</div>';
	document.getElementById('rubiks_grid').innerHTML = html_guide;
	document.getElementById('rubiks_img').src = rubiks.png;
}
	</script>
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
      <div>json: <textarea id="json_raw" name="json_raw"><?php
if (!is_null($rubiks_json))
{
	echo $rubiks_json;
}
?></textarea></div>
      <div>image file: <input type="file" name="file_image" /></div>
      <div>restraint in rubiks cubes: <input type="text" name="restraint" value="20" placeholder="20" /><select name="direction"><option>wide</option><option>high</option></select></div>
      <div><input type="submit" name="submit-btm" value="Submit" /></div>
      </form>
    </div>
    <div><hr /></div>
	<?php
if (!is_null($rubiks_json))
	echo '<div id="rubiks_preview"><img src="" id="rubiks_img" /></div>';
	echo '<div id="rubiks_grid"></div>';
?>
	</body>
<?php
if (!is_null($rubiks_json))
	echo '<script>showMeARubiksArt();</script>';
?>
</html>