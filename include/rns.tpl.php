<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Real-value negative selection algorithm</title>
		<link type="text/css" rel="stylesheet" href="style.css"></link>
	</head>
	<body>
		<h1>Real-value negative selection algorithm</h1>
		
		<div id="menu">
			<ul>
				<li><a href="#settings">Settings</a></li>
				<li><a href="#generations">Detector generations</a></li>
				<li><a href="#tests">Tests</a></li>
				<li><a href="#graphs">Graphs</a></li>
			</ul>
		</div>
		
		<h2 id="settings">Settings:</h2>
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

		<table id="generations">
			<caption>Detector generations</caption>
			<thead><tr><th>#</th><th>Elements</th></tr></thead>
			<tbody>
				<? foreach($generations as $n => $item) { ?>
				<tr>
					<td><?=($n+1)?></td>
					<td>
						<!-- <ol><? foreach($item as $item2) { ?><li><?=$item2?></li><? } ?></ol> -->
						<table>
							<thead>
								<tr>
									<th>#</th><th>Centre</th><th>Radius</th>
									<th>Overlap</th><th>Over max</th><th>Score</th>
								</tr>
							</thead>
							<tbody>
								<? foreach($item as $n2 => $item2) { ?>
								<tr>
									<td><?=($n2 + 1)?></td>
									<td><?=$item2->centre->formatted()?></td>
									<td><?=number_format($item2->radius, 3)?></td>
									<td><?=number_format($item2->overlap, 3)?></td>
									<td><?=($item2->overlap > MAX_OVERLAP ? 'yes' : 'no')?></td>
									<td><?=$item2->score?></td>
								</tr>
								<? } ?>
							</tbody>
						</table>
					</td>
				</tr>
				<? } ?>
			</tbody>
		</table>

		<table id="tests">
			<caption>Tests</caption>
			<thead><tr>
				<th>#</th><th>Antigen</th><th>Result</th><th>Generation #</th><th>Detector #</th>
			</tr></thead>
			<tbody>
				<? foreach($tests as $n => $item) { ?>
				<tr>
					<td><?=($n + 1)?></td>
					<td><?=$item['antigen']?></td>
					<td><?=($item['result'] ? 'Alarm!' : 'OK')?></td>
					<td><?=$item['generation']?></td>
					<td><?=$item['detector_n']?></td>
				</tr>
				<? } ?>
			</tbody>
		</table>
		
		<p>Run time: <?=$runTime?> s.</p>
		
		<h2 id="graphs">Graphs</h2>
		<p>Legend:</p>
		<ul>
			<li>Blue circle: detector</li>
			<li>Green dot: self</li>
			<li>Red dot: antigen</li>
			<li>Red dot in green circle: non-harmful antigen</li>
		</ul>
		<? for($i = 0; $i < DIMENSIONS - 1; $i++) { ?>
			<p><br />x: <?=$space[$i]['desc']?>; y: <?=$space[$i + 1]['desc']?></p>
			<img src="graph.php?<?=http_build_query(array('dimensions' => array($i, $i + 1)))?>" />
		<? } ?>
	</body>
</html>