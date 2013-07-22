<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Real-value negative selection algorithm</title>
		<style type="text/css">
			* {
				font-family: Tahoma, sans-serif;
				font-size: 10pt;
			}
			table {
				border-collapse: collapse;
				border: 1px solid #000;
				padding: 5px;
			}
			td, th {
				padding: 5px;
				border: 1px solid #000;
			}
			caption, h1 {
				font-size: 14pt;
				font-weight: bold;
			}
		</style>
	</head>
	<body>
		<p>Settings:</p>
		<ul>
			<li>Maximum self-element variation: <?=MAX_VARIATION?></li>
			<li>Maximum detector overlap: <?=MAX_OVERLAP?></li>
			<li>Dimensions (sensors): <?=DIMENSIONS?></li>
			<li>Maximum detector population: <?=MAX_POPULATION?></li>
			<li>Number of tests: <?=MAX_TESTS?></li>
			<li>Next generation after <?=NEXT_GEN_AFTER?> tests</li>
			<li>Number of top detectors to clone: <?=TOP_TOCLONE?></li>
		</ul>
		<table>
			<caption>Problem space</caption>
			<thead><tr><th>Dimension</th><th>Minimum</th><th>Maximum</th></tr></thead>
			<tbody>
				<? foreach($space as $n=>$item) { ?>
				<tr>
					<td><?=($n+1)?>. <?=$item['desc']?></td>
					<td><?=$item['min']?></td>
					<td><?=$item['max']?></td>
				</tr>
				<? } ?>
			</tbody>
		</table>

		<h1>Self elements</h1>
		<ul>
			<? foreach($self as $item) { ?><li><?=$item?></li><? } ?>
		</ul>

		<table>
			<caption>Detector generations</caption>
			<thead><tr><th>#</th><th>Elements</th></tr></thead>
			<tbody>
				<? foreach($generations as $n=>$item) { ?>
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
								<? foreach($item as $n2=>$item2) { ?>
								<tr>
									<td><?=($n2+1)?></td>
									<td><?=$item2->centre?></td>
									<td><?=$item2->radius?></td>
									<td><?=$item2->overlap?></td>
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

		<table>
			<caption>Tests</caption>
			<thead><tr><th>#</th><th>Antigen</th><th>Result</th><th>Generation #</th></tr></thead>
			<tbody>
				<? foreach($tests as $n=>$item) { ?>
				<tr>
					<td><?=($n+1)?></td>
					<td><?=$item['antigen']?></td>
					<td><?=($item['result'] ? 'Alarm!' : 'OK')?></td>
					<td><?=$item['generation']?></td>
				</tr>
				<? } ?>
			</tbody>
		</table>
	</body>
</html>