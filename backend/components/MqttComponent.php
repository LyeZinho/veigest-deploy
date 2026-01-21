<?php

namespace backend\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * MQTT Component for Yii2
 * 
 * Provides connectivity to Eclipse Mosquitto broker for real-time alert messaging.
 * 
 * Configuration example in backend/config/main.php:
 * ```php
 * 'components' => [
 *     'mqtt' => [
 *         'class' => 'backend\components\MqttComponent',
 *         'host' => 'mosquitto', // Docker service name or localhost
 *         'port' => 1883,
 *         'clientId' => 'veigest-backend',
 *     ],
 * ],
 * ```
 * 
 * Usage:
 * ```php
 * // Publish an alert
 * Yii::$app->mqtt->publish('alerts/critical', json_encode([
 *     'type' => 'maintenance',
 *     'title' => 'Urgent Maintenance Required',
 *     'vehicle_id' => 5,
 * ]));
 * 
 * // Subscribe to alerts (typically in console command)
 * Yii::$app->mqtt->subscribe('alerts/#', function($topic, $message) {
 *     echo "Received: $topic => $message\n";
 * });
 * ```
 */
class MqttComponent extends Component
{
    /**
     * @var string MQTT broker host
     */
    public $host = 'localhost';

    /**
     * @var int MQTT broker port
     */
    public $port = 1883;

    /**
     * @var string Client ID for MQTT connection
     */
    public $clientId = 'veigest-api';

    /**
     * @var string|null Username for authentication
     */
    public $username = null;

    /**
     * @var string|null Password for authentication
     */
    public $password = null;

    /**
     * @var int Keep alive interval in seconds
     */
    public $keepAlive = 60;

    /**
     * @var resource|null Socket connection
     */
    private $_socket = null;

    /**
     * @var bool Connection status
     */
    private $_connected = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        if (empty($this->host)) {
            throw new InvalidConfigException('MQTT host must be configured.');
        }

