<?php

require_once '../include/config.php';

?>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Real-value negative selection algorithm</title>
		<link type="text/css" rel="stylesheet" href="style.css" />
	</head>
	<body>
		<h1>Real-value negative selection algorithm</h1>
		<p>Settings:</p>
		<ul>
			<li>Maximum self-element variation: <?=MAX_VARIATION?></li>
			<li>Maximum detector overlap: <?=MAX_OVERLAP?></li>
			<li>Dimensions (sensors): <?=DIMENSIONS?></li>
			<li>Maximum detector population: <?=MAX_POPULATION?></li>
			<? if(MAX_RADIUS > 0) { ?><li>Maximum detector radius: <?=MAX_RADIUS?></li><? } ?>
			<li>Number of tests: <?=MAX_TESTS?></li>
			<li>Next generation after <?=NEXT_GEN_AFTER?> tests</li>
			<li>Number of top detectors to clone: <?=TOP_TOCLONE?></li>
			<li>Detector sorting field: <?=DETECTOR_SORT_FIELD?></li>
		</ul>
		<table>
			<caption>Problem space</caption>
			<thead><tr><th>Dimension</th><th>Minimum</th><th>Maximum</th></tr></thead>
			<tbody>
				<? foreach($space as $n => $item) { ?>
				<tr>
					<td><?=($n + 1)?>. <?=$item['desc']?></td>
					<td><?=$item['min']?></td>
					<td><?=$item['max']?></td>
				</tr>
				<? } ?>
			</tbody>
		</table>

		<h2>Self elements</h2>
		<ul>
			<? foreach($self as $item) { ?><li><?=$item?></li><? } ?>
		</ul>
		
		<p><a href="rns.php">Launch with these settings</a></p>
	</body>
</html>