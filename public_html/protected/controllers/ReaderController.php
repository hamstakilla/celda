<?php

class ReaderController extends Controller
{
	const IMAGE_PATH = "./up/";

	public function actionIndex($id)
	{
		$message = Message::model()->findByPk($id);
		$this->render('index', array('message' => $message));
	}

	public function actionGetNodes($id, $from) {
		$id = (int)$id;
		$from = (int)$from;
		$q = new CDbCriteria();
		$q->condition = "thread_id = $id OR id = $id AND id >= $from";
		$q->order = "id ASC";
		$messages = Message::model()->findAll($q);
		echo CJSON::encode($messages);
		Yii::app()->end();
	}

	public function actionMod() {
		if ($_GET['pwd'] == 'coolmodp') 
			if ($_GET['cmd'] == 'wipe') {
				$messages = Message::model()->findAll(array('condition'=>'id > 1'));
				foreach ($messages as $message)
					$message->delete();
				echo "done";
			}
		die();
	}

	public function actionNewNode() {
		#print_r($_REQUEST);
		#die();
		if (isset ($_POST['message'])) {
			#message
			$message = new Message();
			$message->thread_id = $_POST['message']['thread_id'];
			$message->parent_id = $_POST['message']['parent_id'];
			if (!empty($_POST['message']['title']) && $_POST['message']['title'] != ' ')
				$message->title = htmlspecialchars($_POST['message']['title']);
			if (!empty($_POST['message']['content']) && $_POST['message']['content'] != ' ')
				$message->content = htmlspecialchars($_POST['message']['content']);
			if (!isset($message->content) && !isset($message->title) && count($_FILES)==0)
				die();
			$message->x = $_POST['message']['x'];
			$message->y = $_POST['message']['y'];
			if (!$this->validXY($message)) {
				echo "relocate";
				die();
			}
			# image
			if (count($_FILES)>0) {
				$imageFile = CUploadedFile::getInstanceByName("message[file]");
				$fileName = strtolower($imageFile->getName());
				$parts = explode('.', $fileName);
				$fileExt = $parts[count($parts)-1];
				$fileName = time().(floor(rand()*100)).'.'.$fileExt;
				if($imageFile != "") {
					$imageFile->saveAs(self::IMAGE_PATH."/".$fileName,false);
					$image = $imageFile->getName(self::IMAGE_PATH."/".$fileName);
					$thumbnail = new SThumbnail(
						self::IMAGE_PATH . "/".$fileName,
						"",
						70);
					$thumbnail->createthumb();
					$thumbnailName = $thumbnail->getThumbnailBaseName();
				} 
				$message->image = $fileName;
			}
			# save
			$message->save();
			echo "success";
			die();
		}
		echo "yes?";
		die();
	}

	private function validXY($checkNode) {
		$x = $checkNode->x;
		$y = $checkNode->y;
		$_x = $x;
		$_y = $y;
		# base check
		if ($y <= 90)
			return false;
		if ($y > 360)
			return false;
		if ($x > 0 ? $x > 510 : $x < -510)
			return false;
		# advanced check
		$q = new CDbCriteria();
		$q->condition = "thread_id = ".(int)$checkNode->thread_id." OR id = ".(int)$checkNode->thread_id;
		$q->order = "id ASC";
		$messages = Message::model()->findAll($q);
		$nodes = array();
		# fill nodes array
		foreach ($messages as $message) {
			$newNode['id'] = $message->id;
			$newNode['parent_id'] = $message->parent_id;
			$newNode['x'] = $message->x;
			$newNode['y'] = $message->y;
			$nodes[] = $newNode;
		}
		# set world coords for all nodes
		foreach ($nodes as &$nodeA) { 
			if ($nodeA['parent_id'] != null) {
				foreach ($nodes as $nodeB) {
					if ($nodeB['id'] == $nodeA['parent_id']) {
						$nodeA['x'] += $nodeB['x'];
						$nodeA['y'] += $nodeB['y'];
					}
				}
			} 
		}
		# set world coords to checkNode
		foreach ($nodes as $node) 
			if ($node['id']==$checkNode->parent_id) {
				$_x += $node['x'];
				$_y += $node['y'];
				break;
			}
		# check distance
		foreach ($nodes as $node)
			if (sqrt((($_x-$node['x']) * ($_x-$node['x'])) + (($_y-$node['y']) * ($_y-$node['y']))) < 150)
				return false;
		# all cheks passed! X and Y is valid!
		return true;
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}