        if (empty($this->clientId)) {
            $this->clientId = 'veigest-' . uniqid();
        }
    }

    /**
     * Connect to MQTT broker
     * 
     * @return bool
     * @throws \Exception
     */
    public function connect()
    {
        if ($this->_connected) {
            return true;
        }

        try {
            $this->_socket = @fsockopen($this->host, $this->port, $errno, $errstr, 5);
            
            if (!$this->_socket) {
                Yii::error("MQTT Connection Failed: $errstr ($errno)", __METHOD__);
                return false;
            }

            stream_set_timeout($this->_socket, 5);
            stream_set_blocking($this->_socket, false);

            // Send CONNECT packet
            $packet = $this->buildConnectPacket();
            fwrite($this->_socket, $packet);

            // Wait for CONNACK
            $response = $this->readPacket();
            
            if ($response && ord($response[0]) >> 4 === 2) { // CONNACK = 2
                $this->_connected = true;
                Yii::info("MQTT Connected to {$this->host}:{$this->port}", __METHOD__);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Yii::error("MQTT Connection Exception: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Disconnect from MQTT broker
     */
    public function disconnect()
    {
        if ($this->_socket && $this->_connected) {
            // Send DISCONNECT packet (0xE0, 0x00)
            fwrite($this->_socket, chr(0xE0) . chr(0x00));
            fclose($this->_socket);
            $this->_socket = null;
            $this->_connected = false;
            Yii::info("MQTT Disconnected", __METHOD__);
        }
    }

    /**
     * Publish a message to a topic
     * 
     * @param string $topic Topic name
     * @param string $message Message payload
     * @param int $qos Quality of Service (0, 1, or 2)
     * @param bool $retain Retain message flag
     * @return bool
     */
    public function publish($topic, $message, $qos = 0, $retain = false)
    {
        if (!$this->_connected && !$this->connect()) {
            Yii::error("MQTT Publish Failed: Not connected", __METHOD__);
            return false;
        }

        try {
            $packet = $this->buildPublishPacket($topic, $message, $qos, $retain);
            $written = fwrite($this->_socket, $packet);
            
            if ($written === false || $written === 0) {
                Yii::error("MQTT Publish Failed: Write error", __METHOD__);
                $this->_connected = false;
                return false;
            }

            Yii::info("MQTT Published to '$topic': " . substr($message, 0, 100), __METHOD__);
            return true;

        } catch (\Exception $e) {
            Yii::error("MQTT Publish Exception: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Subscribe to a topic
     * 
     * @param string $topic Topic pattern (supports wildcards: + and #)
     * @param callable|null $callback Callback function to handle messages
     * @param int $qos Quality of Service
     * @return bool
     */
    public function subscribe($topic, $callback = null, $qos = 0)
    {
        if (!$this->_connected && !$this->connect()) {
            return false;
        }

        try {
            $packet = $this->buildSubscribePacket($topic, $qos);
            fwrite($this->_socket, $packet);

            // Wait for SUBACK
            $response = $this->readPacket();
            
            if ($response && ord($response[0]) >> 4 === 9) { // SUBACK = 9
                Yii::info("MQTT Subscribed to '$topic'", __METHOD__);
                
                // If callback provided, start listening
                if ($callback && is_callable($callback)) {
                    $this->listen($callback);
                }
                
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Yii::error("MQTT Subscribe Exception: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Listen for incoming messages (blocking)
     * 
     * @param callable $callback Function to handle messages: function($topic, $message)
     * @param int $timeout Maximum time to listen (0 = infinite)
     */
    public function listen($callback, $timeout = 0)
    {
        $start = time();
        
        while ($this->_connected) {
            if ($timeout > 0 && (time() - $start) >= $timeout) {
                break;
            }

            $packet = $this->readPacket(1); // 1 second timeout
            
            if ($packet) {
                $type = ord($packet[0]) >> 4;
                
                if ($type === 3) { // PUBLISH = 3
                    list($topic, $message) = $this->parsePublishPacket($packet);
                    
                    if ($topic && $message !== false) {
                        call_user_func($callback, $topic, $message);
                    }
                } elseif ($type === 13) { // PINGRESP = 13
                    // Keep-alive response, continue
                    continue;
                }
            }

            // Send PINGREQ every keep-alive interval
            if ((time() % $this->keepAlive) === 0) {
                fwrite($this->_socket, chr(0xC0) . chr(0x00));
            }
        }
    }

    /**
     * Build MQTT CONNECT packet
     * 
     * @return string
     */
    private function buildConnectPacket()
    {
        $protocol = 'MQTT';
        $version = chr(4); // MQTT 3.1.1

        $flags = 0x02; // Clean session
        if ($this->username) {
            $flags |= 0x80; // Username flag
        }
        if ($this->password) {
            $flags |= 0x40; // Password flag
        }

        $payload = $this->encodeString($this->clientId);
        
        if ($this->username) {
            $payload .= $this->encodeString($this->username);
        }
        if ($this->password) {
            $payload .= $this->encodeString($this->password);
        }

        $variableHeader = $this->encodeString($protocol) . $version . chr($flags) . pack('n', $this->keepAlive);
        
        $remainingLength = strlen($variableHeader) + strlen($payload);
        
        return chr(0x10) . $this->encodeLength($remainingLength) . $variableHeader . $payload;
    }

    /**
     * Build MQTT PUBLISH packet
     * 
     * @param string $topic
     * @param string $message
     * @param int $qos
     * @param bool $retain
     * @return string
     */
    private function buildPublishPacket($topic, $message, $qos = 0, $retain = false)
    {
        $cmd = 0x30; // PUBLISH
        if ($retain) {
            $cmd |= 0x01;
        }
        $cmd |= ($qos << 1);

        $variableHeader = $this->encodeString($topic);
        
        if ($qos > 0) {
            $variableHeader .= pack('n', 1); // Message ID
        }

        $remainingLength = strlen($variableHeader) + strlen($message);
        
        return chr($cmd) . $this->encodeLength($remainingLength) . $variableHeader . $message;
    }

    /**
     * Build MQTT SUBSCRIBE packet
     * 
     * @param string $topic
     * @param int $qos
     * @return string
     */
    private function buildSubscribePacket($topic, $qos = 0)
    {
        $cmd = 0x82; // SUBSCRIBE with QoS 1
        $messageId = 1;

        $payload = $this->encodeString($topic) . chr($qos);
        $variableHeader = pack('n', $messageId);
        
        $remainingLength = strlen($variableHeader) + strlen($payload);
        
        return chr($cmd) . $this->encodeLength($remainingLength) . $variableHeader . $payload;
    }

    /**
     * Read MQTT packet from socket
     * 
     * @param int $timeout Timeout in seconds
     * @return string|false
     */
    private function readPacket($timeout = 5)
    {
        if (!$this->_socket) {
            return false;
        }

        $start = microtime(true);
        
        while ((microtime(true) - $start) < $timeout) {
            $byte = fread($this->_socket, 1);
            
            if ($byte === false || $byte === '') {
                usleep(10000); // 10ms
                continue;
            }

            // Read remaining length
            $multiplier = 1;
            $length = 0;
            
            do {
                $digit = fread($this->_socket, 1);
                if ($digit === false || $digit === '') {
                    return false;
                }
                
                $value = ord($digit);
                $length += ($value & 127) * $multiplier;
                $multiplier *= 128;
            } while (($value & 128) != 0);

            // Read remaining packet
            $packet = $byte . fread($this->_socket, $length + 1);
            
            return $packet;
        }

        return false;
    }

    /**
     * Parse MQTT PUBLISH packet
     * 
     * @param string $packet
     * @return array [topic, message]
     */
    private function parsePublishPacket($packet)
    {
        $pos = 1; // Skip fixed header
        
        // Skip remaining length bytes
        do {
            $byte = ord($packet[$pos++]);
        } while (($byte & 128) != 0);

        // Read topic length
        $topicLen = unpack('n', substr($packet, $pos, 2))[1];
        $pos += 2;

        // Read topic
        $topic = substr($packet, $pos, $topicLen);
        $pos += $topicLen;

        // Read message (rest of packet)
        $message = substr($packet, $pos);

        return [$topic, $message];
    }

    /**
     * Encode string for MQTT
     * 
     * @param string $str
     * @return string
     */
    private function encodeString($str)
    {
        return pack('n', strlen($str)) . $str;
    }

    /**
     * Encode remaining length for MQTT
     * 
     * @param int $length
     * @return string
     */
    private function encodeLength($length)
    {
        $encoded = '';
        
        do {
            $digit = $length % 128;
            $length = intdiv($length, 128);
            
            if ($length > 0) {
                $digit |= 0x80;
            }
            
            $encoded .= chr($digit);
        } while ($length > 0);

        return $encoded;
    }

    /**
     * Check if connected
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->_connected;
    }

    /**
     * Destructor - ensure clean disconnect
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
