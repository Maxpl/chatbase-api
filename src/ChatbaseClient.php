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

class ChatbaseClient  extends \yii\base\Component
{
    /**
     * Version
     *
     * @var string
     */
    public $version = '0.1.6';

    /**
     * chatbase.com API URL
     *
     * @var string
     */
    public $api_uri = 'https://chatbase.com/api';

    /**
     * Chatbase bot api key
     *
     * @var string
     */
    public $api_key = '';
    
    /**
     * Command
     * @var string
     */
    public $command = '';
    
    /**
     * Platform
     * @var string
     */
    public $platform = 'messenger';

    /**
     * Guzzle Client object
     *
     * @var \GuzzleHttp\Client
     */
    private static $client;
    
    /**
     * Initialize Chatbase
     *
     * @param  string $key
     * @param  array  $options
     *
     * @throws \Exception
     */
    public function init($key = '', array $options = [])
    {
        if (empty($key) && empty($this->api_key)) {
            return $this;
        }

        if ($key) {
            $this->setApiKey($key);
        }
        
        $options_default = [
            'timeout' => 3,
        ];

        $options = array_merge($options_default, $options);

        if (!is_numeric($options['timeout'])) {
            throw new \Exception('Timeout must be a number!');
        }

        self::$client = new Client(['timeout' => $options['timeout']]);

        return $this;
        
    }
    
    /**
     * Send Message
     *
     * @param array $params ['user_id' => int, 'message' => string, 'session' => optional]
     *
     * @return bool|string
     * @throws \Exception GuzzleHttp\Exception\RequestException
     */
    public function send(array $params)
    {
        if (!isset($params['command'])) {
            $params['command'] = $this->command;
        }
        
        $command = strtolower($params['command']);
        
        unset($params['command']);

        if (empty($this->api_key)) {
            return 'api key is empty!';
        }

        $data = [
            'api_key' => $this->api_key,
            'type' => 'user',
            'user_id' => 1,
            'time_stamp' => $this->getTimeStamp(),
            'platform' => $this->platform,
            //'message' => '',
            'version' => $this->version,
            //'session_id' => null,
        ];

        $data = array_merge($data, $params);
        
        if ($data['type'] == 'user') {
            $data['intent'] = $command;
        }

        try {
            $response = self::$client->post(
                $this->api_uri . '/message',
                [
                    'headers' => [
                        'cache-control' => 'no-cache',
                        'content-type' => 'application/json',
                    ],
                    'json'    => $data,
                ]
            );

            $result = $response->getBody()->getContents();
            
        } catch (RequestException $e) {
            $result = $e->getMessage();
            if (YII_DEBUG)
                \Yii::info($result);
        }

        $responseData = json_decode($result, true);

        if (!$responseData || $responseData['status'] !== 200) {
            //Log message_id

            return false;
        }

        return $responseData;
    }
    
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        
        return $this->api_key;
    }
    
    public function setCommand($command)
    {
        $this->command = $command;
        
        return $this->command;
    }
    
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        
        return $this->platform;
    }
    
    /**
     * Convert current microtime * 1000 without dot
     * @return string
     */
    private function getTimeStamp()
    {
        $microtime = round(microtime(true) * 1000);
        
        return number_format($microtime, 0, ".", "");
    }

}