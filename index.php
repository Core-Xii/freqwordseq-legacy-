<?php
if (@isset($_GET['source']))
	{
	highlight_string(file_get_contents('index.php'));
	exit();
	}
?>
Core Xii's frequent word sequence finder<br />
<br />
<?php
if ($_GET['file'])
	{
	if (preg_match("(\\W)", $_GET['file'])) die('Directory traversal not permitted.');
	if (!$file = @file_get_contents($_GET['file'].'.txt')) die('Nonexistant or empty file.');
	if ($_GET['minfreq'])
		{
		if (!@preg_match("(\A[1-9][0-9]*\Z)", $_GET['minfreq'])) die('Invalid minimum frequency. Must be an integer >= 1.');
		else $minfreq = (int) $_GET['minfreq'];
		}
	else $minfreq = 3;
	if ($_GET['minchars'])
		{
		if (!@preg_match("(\A[1-9][0-9]*\Z)", $_GET['minchars'])) die('Invalid minimum character count. Must be an integer >= 1.');
		else $minchars = (int) $_GET['minchars'];
		}
	else $minchars = 3;
	if ($_GET['minseq'])
		{
		if (!@preg_match("(\A[1-9][0-9]*\Z)", $_GET['minseq'])) die('Invalid minimum sequence length. Must be an integer >= 1.');
		else $minseq = (int) $_GET['minseq'];
		}
	else $minseq = 1;
	if ($_GET['maxseq'])
		{
		if (!@preg_match("(\A[1-9][0-9]*\Z)", $_GET['maxseq'])) die('Invalid maximum sequence length. Must be an integer >= 1.');
		else if ((int) $_GET['maxseq'] <= $minseq) die('Invalid maximum sequence length. Must be more than minimum!');
		else $maxseq = (int) $_GET['maxseq'];
		}
	else $maxseq = 3;
	
	$comm = array_unique(preg_split("(\b\W+\b)", file_get_contents("common_words.txt")));
	$source = preg_split("(\b\W+\b)", $file);
	$ignore = array(
		0 => 'post',
		'topic',
		'http',
		'www',
		'com',
		);
	$num_ignore = count($ignore);
	
	foreach ($source as $w)
		{
		if (strlen($w) >= $minchars)
		if (!preg_match("/\A\d+\Z/", $i))
		if (!preg_match("/\A(\w)\1+\Z/", $i))
		if (!in_array($w, $comm))
		if (!in_array($w, $ignore))
			{
			$words[] = $w;
			}
		}
	$num_words = count($words);
	
	?>Searching for sequences between <b><?= $minseq ?></b> and <b><?= $maxseq ?></b> words long, among <b><a href='<?= $_GET['file'] ?>.txt'><?= number_format($num_words) ?></b> words</a> of <b><?= $minchars ?></b> character<?= ($minchars > 1 ? 's' : '') ?> or more in length, that have a frequency of <b><?= $minfreq ?></b> or more.<br />Filtering out <b><a href='common_words.txt'><?= number_format(count($comm)) ?></b> common words</a> and the following word<?= ($num_ignore > 1 ? 's' : '') ?>: <?php
	echo $ignore[0];
	if ($num_ignore > 1)
		{
		for ($i = 1; $i < $num_ignore; $i ++)
			{
			if ($i == $num_ignore - 1)
				{
				echo ' and ' . $ignore[$i];
				break;
				}
			echo ', ' . $ignore[$i];
			}
		}
	?>.<br /><?php
	if ($num_words > 10000)
		{
		?><div id='hide' name='hide'>(Be patient, this might take a while)</div><?php
		}
	
	$start = microtime(true);
	$str = strtolower(implode(' ', $words));
	$seqs = array();
	for ($i = 0; $i < $num_words; $i ++) // for each word
		{
		for ($j = $maxseq; $j >= $minseq; $j --) // seq word counts
			{
			$try = $words[$i];
			if ($j > 1)
				{
				for ($k = 1; $k < $j; $k ++) // fetch words to try
					{
					$try .= ' ' . $words[$i + $k];
					}
				}
			$matches = substr_count($str, $try);
			if ($matches >= $minfreq)
				{
				$seqs[$try] = $matches;
				break;
				}
			}
		set_time_limit(1);
		}
	set_time_limit(30);
	$finish = microtime(true);
	
	?><script type='text/javascript'>document.getElementById('hide').innerHTML = '';</script>Found <?= number_format(count($seqs)) ?> sequences. Search took ~<b><?= round($finish - $start, 3) ?></b> seconds.<br /><ol><?php
	
	arsort($seqs);
	foreach ($seqs as $s => $n)
		{
		?><li><?= $s ?> (<?= $n ?>)</li><?php
		}
	
	?></ol><?php
	}
else
	{
	?>Usage: ?file=&lt;file&gt;[&amp;minfreq=3][&amp;minchars=3][&amp;minseq=1][&amp;maxseq=3]<br />The following files are available:<ul><?php
	$dir = opendir('/home/sg315/public_html/corexii/freqwordseq/');
	while (($f = readdir($dir)) !== false)
		{
		if (basename($f) == '.' || basename($f) == '..' || substr(basename($f), -4) != '.txt' || basename($f) == 'common_words.txt') continue;
		?><li><?= substr(basename($f), 0, -4) ?></li><?php
		}
	closedir($dir);
	?></ul>Example: <a href='http://corexii.com/freqwordseq/?file=forumtext&amp;minfreq=5&amp;minseq=2&amp;maxseq=4'>?file=forumtext&amp;minfreq=5&amp;minseq=2&amp;maxseq=4</a><br /><br /><?php
	}
?>
<a href='?source'>Source</a>