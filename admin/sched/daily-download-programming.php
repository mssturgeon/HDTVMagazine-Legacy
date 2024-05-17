<?
	set_time_limit(0);

#	$debug = true;
#	$skipdownload = true;
/*
	$debug = isset($_GET['debug']);
	$skipdownload = isset($_GET['skipdownload']);
*/

	if ($debug) {
		header('Content-Type: text/plain');
		echo date('Y/m/d H:i:s') .': '. $_SERVER['PHP_SELF'] ."\n";
	}

	define('BASE_DIR', '/var/www/html');
	require(BASE_DIR .'/includes/constants.php');
	require(BASE_DIR .'/includes/lib_mysql.php');
	require(BASE_DIR .'/includes/lib_common.php');
	require(BASE_DIR .'/includes/lib_ftp.php');

	$download_dir = DATA_DIR .'/prog';

	if (!$skipdownload) {
		$files[] = 'TVGDataFile.zip';

		### Get FTP Files
		ftp_mget('ftp.data.macrovision.com', 'HDTVMagazine', 'V!rtu@l', $files, $remote_dir, $download_dir);
#		ftp_mget('ftp.tvguideinc.com', 'HDTVMagazine', 'V!rtu@l', $files, $download_dir);

		### Unzip Files
		foreach ($files as $filepath) {
			unzip("$download_dir/$filepath");
		}
	}

### Import files

	# Source
	$sql = "
		LOAD DATA INFILE '$download_dir/Source.txt' REPLACE INTO TABLE prog_source
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'
		SET analog_partner_id = nullif(analog_partner_id,''),
			ota_channel_number = nullif(ota_channel_number,''),
			dma_rank = nullif(dma_rank,'')";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Populate aux_prog_source with dates added for new sources
	$sql = "
	INSERT IGNORE aux_prog_source (source_id, added, img)
		SELECT source_id, CURRENT_DATE(), IF(affiliation_1<>'', CONCAT(LEFT(LCASE(affiliation_1), 3), '_[size].gif'), 'no-image_[size].gif')
		FROM prog_source";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# MSO
	$sql = "
		LOAD DATA INFILE '$download_dir/MSO.txt' REPLACE INTO TABLE prog_mso
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Headend
	$sql = "
		LOAD DATA INFILE '$download_dir/Headend.txt' REPLACE INTO TABLE prog_headend
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Channel_Lineup
	$sql = "
		LOAD DATA INFILE '$download_dir/Channel_Lineup.txt' REPLACE INTO TABLE prog_lineup
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# ZipCodes
	$sql = "
		LOAD DATA INFILE '$download_dir/ZipCodes.txt' REPLACE INTO TABLE prog_zipcode
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Schedule
	mQuery("DELETE FROM prog_schedule");
	$sql = "
		LOAD DATA INFILE '$download_dir/Schedule.txt' INTO TABLE prog_schedule
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Program
	$sql = "
		LOAD DATA INFILE '$download_dir/Program.txt' REPLACE INTO TABLE prog_program
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Program_Credits
	$sql = "
		LOAD DATA INFILE '$download_dir/Program_Credits.txt' REPLACE INTO TABLE prog_credits
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Program_Genres
	$sql = "
		LOAD DATA INFILE '$download_dir/Program_Genres.txt' REPLACE INTO TABLE prog_genre
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Program_Broadcast_History
/*
	$sql = "
		LOAD DATA INFILE '$download_dir/Program_Broadcast_History.txt' REPLACE INTO TABLE prog_history
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}
*/

	# Program_Ratings
	$sql = "
		LOAD DATA INFILE '$download_dir/Program_Ratings.txt' REPLACE INTO TABLE prog_rating
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}

	# Program_Premise
/*
	$sql = "
		LOAD DATA INFILE '$download_dir/Program_Premise.txt' REPLACE INTO TABLE prog_premise
		FIELDS TERMINATED BY '|' ESCAPED BY '\\\\' LINES TERMINATED BY '\\r\\n'";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}
*/

	# Populate Guide table
	mQuery("DELETE FROM prog_guide");
	$sql = "
		INSERT INTO prog_guide
		SELECT s.source_id, p.program_id, s.call_letters,
			IF(LOCATE('A ', title_128) = 1, REPLACE(title_128, 'A ', ''),
				IF(LOCATE('An ', title_128) = 1, REPLACE(title_128, 'An ', ''),
					IF(LOCATE('The ', title_128) = 1, REPLACE(title_128, 'The ', ''), title_128)
				)
			),
			p.title_128, p.title_50, p.title_30, p.title_15, p.title_8, p.subtitle, p.episode_title, p.episode_number,
			UNIX_TIMESTAMP(sc.start_date) + LEFT(sc.start_time, 2)*3600 + RIGHT(sc.start_time, 2)*60 + (UNIX_TIMESTAMP() - UNIX_TIMESTAMP(UTC_TIMESTAMP())),
			(LEFT(sc.duration, 2)*60)+RIGHT(sc.duration, 2), long_description, p.run_time, p.release_year, p.original_air_date, p.event_date, p.show_type, p.movie_type,
			p.star_rating, mpaa.rating, ustv.rating,
			sc.hdtv_level, sc.audio_level, sc.program_showing_type, sc.hdtv, sc.repeatYN, sc.live,
			sc.new, sc.letter_box, sc.closed_captioned, sc.part_number, sc.number_of_parts
		FROM prog_schedule sc, prog_source s, aux_prog_source a,
			prog_program p LEFT JOIN prog_rating mpaa ON p.program_id = mpaa.program_id AND mpaa.rating_type = 'MPAA'
			LEFT JOIN prog_rating ustv ON p.program_id = ustv.program_id AND ustv.rating_type = 'US TV'
		WHERE sc.source_id = s.source_id
			AND sc.source_id = a.source_id
			AND sc.program_id = p.program_id";
	if ($debug) {
		echo "$sql\n";
		$start = time();
		mQuery($sql);
		$duration = time() - $start;
		echo 'Loaded in: '. date("i:s", $duration) ."\n";
		ob_flush(); flush();
	} else {mQuery($sql);}
?>