<?php
#if (!defined("_KATE_MAIN")) die("You have no access to this file");
require_once("config/config.php");
require_once($global_path . "include/mysql.php");

function ffstop($xid)
{
	global $db, $uploads_path;
	//Stop
	//Getting PID of ffmpeg processing XID
	exec("ps axwww | grep $xid | grep -v grep | head -n 1 | awk '{print $1}'", $pid);
	exec("kill $pid[0]");
	sleep(1);
	// Now let's remove the playing dir
	rrmdir($uploads_path . $xid);
	// Updating table radios
	$db->sql_query("UPDATE radios SET pid=NULL,lastrequest=NULL WHERE xid='$xid'");
}

function ffstart($xid)
{
	global $db, $ffbin, $domain, $uploads_path;
	$input = [
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.3.1; WT19M-FI Build/JLS36I)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.1.1; BroadSign Xpress 1.0.14 B- (720) Build/JRO03H)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.4.4; XT1080 Build/SU6-7.3)"',
		'User-Agent: "Dalvik/1.4.0 (Linux; U; Android 2.3.6; Lenovo A269i Build/GRK39F)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.1.2; ST26i Build/11.2.A.0.31)AWEIY511-U30)"',
		'User-Agent: "Dalvik/2. 1. 0 (Linux; U; Android 5.0.2; D5503 Build/14.5.A.0.270)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.4.4; Ascend G510 Build/KTU84Q)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.1.2; GT-S6310 Build/JZO54K)kman)"',
		'User-Agent: "Dalvik/1.1.0 (Linux; U; Android 2.1-update1; E15i Build/2.1.1.A.0.6)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.0.4; W2430 Build/IMM76D)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.0.4; W2430 Build/IMM76D)014; Profile/MIDP-2.1 Configuration/CLDC-1"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.0.4; W2430 Build/IMM76D)CLDC-1.1; Opera Mini/att/4.2.22250; U; en-"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.1.2; LG-E410 Build/JZO54K)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.4.2; ASUS_T00Q Build/KVT49L)"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.4.2; ASUS_T00Q Build/KVT49L)0310; Profile/MIDP-2.1 Configuration/C"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.4.2; ASUS_T00Q Build/KVT49L)UNTRUSTED/1.0C-1.1; Opera Mini/att/4.2"',
		'User-Agent: "Dalvik/1.6.0 (Linux; U; Android 4.4.2; ASUS_T00Q Build/KVT49L)/CLDC-1.1"'
	];

	//Getting PID of ffmpeg processing XID
	exec("ps axwww | grep $xid | grep -v grep | head -n 1 | awk '{print $1}'", $pid);
	//Starting ffmpeg if it is not running
	$radioRow = $db->sql_fetchrow($db->sql_query("SELECT * FROM radios WHERE xid='$xid' LIMIT 1"));

	//  echo "Error! No radiostation with XID: $xid in db.";
	if ($radioRow['streamurl'] == '') {
		http_response_code(404);
		die();
	}

	if ($pid[0] == '') {
		if ($radioRow['header'] == '') {
			$head = $input[array_rand($input)]; //select random user-agent from array
		} else {
			$head = $radioRow['header'];
		}
		
		if ($radioRow['noencode'] == 1) {
			$encoder = '-c:a copy';
		} else {
			$encoder = '-c:a libfdk_aac -b:a 96k';
		}
		
		//Starting ffmpeg
		//Now let's remove the playing dir
		$play_dir = $uploads_path . $xid;
		rrmdir($play_dir);
		//And create a new one
		mkdir($play_dir, 0755, true);
		//Then launch ffmpeg in background, redirect its output to devnull and sleep 7sec before returning playlist contents
		$ffCmd = '{ffmpeg} -headers {head} -i {url} {codec} -f ssegment -segment_list {seglist} -segment_list_flags +live -segment_time 7 -segment_list_size 3 -segment_wrap 5 -segment_list_entry_prefix {prefix} {out} > /dev/null 2>&1 &';
		$values = [
			'{ffmpeg}' => $ffbin,
			'{head}' => escapeshellarg($head),
			'{url}' => escapeshellarg($radioRow['streamurl']),
			'{codec}' => $encoder,
			'{seglist}' => escapeshellarg($play_dir . '/pl.m3u8'),
			'{prefix}' => escapeshellarg('http://' . $domain . '/uploads/playing/' . $xid . '/'),
			'{out}' => escapeshellarg($play_dir . '/96%03d.aac')
		];
		exec(str_replace(array_keys($values), array_values($values), $ffCmd));
		sleep(7);
		//Update PID and lastrequest in database
		exec("ps axwww | grep $xid | grep -v grep | head -n 1 | awk '{print $1}'", $pid);
		if (empty($pid[0])) {
			http_response_code(404);
			rrmdir($play_dir);
			die();
		}
	}

	$db->sql_query("UPDATE radios SET pid='$pid[0]' WHERE xid='$xid'");
	return $pid[0];
}
