<?php

class Webhooks {

	private $lists;

	public function updateWebhooks($request, $response, $params){
		$this->getLists();
		foreach($this->lists as $listID):
			if( !$this->webhookExists($this->getWebhooks($listID)) ){
				$this->addWebhookToList($listID);
			}
		endforeach;
	}

	public function listWebhooks($request, $response, $params){
		$this->getLists();
		foreach($this->lists as $listID):
			echo '<pre>';
				print_r($this->getWebhooks($listID));
			echo '</pre>';
		endforeach;
	}

	private function getLists(){
		$response = Requests::get( 'https://a.wunderlist.com/api/v1/folders/'.Secret::WUNDERLIST_FOLDER_ID, Auth::addHeaders());
		$this->lists = json_decode($response->body)->list_ids;
	}

	private function getWebhooks($listID){
		$listData = array('list_id'=>$listID);
		$existingHooks = Requests::get( 'https://a.wunderlist.com/api/v1/webhooks?'.http_build_query($listData), Auth::addHeaders());
		$existingHooks = json_decode($existingHooks->body);
		return $existingHooks;
	}

	private function webhookExists($webhooks){
		return ( !empty($webhooks) );
	}

	private function addWebhookToList($listID){
		$hookData = array(
			'list_id'=>$listID,
			'url'=>Secret::HOOK_ENDPOINT,
			'processor_type'=>'generic',
			'configuration'=>''
		);
		Requests::post( 'https://a.wunderlist.com/api/v1/webhooks', Auth::addHeaders(), $hookData);
		echo '<pre>';
			print_r('Added webhook to List #'.$listID);
		echo '</pre>';
	}

	public function receiveWebhook($request, $response, $params){
		$data = json_decode(file_get_contents('php://input'));

		if( !empty($data->cause) && $data->cause->subject->type == 'list' && $data->cause->operation == 'delete' ){
			$message = $this->buildListMessage($data);
		} else {
			switch($data->subject->type){
				case 'task':
					$message = $this->buildTaskMessage($data);
					break;
				case 'subtask':
					$message = $this->buildSubtaskMessage($data);
					break;
				default:
					break;
			}
		}

		if(!empty($message)){
			Slack::sendMessage( $message );
		}

	}

	private function getListDetails($listID){
		$list = Requests::get( 'https://a.wunderlist.com/api/v1/lists/'.$listID, Auth::addHeaders());
		$list = json_decode($list->body);
		return $list;
	}

	private function getTask($taskID){
		$task = Requests::get( 'https://a.wunderlist.com/api/v1/tasks/'.$taskID, Auth::addHeaders());
		$task = json_decode($task->body);
		return $task;
	}

	private function isComplete($data){
		return ( $data->before->completed == false && $data->after->completed );
	}

	private function buildSubtaskMessage($data){
		if( !$this->isComplete($data) ){
			return;
		}

		$subTaskName = $data->after->title;
		$task = $this->getTask($data->subject->parents[0]->id);
		$list = $this->getListDetails($task->list_id);
		return 'Task Complete: *'. $list->title . ' > ' . $task->title . ' > ' . $subTaskName.'*';
	}

	private function buildTaskMessage($data){
		if( !$this->isComplete($data) ){
			return;
		}

		$taskName = $data->after->title;
		$list = $this->getListDetails($data->after->list_id);
		$starred = ( $data->after->starred ) ? ' :star2:' : '';
		return 'Task Complete: *'. $list->title . ' > ' . $taskName.'*'.$starred;
	}

	private function buildListMessage($data){
		$list = $this->getListDetails($data->cause->subject->id);
		return 'List Complete: *'. $list->title . '*';
	}

}