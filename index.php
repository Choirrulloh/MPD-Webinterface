<?

include 'inc/mpd.class.php';
include 'inc/functions.inc.php';

$mpd = new mpd('localhost',6600);
$mpd->debugging = false;

define('CURRENTARTIST', $mpd->playlist[$mpd->current_track_id]['Artist']);
define('CURRENTTRACK', $mpd->playlist[$mpd->current_track_id]['Title']);
define('CURRENTID', $mpd->playlist[$mpd->current_track_id]['Id']);

include 'tpl/header.tpl.php';


if($mpd->connected == FALSE) {
    	echo "Error: " .$mpd->errStr;
} else {
	if(isset($_POST['toadd'])) {
		$object = $_POST['toadd'];
		$files = explode("\n", $mpd->SendCommand('lsinfo'));

		foreach($files as $row) {
			$file = explode(':', $row);
			$thefiles[][$file[0]] = ltrim($file[1]);
		}	

		foreach($thefiles as $search) {
			if(array_search($object, $search) == 'directory') {
				$dir = $mpd->GetDir($object);
				
				foreach($dir as $addRow) {
					$addArr[] = $addRow['file'];
				}
				
				$mpd->PLAddBulk($addArr);
				break;
			} else {
				$songs = explode(',', $object);

				$mpd->PLAddBulk($songs);
				break;
			}
		}
	
		header('Location: ./');
	}
	
	switch($_GET['a']) {
		case 'play':
			$mpd->Play();
			header('Location: ./#current');
			break;
		case 'pause':
			$mpd->Pause();
			header('Location: ./#current');
			break;
		case 'prev':
			$mpd->Previous();
			header('Location: ./#current');
			break;
		case 'next':
			$mpd->Next();
			header('Location: ./#current');
			break;
		case 'stop':
			$mpd->Stop();
			header('Location: ./#current');
			break;
		case 'start':
			$songID = (int) $_GET['id'];
			$mpd->SkipTo($songID);
			header('Location: ./#current');
			break;
		case 'clearpl':
			$mpd->PLClear();
			header('Location: ./');
			break;
		case 'remove':
			$songID = (int) $_GET['id'];
			$mpd->SendCommand('deleteid', $songID);
			$mpd->RefreshInfo();
			header('Location: ./#current');
			break;
	}

	switch($mpd->state) {
		case 'play':
			$status = 'playing';
			break;
		case 'pause':
			$status = 'paused';
			break;
		default:
			$status = 'stopped';
			break;
	}

	include 'tpl/main.tpl.php';
}

include 'tpl/footer.tpl.php';

?>