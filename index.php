<?php

	$debug = false;

	//config file
	require_once('config.php');

	//default image compression qualities
	$quality = 60;
	$max_quality = 92;
	//nearest unit to ceiling out on for resized image width
	$nearest = 20;
	//set baseline width here
	//	defaulting to 640px width to provide small-ish images for low-bandwidth/narrow screens
	//	but also reasonable size/quality images so as not to look too poorly when blown up in wide screens
	$baseline = 640;
	//default empty image
	$mt = $local_host."/mt.gif";
	//whitelist of domains
	require_once('whitelist.php');

	//if is call to root don't process
	if($_SERVER['REQUEST_URI']!="/"){

		session_start();
		//check for screen width in session
		if( isset($_SESSION["clientWidth"]) && is_numeric($_SESSION["clientWidth"]) ){
			$w = $_SESSION["clientWidth"];
		} else {
			$w = $baseline;
		}
		session_write_close();		
		
		//query string is parsed down from the original url via the htaccess
		parse_str($_SERVER['QUERY_STRING'], $q);
		
		//check if src query is set
		if(isset($q['src'])){
		
			//sanitise url from query, so local filenames don't have any funky characters
			$remote_src_raw = $q['src'];
			$remote_src = filter_var($q['src'], FILTER_SANITIZE_URL);
			
			//break src url down and generate local input and output urls
			//parse image src down to just domain and path
			$remote_host = parse_url($remote_src, PHP_URL_HOST);
			$remote_path = parse_url($remote_src, PHP_URL_PATH);
			$remote_url = $remote_host.$remote_path;
			//setup local file/folder names
			$local_root = $_SERVER['DOCUMENT_ROOT'];
			$local_input = $local_root."/input/".$remote_host.$remote_path;
			//break local input url down into parts
			$local_input_pathinfo = pathinfo($local_input);
			$local_input_dirname = $local_input_pathinfo['dirname'];
			$local_filename = $local_input_pathinfo['filename'];
			$local_ext = strtolower($local_input_pathinfo["extension"]);
			//break local output url down into parts 
			$local_output_pathinfo = pathinfo($remote_url);
			$local_output_dirname = $local_output_pathinfo['dirname'];
			//determine folders only for now, file name is processed and appended later on
			$local_output = $local_root."/output/".$local_output_dirname;
			//$server_output = $GLOBALS["local_host"]."/output/".$local_output_dirname;
			$server_output = $local_host."/output/".$local_output_dirname;

			//check remote host against domains in whitelist
			//foreach counter
			$c = 1;
			//foreach ($GLOBALS["whitelist"] as &$domain) {
			foreach ($whitelist as &$domain) {
				//if remote host is in whitelist, process the file
				if( strpos($remote_host,$domain) !== false && strpos($remote_host,$domain) >= 0){

					//if range is not set to flush
					if($q['range']!="{f}"){

						//if file does not exist
						if (!file_exists($local_input)) {
							//grab it and make a local copy
							$dirname = dirname($local_input);
							if (!is_dir($dirname)) {
								mkdir($dirname, 0755, true);
							}
							$ch = curl_init();
							//get remote_src (not from sanitised url)
							curl_setopt($ch, CURLOPT_URL, $remote_src_raw);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							$data = curl_exec ($ch);
							curl_close ($ch);
							$file = fopen($local_input, "w+");
							fputs($file, $data);
							fclose($file);
						}

						/*
						$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
						finfo_file($finfo, $filename)
						finfo_close($finfo)
						*/

						$mime = image_type_to_mime_type(exif_imagetype($local_input));
						if(strpos($mime, 'image')!== false && strpos($mime, 'image') >= 0){
						
							//only need to calculate this if width is set
							if($w!="undefined"){

								//this is not really a great way to parse it all down, but good enough for now
								$s = explode("|", substr($q['range'], 1, -1));
								$ranges = array();
								$d=0;
								foreach ($s as &$value) {
									$value = explode(",", $value);
									foreach ($value as &$values) {
										$i = explode(":", $values);
										$ranges[$d][$i[0]] = $i[1];
									}
									$d++;
								}
						
								//compare screen width to values in ranges
								foreach ($ranges as &$range){
									//make sure have all the required values we need - min, max, and per
									//don't actually need quality since it's not used in Imagick at all...
									if( ( isset($range["min"]) && is_numeric($range["min"]) ) && ( isset($range["max"]) && ( is_numeric($range["max"]) || $range["max"]=="*") ) && ( isset($range["per"]) && is_numeric($range["per"]) ) ){

										//check in case max is set to "all"
										$max = ($range["max"]=="*") ? 99999 : $range["max"];
									
										//if width is within specific range of mix and max
										if( $w >= $range["min"] && $w < $max){

											//if output folder doesn't exist yet, create it
											if (!is_dir($local_output)) {
												mkdir($local_output, 0755, true);
											}

											//set up image with imagick
											$image = new Imagick($local_input);
											$img_width = $image->getImageWidth();

											$target_width = ceil( ($w * $range["per"])  / $nearest ) * $nearest;

											$final_width = $img_width;
											//if actual image width is greater than desired width
											if($img_width > $target_width){
												$final_width = $target_width;
											}
											//set output file name, local and server, using target width
											$filename = "/".$local_filename.".width-".$final_width."px.".$local_ext;
											$local_output .= $filename;
											$server_output .= $filename;
											
											//check to see if resized image exists
											if (!file_exists($local_output)) {

												//output resized image
												//FILTER_CATROM
												//FILTER_LANCZOS
												//$image->resizeImage(, ,Imagick::FILTER_CATROM,1);

												//calc width, in case passing 0 is causing the errors
												$final_height = round( $image->getImageHeight() * ($final_width/$img_width) );

												try {
													$image->scaleImage($final_width,$final_height,true);
													$image->writeImage($local_output);
													$image->clear();
													$image->destroy();
												} catch(Exception $e){
													echo 'Caught exception: ',  $e->getMessage(), "\n";
												}

											}

											//send new header for resized image
											header("Location: $server_output");
											exit;
											
										} //end if, if width is within specific range

									} else {
									//if missing any required paramters output a blank image
										showMt("missing required parameters");
									}
								} //end foreach

							} else {
							//if cannot grep screen width from anywhere, just show original full image at source url
							//this will probably never be called since baseline is now set in code
								//give back original unsanitised url
								header("Location: $remote_src_raw");
								exit;
							}
						} else {
							//end if, if mime type is not image
							showMt("incorrect mime type");
						}

						//if domain is in whitelist, break out of foreach
						//this is probably redundant but good to have just in case
						return;

					} else {
						//if ($q['range']=="{f}"), do a flush
						//if input file exists, remove it
						if (file_exists($local_input)) {
							unlink($local_input);
						}
						//check for processed files as well and remove if exist
						foreach (glob($local_output."/".$local_filename.".width-*px.".$local_ext) as $filename) {
							unlink($filename);
						}
						exit;
					}

				}
				//if domain is not in whitelist, add to counter and keep checking
				$c++;
			}

			//final check, if remote domain is not in whitelist
			if ($c>count($whitelist)) {
				showMt("domain not in whitelist");
			}

		} else {
			//end if, if src is not in query
			//this will basically never happen currently cause htaccess does not pass through if not a proper image extension
			showMt("src not in query");
		}
	
	} //end request uri check

	function showMt($e){
		//output blank gif for cases where no there is no image
		if($debug){
			header('Content-type: image/gif');
			readfile($GLOBALS["mt"]);
			exit;
		} else {
			echo $e."<br />";
		}
	}

?>
