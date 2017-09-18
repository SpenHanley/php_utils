<?php
error_reporting(E_ALL);
// Read the information from the /proc file
$cpuInfo = explode("\n", file_get_contents('/proc/cpuinfo'));
$actual = "";


// Iterate through all the values stored in the /proc/cpuinfo file
foreach ($cpuInfo as $item)
{
	$block = "";

	// Split the item on ':' character
	$subArr = explode(':', $item);

	// Remove all tabs
	$subArr[0] = preg_replace('/\t/', '',$subArr[0]);

	// Replace spaces with underscores
	$subArr[0] = str_replace(' ', '_', $subArr[0]);

	// Remove leading space from values
	$subArr[1] = ltrim($subArr[1]);
	
	// No point in working with an element if it is empty
	if (empty($subArr[0]))
	{
		continue;
	  // Check for processor value as this denotes the beginning of a processor core
	} elseif(in_array('processor', $subArr, FALSE)) {
		// Create the processor tag and prepend an opening core tag
		$block = "<core><$subArr[0]>$subArr[1]</$subArr[0]>";
	  // Check for power management value as this denotes the end of a processor core
	} elseif (in_array('power_management', $subArr))
	{
		$val = empty($subArr[1]) ? 'null' : $subArr[1];
		// Create the power management tag and append the closing core tag
		$block = "<$subArr[0]>$val</$subArr[0]></core>";
	  // Check for flags so that they can be split into their individual flag
	} elseif (in_array('flags', $subArr, FALSE)) 
	{
		$block = "<flags>";
		// Break the flags up based on spaces
		foreach (explode(' ', $subArr[1]) as $line)
		{
			// Ensure that a flag actually exists
			if (!empty($line))
				// Create a flag object for each flag
				$block .= "<flag>".$line."</flag>";
		}
		$block .= "</flags>";
	} else
	{
		// Create a standard tag
		$block = "<$subArr[0]>$subArr[1]</$subArr[0]>";
	}
	// Append the tag to the main body of tags
	$actual .= $block;
}
?>
<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>CPU Info PHP</title>
		<meta charset='utf-8'>
	</head>
	<body>
		<?=$actual;?>
		<script>
			iterateJSON();
			function iterateJSON()
			{
				var parser = new DOMParser();
				// Pass the data to javascript for us to iterate through
				var data = parser.parseFromString('<?=$actual; ?>', 'text/xml')
				var processor = data.getElementsByTagName('processor')
				console.log(processor.length)
			}
		</script>
	</body>
</html>
