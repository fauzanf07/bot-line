<?php

require __DIR__ . '/vendor/autoload.php';
require_once('Git.php');

use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// load config
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

// initiate app
$configs =  [
	'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);

function remove_reminder(){
	$message ='';
	$namaFile1 = 'reminder.txt';
	$namaFile2 = 'date.txt';
	$namaFile3 = 'time.txt';
	if(filesize($namaFile1) != 0){
		$handle = fopen($namaFile1, "r");
		$data_tugas = fread($handle, filesize($namaFile1));
		fclose($handle);
		$handle = fopen($namaFile2, "r");
		$data_date = fread($handle, filesize($namaFile2));
		fclose($handle);
		$handle = fopen($namaFile3, "r");
		$data_time = fread($handle, filesize($namaFile3));
		fclose($handle);
		$list_tugas = explode("_", $data_tugas);
		$list_date = explode("_", $data_date);
		$list_time = explode("_", $data_time);
		$data_tugas = '';
		$data_date = '';
		$data_time = '';
		date_default_timezone_set('Asia/Jakarta');
		$now = new DateTime();
		for($i=0;$i<count($list_tugas);$i++){
			$deadline = $list_date[$i] ." " .$list_time[$i];
			$future_date = new  DateTime($deadline);
			if($now > $future_date){
				continue;
			}
			else{
				if($data_tugas ==''){
					$data_tugas = $list_tugas[$i];
					$data_date = $list_date[$i];
					$data_time = $list_time[$i];
				}
				else{
					$data_tugas = $data_tugas . "_" . $list_tugas[$i];
					$data_date = $data_date . "_" . $list_date[$i];
					$data_time = $data_time . "_" . $list_time[$i];
				}
			}
		}
		$handle = fopen($namaFile1, 'w');
		fwrite($handle,$data_tugas);
		fclose($handle);
		$handle = fopen($namaFile2, 'w');
		fwrite($handle,$data_date);
		fclose($handle);
		$handle = fopen($namaFile3, 'w');
		fwrite($handle,$data_time);
		fclose($handle);
	}
}

function update(){
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);
	date_default_timezone_set('Asia/Jakarta');
	$message ='';
	$namaFile1 = 'reminder.txt';
	$namaFile2 = 'date.txt';
	$namaFile3 = 'time.txt';
	if(filesize($namaFile1) != 0){
		$handle = fopen($namaFile1, "r");
		$data_tugas = fread($handle, filesize($namaFile1));
		fclose($handle);
		$handle = fopen($namaFile2, "r");
		$data_date = fread($handle, filesize($namaFile2));
		fclose($handle);
		$handle = fopen($namaFile3, "r");
		$data_time = fread($handle, filesize($namaFile3));
		fclose($handle);
		$list_tugas = explode("_", $data_tugas);
		$list_date = explode("_", $data_date);
		$list_time = explode("_", $data_time);
		if(filesize("groupId.txt")!=0){
			$tugas_done = '';
			$hari_1 ='';
			$jam_8 ='';
			$handle = fopen("groupId.txt", "r");
			$groupId = fread($handle, filesize("groupId.txt"));
			fclose($handle);
			$now =  new DateTime();
			for($i=0;$i<count($list_tugas);$i++){
				$deadline = $list_date[$i] . " " .$list_time[$i];
				$future_date = new DateTime($deadline);
				$diffrence = $future_date->diff($now);
				$hari = $diffrence->format("%a");
				$jam = $diffrence->format("%h");
				$menit = $diffrence->format("%i");
				if($hari == '0' && $jam =='23' && $menit == '59'){
					if($hari_1 == ''){
						$hari_1 = $list_tugas[$i];
					}
					else{
						$hari_1 = $hari_1 . "_" .$list_tugas[$i];
					}
				}
				if($hari == '0' && $jam =='7' && $menit == '59'){
					if($jam_8 == ''){
						$jam_8 = $list_tugas[$i];
					}
					else{
						$jam_8 = $jam_8 . "_" .$list_tugas[$i];
					}
				}
				if($now >= $future_date){	
					if($tugas_done == ''){
						$tugas_done = $list_tugas[$i];
					}
					else{
						$tugas_done = $tugas_done . "_" .$list_tugas[$i];
					}
				}
			}
			$multiMessageBuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
			$tugas_done = explode("_", $tugas_done);
			$hari_1 = explode("_", $hari_1);
			$jam_8 = explode("_", $jam_8);
			if($tugas_done[0] != ''){
				for($i=0;$i<count($tugas_done);$i++){
					
						$message = $tugas_done[$i] . "\n\nTugas telah Berakhir";
						$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
						$multiMessageBuilder->add($textMessageBuilder);
				}
				
			}
			if($hari_1[0] != ''){
				for($i=0;$i<count($hari_1);$i++){
					
						$message = $hari_1[$i] . "\n\nDeadline 1 hari lagi\n\nSemangat!!";
						$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
						$multiMessageBuilder->add($textMessageBuilder);
				}
			}

			if($jam_8[0] != ''){
				for($i=0;$i<count($jam_8);$i++){
						$message = $jam_8[$i] . "\n\nDeadline 8 Jam lagi\n\nAyo, kamu pasti bisa.. Semangat kaka!!";
						$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
						$multiMessageBuilder->add($textMessageBuilder);
				}
				$audio = new \LINE\LINEBot\MessageBuilder\AudioMessageBuilder("https://boiling-badlands-22611.herokuapp.com/tarik_sis.m4a",4000);
				$multiMessageBuilder->add($audio);
			}
			remove_reminder();
			$result = $bot->pushMessage($groupId, $multiMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
			
		}
	}
}


$app->get('/', function ($request, $response) {
	update();
	return "Lanjutkan";
});


$app->post('/', function ($request, $response)
{
	// get request body and line signature header
	$body 	   = file_get_contents('php://input');
	$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'];

	// log body and signature
	file_put_contents('php://stderr', 'Body: '.$body);

	// is LINE_SIGNATURE exists in request header?
	if (empty($signature)){
		return $response->withStatus(400, 'Signature not set');
	}

	// is this request comes from LINE?
	if($_ENV['PASS_SIGNATURE'] == false && ! SignatureValidator::validateSignature($body, $_ENV['CHANNEL_SECRET'], $signature)){
		return $response->withStatus(400, 'Invalid signature');
	}

	// init bot
	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);
	$data = json_decode($body, true);
	foreach ($data['events'] as $event)
	{
		$userMessage = $event['message']['text'];
		$groupId = $event['source']['groupId'];
		$arr_message = explode("_",$userMessage);
		if($groupId !='' && filesize('groupId.txt') == 0){
			$handle = fopen('groupId.txt', 'w');
			fwrite($handle,$groupId);
			fclose($handle);
		}
		if(strtolower($userMessage) == 'halo')
		{
			$message = "Halo juga";
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		
		}
		if(strtolower($userMessage) == 'pokcoi'){
			$message = "Iya, ada yang bisa saya bantu ?";
            $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$carouselTemplateBuilder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder([
			  new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Bagi Kelompok", "Membagi teman-teman menjadi beberapa kelompok","https://boiling-badlands-22611.herokuapp.com/group.png",[
			  new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('Mulai',"/mulai_bagi_kelompok"),
			  ]),
			  new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Tugas Reminder", "Membuat sebuah pengingat tugas","https://boiling-badlands-22611.herokuapp.com/reminder.jpg",[
			  new \LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder('Mulai',"/mulai_buat_pengingat"),
			  ]),
			]);
			$templateMessage = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder('nama template',$carouselTemplateBuilder);
			$multiMessageBuilder = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
			$multiMessageBuilder->add($textMessageBuilder);
			$multiMessageBuilder->add($templateMessage);
			$result = $bot->replyMessage($event['replyToken'],$multiMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}
		if(strtolower($userMessage) == '/mulai_bagi_kelompok'){
			$message = "Oke teman-teman jadi untuk membuat kelompok, kalian hanya mengetik perintah seperti dibawah ini yah : \n\n /create kelompok_judul kelompok_jumlah kelompok \n\n Aku beri contoh :\n /create kelompok_kelompok basis data_9 \n\n Oke, Selamat Mencoba!";
			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}

		if(strtolower($arr_message[0]) == '/create kelompok'){

			$judul_kelompok = strtoupper($arr_message[1]);
			$jumlah_kelompok = (int)$arr_message[2];
			$mahasiswa_laki = array("Yafi", "Adhi","Ahmad","Bagas","Bambang","Dalih","Fadhil","Fauzan","Ferdi","Ghirah","Gilang","Ibnu","Ifana","Irfan","Isyak","Luthfi","Jiddan","Reza","Aldi","Rachim", "Shofwan", "Yusuf", "Nandiaz", "Rahmat", "Rival", "Wisnu", "Yazid");
			$mahasiswa_cewek = array("Agil","Anggita", "Annisa Sekar", "Anissa Putri","Asita","Ichy", "Julia", "Lelah", "Meila","Nabila","Regina","Siti","Tiara","Wafa","Zahra");
			$kelompok = array();
			$sisa_anggota = array();
			for($i=0; $i<$jumlah_kelompok;$i++){
				$kelompok[$i] = $i;
			}
			shuffle($mahasiswa_laki);
			shuffle($mahasiswa_cewek);
			shuffle($kelompok);
			$anggota_laki = intdiv(27, $jumlah_kelompok);
			$anggota_cewek = intdiv(15, $jumlah_kelompok);
			$sisa_anggota_laki = 27 % $jumlah_kelompok;
			$sisa_anggota_cewek = 15 % $jumlah_kelompok;
			$a =0;
			if($sisa_anggota_laki != 0){
				for($i=($anggota_laki*$jumlah_kelompok);$i<27;$i++){
					$sisa_anggota[$a] = $mahasiswa_laki[$i];
					$a++;
				}
			}
			if($sisa_anggota_cewek != 0){
				for($i=($anggota_cewek*$jumlah_kelompok);$i<15;$i++){
					$sisa_anggota[$a] = $mahasiswa_cewek[$i];
					$a++;
				}
			}
			var_dump($mahasiswa_laki);
			echo "<br/><br/>";
			var_dump($mahasiswa_cewek);
			echo "<br/><br/>";
			var_dump($sisa_anggota);
			echo "<br/><br/>";
			var_dump($kelompok);
			$a=0;$b=0;$c=0;
			$message = $judul_kelompok;
			for($i=0;$i<$jumlah_kelompok;$i++){
				$message = $message . "\n\nKelompok " .($i+1);
				for($j=$a;$j<$anggota_laki*($i+1);$j++){
					$message = $message ."\n- ".$mahasiswa_laki[$j];
					$a++;
				}
				for($j=$b;$j<$anggota_cewek*($i+1);$j++){
					$message = $message ."\n- ".$mahasiswa_cewek[$j];
					$b++;
				}
				if(count($sisa_anggota) >= $jumlah_kelompok){
						$message = $message ."\n- ".$sisa_anggota[$c];
						$c++;
				}
				for($j=0;$j<(42%$jumlah_kelompok);$j++){
					if($i==$kelompok[$j]){
						$message = $message ."\n- ".$sisa_anggota[$c];
						$c++;
					}
				}
			}

			

			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}
		if(strtolower($userMessage) == '/mulai_buat_pengingat'){
			$message = "Oke teman-teman dalam menu Tugas Reminder ini kalian bisa Menambah, Melihat, Mengedit, dan Menghapus pengingat yang kalian akan buat. Untuk menjalankannya silahkan ketik perintah sebagai berikut.\n\nUntuk membuat pengingat :\n/add reminder_nama tugas_tanggal deadline dengan format(dd-mm-yyyy)_waktu deadline dengan format(hh:mm)_keterangan\n\nUntuk melihat pengingat yang telah dibuat :\n/view_reminder\n\nUntuk mengedit pengingat :\n/edit reminder_nomor pengingat_nama tugas_tanggal deadline dengan format(dd-mm-yyyy)_waktu deadline dengan format(hh:mm)_keterangan\n\nUntuk menghapus pengingat ada bebeapa cara yakni : \n1. /remove reminder_no menu yang akan dihapus pada view reminder \n2. /remove reminder_all (untuk menghapus semua pengingat)\n\n Selamat Mencoba!!";
			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}
		if(strtolower($arr_message[0]) == "/add reminder"){
			date_default_timezone_set('Asia/Jakarta');
			$now = new DateTime();
			$date = explode("-", $arr_message[2]);
			$checkdate = checkdate($date[1], $date[0], $date[2]);
			if($checkdate == true && strtotime($arr_message[3]) !== false){
				$deadline = new DateTime($arr_message[2] . " " .$arr_message[3].":00");
				if($now < $deadline){
					$date = strtotime($arr_message[2]);
					$reminder = $arr_message[1] . " ( " .	date('j F Y',$date) . " " . $arr_message[3] . " " . $arr_message[4] . " )";
					$namaFile1 = 'reminder.txt';
					$namaFile2 = 'date.txt';
					$namaFile3 = 'time.txt';
					if(filesize($namaFile1) == 0){
						$handle = fopen($namaFile1, 'a');
						fwrite($handle,$reminder);
						fclose($handle);
					}else{
						$reminder = "_" .$reminder;
						$handle = fopen($namaFile1, 'a');
						fwrite($handle,$reminder);
						fclose($handle);
					}
					if(filesize($namaFile2) == 0){
						$handle = fopen($namaFile2, 'a');
						fwrite($handle,$arr_message[2]);
						fclose($handle);
					}else{
						$date = "_" .$arr_message[2];
						$handle = fopen($namaFile2, 'a');
						fwrite($handle,$date);
						fclose($handle);
					}
					if(filesize($namaFile3) == 0){
						$time = $arr_message[3] . ":00";
						$handle = fopen($namaFile3, 'a');
						fwrite($handle,$arr_message[3]);
						fclose($handle);
					}else{
						$time = "_" .$arr_message[3] . ":00";
						$handle = fopen($namaFile3, 'a');
						fwrite($handle,$time);
						fclose($handle);
					}
					$message = "Berhasil membuat reminder tugas!";
				}
				else{
					$message = "Maaf tugas tersebut sudah berlalu!";
				}
			}
			else{
				$message = "Format tanggal atau waktu salah";
			}

		

			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}
		if(strtolower($userMessage) == "/view_reminder"){
			date_default_timezone_set('Asia/Jakarta');
			$namaFile1 = 'reminder.txt';
			$namaFile2 = 'date.txt';
			$namaFile3 = 'time.txt';
			if(filesize($namaFile1) !=0){
				$message = "TUGAS SAAT INI : \n";
				$handle = fopen($namaFile1, "r");
				$data_tugas = fread($handle, filesize($namaFile1));
				fclose($handle);
				$handle = fopen($namaFile2, "r");
				$data_date = fread($handle, filesize($namaFile2));
				fclose($handle);
				$handle = fopen($namaFile3, "r");
				$data_time = fread($handle, filesize($namaFile3));
				fclose($handle);
				$list_tugas = explode("_", $data_tugas);
				$list_date = explode("_", $data_date);
				$list_time = explode("_", $data_time);
				$now = new DateTime();
				for($i=0;$i<count($list_tugas);$i++){
					$message = $message . "\n". ($i+1) . ". " . $list_tugas[$i] ;
					$deadline = $list_date[$i] . " " . $list_time[$i];
					$future_date = new DateTime($deadline);
					$interval = $future_date->diff($now);
					if($now < $future_date){
						$message = $message  . "\nSisa Waktu : " . $interval->format("%a Hari, %h Jam, %i menit, %s detik ") . " \n";
					}else{
						$message = $message . "\nTugas sudah selesai\n";
					}
					
				}
			}

			else{
				$message = "Tidak ada tugas untuk saat ini!";
			}
			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}
		if(strtolower($arr_message[0]) == "/edit reminder"){
			$message ='';
			$namaFile1 = 'reminder.txt';
			$namaFile2 = 'date.txt';
			$namaFile3 = 'time.txt';
			$handle = fopen($namaFile1, "r");
			$data_tugas = fread($handle, filesize($namaFile1));
			fclose($handle);
			$handle = fopen($namaFile2, "r");
			$data_date = fread($handle, filesize($namaFile2));
			fclose($handle);
			$handle = fopen($namaFile3, "r");
			$data_time = fread($handle, filesize($namaFile3));
			fclose($handle);
			$list_tugas = explode("_", $data_tugas);
			$list_date = explode("_", $data_date);
			$list_time = explode("_", $data_time);
			if((int)$arr_message[1] <= count($list_tugas) && (int)$arr_message[1] >= 1){
				$handle = fopen($namaFile1, "r+");
				ftruncate($handle, 0);
				fclose($handle);
				$handle = fopen($namaFile2, "r+");
				ftruncate($handle, 0);
				fclose($handle);
				$handle = fopen($namaFile3, "r+");
				ftruncate($handle, 0);
				fclose($handle);
				for($i=0;$i<count($list_tugas);$i++){
					$a = ((int)$arr_message[2])-1;
					if($i==0){					
						if($i== ((int)$arr_message[1])-1){
							$date = explode("-", $arr_message[3]);
							$checkdate = checkdate($date[1], $date[0], $date[2]);
							$date = strtotime($arr_message[3]);
							if($checkdate == true){
								$data_reminder = $arr_message[2] . " ( " .	date('j F Y',$date) . " " . $arr_message[4] . " " . $arr_message[5] . " )";
								$data_date = $arr_message[3];
								$data_time = $arr_message[4] . ":00";
							}
							else{
								$message = "Format tanggal salah";
							}
						}
						else{
							$data_reminder = $list_tugas[$i];
							$data_date = $list_date[$i];
							$data_time = $list_time[$i];
						}
					}else{
						if($i== (int)$arr_message[1]-1){
							$date = explode("-", $arr_message[3]);
							$checkdate = checkdate($date[1], $date[0], $date[2]);
							$date = strtotime($arr_message[3]);
							if($checkdate == true){
								$data_reminder = $data_reminder ."_" . $arr_message[2] . " ( " .	date('j F Y',$date) . " " . $arr_message[4] . " " . $arr_message[5] . " )";
								$data_date = $data_date . "_" . $arr_message[3];
								$data_time = $data_time . "_" . $arr_message[4] . ":00";
							}
							else{
								$message = "Format tanggal salah";
							}
						}
						else{
							$data_reminder = $data_reminder. "_" . $list_tugas[$i];
							$data_date = $data_date . "_" . $list_date[$i];
							$data_time = $data_time . "_" . $list_time[$i];
						}
					}
					
				}
				$handle = fopen($namaFile1, 'w');
				fwrite($handle,$data_reminder);
				fclose($handle);
				$handle = fopen($namaFile2, 'w');
				fwrite($handle,$data_date);
				fclose($handle);
				$handle = fopen($namaFile3, 'w');
				fwrite($handle,$data_time);
				fclose($handle);

				if($message == ''){
					$message = "Berhasil edit data";
				}
			}
			else{
				$message = "Maaf tidak ada menu nomor ". $arr_message[1];
			}
			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}
		if(strtolower($arr_message[0]) == "/remove reminder"){
			if($groupId == ''){
				$message = "Maaf anda tidak bisa menghapus reminder!!";
				
			}else{
				$message ='';
				$namaFile1 = 'reminder.txt';
				$namaFile2 = 'date.txt';
				$namaFile3 = 'time.txt';
				if(filesize($namaFile1) != 0){
					$handle = fopen($namaFile1, "r");
					$data_tugas = fread($handle, filesize($namaFile1));
					fclose($handle);
					$handle = fopen($namaFile2, "r");
					$data_date = fread($handle, filesize($namaFile2));
					fclose($handle);
					$handle = fopen($namaFile3, "r");
					$data_time = fread($handle, filesize($namaFile3));
					fclose($handle);
					$list_tugas = explode("_", $data_tugas);
					$list_date = explode("_", $data_date);
					$list_time = explode("_", $data_time);
					if(strtolower($arr_message[1]) == "all"){
						$handle = fopen($namaFile1, "r+");
						ftruncate($handle, 0);
						fclose($handle);
						$handle = fopen($namaFile2, "r+");
						ftruncate($handle, 0);
						fclose($handle);
						$handle = fopen($namaFile3, "r+");
						ftruncate($handle, 0);
						fclose($handle);
						$message = "Berhasil menghapus data";
					}
					elseif((int)$arr_message[1] <= count($list_tugas) && (int)$arr_message[1] >= 1){
						$data_tugas = '';
						$data_date = '';
						$data_time = '';
						for($i=0;$i<count($list_tugas);$i++){
							if($i == (int)$arr_message[1]-1){
								continue;
							}
							if($data_tugas == ''){
								$data_tugas = $data_tugas . $list_tugas[$i];
								$data_date = $data_date . $list_date[$i];
								$data_time = $data_time . $list_time[$i];
							}else{
								$data_tugas = $data_tugas ."_". $list_tugas[$i];
								$data_date = $data_date ."_". $list_date[$i];
								$data_time = $data_time ."_". $list_time[$i];
							}
						}

						$handle = fopen($namaFile1, 'w');
						fwrite($handle,$data_tugas);
						fclose($handle);
						$handle = fopen($namaFile2, 'w');
						fwrite($handle,$data_date);
						fclose($handle);
						$handle = fopen($namaFile3, 'w');
						fwrite($handle,$data_time);
						fclose($handle);

						if($message == ''){
							$message = "Berhasil hapus data";
						}
					}
					elseif($arr_message[1] == "isDone"){
						$message ='';
						$namaFile1 = 'reminder.txt';
						$namaFile2 = 'date.txt';
						$namaFile3 = 'time.txt';
						if(filesize($namaFile1) != 0){
							$handle = fopen($namaFile1, "r");
							$data_tugas = fread($handle, filesize($namaFile1));
							fclose($handle);
							$handle = fopen($namaFile2, "r");
							$data_date = fread($handle, filesize($namaFile2));
							fclose($handle);
							$handle = fopen($namaFile3, "r");
							$data_time = fread($handle, filesize($namaFile3));
							fclose($handle);
							$list_tugas = explode("_", $data_tugas);
							$list_date = explode("_", $data_date);
							$list_time = explode("_", $data_time);
							$data_tugas = '';
							$data_date = '';
							$data_time = '';
							date_default_timezone_set('Asia/Jakarta');
							$now = new DateTime();
							for($i=0;$i<count($list_tugas);$i++){
								$deadline = $list_date[$i] ." " .$list_time[$i];
								$future_date = new  DateTime($deadline);
								if($now > $future_date){
									continue;
								}
								else{
									if($data_tugas ==''){
										$data_tugas = $list_tugas[$i];
										$data_date = $list_date[$i];
										$data_time = $list_time[$i];
									}
									else{
										$data_tugas = $data_tugas . "_" . $list_tugas[$i];
										$data_date = $data_date . "_" . $list_date[$i];
										$data_time = $data_time . "_" . $list_time[$i];
									}
								}
							}
							$handle = fopen($namaFile1, 'w');
							fwrite($handle,$data_tugas);
							fclose($handle);
							$handle = fopen($namaFile2, 'w');
							fwrite($handle,$data_date);
							fclose($handle);
							$handle = fopen($namaFile3, 'w');
							fwrite($handle,$data_time);
							fclose($handle);
						}
					}
					else{
						$message = "Maaf tidak ada menu nomor ". (int)$arr_message[1];
					}
				}
				else{
					$message = "Pengingat masih kosong";
				}
			}
			$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($message);
			$result = $bot->replyMessage($event['replyToken'],$textMessageBuilder);
			return $result->getHTTPStatus() . ' ' . $result->getRawBody();
		}

	}
	

});


// $app->get('/push/{to}/{message}', function ($request, $response, $args)
// {
// 	$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV['CHANNEL_ACCESS_TOKEN']);
// 	$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV['CHANNEL_SECRET']]);

// 	$textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($args['message']);
// 	$result = $bot->pushMessage($args['to'], $textMessageBuilder);

// 	return $result->getHTTPStatus() . ' ' . $result->getRawBody();
// });

/* JUST RUN IT */
$app->run();
?>