<?
	function ftp_mget($server, $username, $password, $files, $remote_dir, $local_dir) {
		global $debug;

		# Connect to FTP server
		$conn_id = ftp_connect($server);

		if ($conn_id) {
			if ($debug) echo "Connected to $server\n";

			# Turn passive mode on
			ftp_pasv($conn_id, true);

			# Login with username and password
			if (ftp_login($conn_id, $username, $password)) {
				if ($debug) echo "Logged in as $username / $password\n";

				# Change to remote directory
				if (ftp_chdir($conn_id, $remote_dir)) {
					if ($debug) echo "Changed remote directory to $remote_dir\n";

					# Download files
					foreach ($files as $filepath) {
						if ($debug) $start = time();
						if (ftp_get($conn_id, "$local_dir/$filepath", $filepath, FTP_BINARY)) {
	#						chmod("$local_dir/$filepath", 0664);
							if ($debug) echo "Download time for $filepath: ". (time() - $start) ."s\n";
						} else {
							echo date('Y/m/d H:i:s') .": $errstr ($errno) [ftp_get($conn_id, $local_dir/$filepath, $filepath, FTP_BINARY)]\n";
						}
					}
				} else {
					echo date('Y/m/d H:i:s') .": $errstr ($errno) [ftp_chdir($conn_id, $remote_dir)]\n";
				}
			} else {
				echo date('Y/m/d H:i:s') .": $errstr ($errno) [ftp_login($conn_id, $username, $password)]\n";
			}

			# Close the FTP connection
			ftp_close($conn_id);
		} else {
			echo date('Y/m/d H:i:s') .": $errstr ($errno) [ftp_connect($server)]\n";
		}
	}

	function unzip($filepath) {
		global $debug;

		$path_parts = pathinfo($filepath);
		$ext = $path_parts['extension'];
		$quiet = $debug ? '' : 'qq';

		if($ext == 'gz') $exec_string = 'gunzip -f '. $filepath;
		if($ext == 'zip') $exec_string = "unzip -{$quiet}uo $filepath -d {$path_parts['dirname']}";

		if ($debug) echo "$exec_string\n";
		passthru($exec_string);
	}
?>
