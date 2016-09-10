<?php

class Slack {

	public static function sendMessage($message) {

		$urlBase = 'https://'.Secret::SLACK_TEAM.'.slack.com/services/hooks/slackbot';

		$params = http_build_query(array(
			'token' => Secret::SLACK_TOKEN,
			'channel' => '#'.Secret::SLACK_CHANNEL
		));

		$url = $urlBase.'?'.$params;

		$response = Requests::post($url, array(), $message);

		return $response;

	}

}