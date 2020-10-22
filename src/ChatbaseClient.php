<?php

/* 
 * This file is part of the ChatbaseApi package.
 *
 * (c) Maks Sloboda <msloboda@ukr.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Maxpl\ChatbaseApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChatbaseClient 
{
    /**
     * Version
     *
     * @var string
     */
    public $version = '0.1.0';

    /**
     * chatbase.com API URL
     *
     * @var string
     */
    public $api_uri = 'https://chatbase.com/api/message';

    /**
     * Chatbase bot api key
     *
     * @var string
     */
    public $api_key = '';

    /**
     * Guzzle Client object
     *
     * @var \GuzzleHttp\Client
     */
    private $client;
    
    /**
     * Command
     * @var string
     */
    public $command = '';

    /**
     * Initialize Chatbase
     *
     * @param  string $token
     * @param  array  $options
     *
     * @throws \Exception
     */
    public function init($key = '', array $options = [])
    {
        if (empty($key) && empty($this->api_key)) {
            throw new \Exception('Chatbase key is empty!');
        }

        if ($key) {
            $this->api_key = $key;
        }
        
        $options_default = [
            'timeout' => 3,
        ];

        $options = array_merge($options_default, $options);

        if (!is_numeric($options['timeout'])) {
            throw new \Exception('Timeout must be a number!');
        }

        self::$client = new Client(['base_uri' => self::$api_uri, 'timeout' => $options['timeout']]);

    }
    
    /**
     * Send Message
     *
     * @param array $params
     *
     * @return bool|string
     * @throws \Exception
     */
    public function send($params)
    {
        if (!isset($params['command'])) {
            $params['command'] = $this->command;
        }
        
        $command = strtolower($params['command']);
        
        unset($params['command']);

        if (empty($this->api_key) || !$command) {
            return false;
        }

        $data = [
            'api_key' => $this->api_key,
            'type' => 'user',
            'user_id' => 1,
            'time_stamp' => microtime(),
            'platform' => 'telegram',
            'message' => 'start dialog',
            'version' => $this->version,
            'session_id' => 1,
        ];

        $data = array_merge($data, $params);
        
        if ($data['type'] == 'user') {
            $data['intent'] = $command;
        }

        try {
            $response = self::$client->post(
            $this->api_uri,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json'    => $data,
                ]
            );

            $result = (string) $response->getBody();
        } catch (RequestException $e) {
            $result = $e->getMessage();
        }

        $responseData = json_decode($result, true);

        if (!$responseData || $responseData['status'] !== 200) {
            //Log message_id

            return false;
        }

        return $responseData;
    }
    
    public function setCommand($command)
    {
        $this->command = $command;
        
        return $this->command;
    }
}