<?php
declare(strict_types = 1);

namespace ss\api;

/**
 * Class ss_api
 */
class ss_api {

	public string $host;
	public int $port;
	public string $game;

	private $_curl_handle;
	private string $_cookie;

	public function __construct(string $game, string $host = '127.0.0.1', int $port = 49776) {
		$this->game = $game;
		$this->host = $host;
		$this->port = $port;
		$this->_cookie = sys_get_temp_dir()."/cookie_ss.txt";
		touch($this->_cookie);
	}

	function __destruct() {
		if (file_exists($this->_cookie)) unlink($this->_cookie);
	}

	public function game_event(string $event, ?array $data = null):array {
		$this->curl_init("{$this->host}:{$this->port}/game_event");
		$payload['game'] = $this->game;
		$payload['event'] = $event;
		if (null !== $data) $payload['data'] = $data;
		$this->curl_post($payload);
		return $this->curl_exec();
	}

	public function game_heartbeat():array {
		$this->curl_init("{$this->host}:{$this->port}/game_heartbeat");
		$this->curl_post([
			'game' => $this->game
		]);
		return $this->curl_exec();
	}

	/**
	 * @param string|null $game_display_name User-friendly name displayed in SSE. If this is not set, your game will show up as the game string sent with your data
	 * @param string|null $developer Developer name displayed underneath the game name in SSE. This line is omitted in SSE if the metadata field is not set.
	 * @param int $deinitialize_timer_length_ms By default, SSE will return to default behavior when the stop_game call is made or when no events have been received for 15 seconds. This can be used to customize that length of time between 1 and 60 seconds.
	 */
	public function game_metadata(?string $game_display_name = null, ?string $developer = null, int $deinitialize_timer_length_ms = 15000) {
		$this->curl_init("{$this->host}:{$this->port}/game_metadata");
		$payload = [
			'game' => $this->game,
			'deinitialize_timer_length_ms' => $deinitialize_timer_length_ms
		];
		if (null !== $game_display_name) $payload['game_display_name'] = $game_display_name;
		if (null !== $developer) $payload['developer'] = $developer;

		$this->curl_post($payload);
		return $this->curl_exec();
	}

	/**
	 * @param string $event Event name
	 * @param int|null $min_value Optional minimum numeric value for event
	 * @param int|null $max_value Optional maximum numeric value for event
	 * @param int|null $icon_id Optional id specifying what icon is displayed next to the event in the SteelSeries Engine UI (see ss_icons constants).
	 * @param bool|null $value_optional If the value_optional key is set to true for an event, the handlers for the event will be processed each time it is updated, even if a value key is not specified in the data or if the value key matches the previously cached value.
	 * This is mainly useful for events that use context data rather than the event value to determine what to display, such as some OLED screen events or for bitmap type lighting events.
	 * @return array
	 */
	public function register_game_event(string $event, ?int $min_value = null, ?int $max_value = null, ?int $icon_id = null, ?bool $value_optional = null):array {
		$this->curl_init("{$this->host}:{$this->port}/register_game_event");
		$payload['game'] = $this->game;
		$payload['event'] = $event;
		if (null !== $min_value) $payload['min_value'] = $min_value;
		if (null !== $max_value) $payload['max_value'] = $max_value;
		if (null !== $icon_id) $payload['icon_id'] = $icon_id;
		if (null !== $value_optional) $payload['value_optional'] = $value_optional;
		$this->curl_post($payload);
		return $this->curl_exec();
	}

	/**
	 * @param string $url
	 */
	private function curl_init(string $url):void {
		$this->_curl_handle = curl_init();
		curl_setopt($this->_curl_handle, CURLOPT_URL, $url);
		curl_setopt($this->_curl_handle, CURLOPT_REFERER, $url);
		curl_setopt($this->_curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');
		curl_setopt($this->_curl_handle, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->_curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->_curl_handle, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($this->_curl_handle, CURLOPT_COOKIEFILE, $this->_cookie);
		curl_setopt($this->_curl_handle, CURLOPT_COOKIEJAR, $this->_cookie);
		curl_setopt($this->_curl_handle, CURLOPT_FOLLOWLOCATION, true);
	}

	/**
	 * @param array $post_data
	 */
	private function curl_post(array $post_data):void {
		curl_setopt($this->_curl_handle, CURLOPT_HTTPHEADER, ['Steel Series API PHP', 'Content-Type: application/json']);
		curl_setopt($this->_curl_handle, CURLOPT_POST, true);
		curl_setopt($this->_curl_handle, CURLOPT_POSTFIELDS, json_encode($post_data));
	}

	/**
	 * @return array
	 */
	private function curl_exec():array {
		$result_ = curl_exec($this->_curl_handle);
		$status = curl_errno($this->_curl_handle);
		curl_close($this->_curl_handle);
		if ($status == 0 && !empty($result_)) {
			return [
				"success" => true,
				"result" => $result_
			];
		}

		return [
			"success" => false,
			"result" => $status
		];
	}

}