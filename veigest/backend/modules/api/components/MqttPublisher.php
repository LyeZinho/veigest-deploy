<?php

namespace backend\modules\api\components;

use Yii;
use yii\base\Component;

/**
 * MqttPublisher - Componente para publicação de mensagens MQTT
 * 
 * Publica alertas no broker Mosquitto para atualização dinâmica
 * dos clientes (apps Android, dashboards, etc.)
 * 
 * Canais disponíveis:
 * - veigest/alerts/{company_id}          - Todos os alertas de uma empresa
 * - veigest/alerts/{company_id}/critical - Alertas críticos
 * - veigest/alerts/{company_id}/high     - Alertas de alta prioridade
 * - veigest/alerts/{company_id}/new      - Novos alertas criados
 * - veigest/alerts/{company_id}/resolved - Alertas resolvidos
 * 
 * @author VeiGest Team
 * @version 1.0
 */
class MqttPublisher extends Component
{
    /**
     * @var string Host do broker MQTT (Mosquitto)
     * 'localhost' para API rodando fora do Docker
     * 'mosquitto' para API rodando dentro do Docker
     */
    public $host = 'localhost';
    
    /**
     * @var int Porta do broker MQTT
     */
    public $port = 1883;
    
    /**
     * @var string Client ID para identificação
     */
    public $clientId = 'veigest-api';
    
    /**
     * @var int Timeout de conexão em segundos
     */
    public $timeout = 5;
    
    /**
     * @var resource Socket connection
     */
    private $socket;
    
    /**
     * @var bool Estado da conexão
     */
    private $connected = false;

    /**
     * Prefixo base dos tópicos
     */
    const TOPIC_PREFIX = 'veigest/alerts';

    /**
     * Tipos de eventos para publicação
     */
    const EVENT_NEW = 'new';
    const EVENT_RESOLVED = 'resolved';
    const EVENT_IGNORED = 'ignored';
    const EVENT_UPDATED = 'updated';

    /**
     * Conectar ao broker MQTT
     * 
     * @return bool True se conectado com sucesso
     */
    public function connect()
    {
        if ($this->connected) {
            return true;
        }

        try {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            
            if (!$this->socket) {
                Yii::warning("MQTT: Não foi possível conectar ao broker - $errstr ($errno)", 'mqtt');
                return false;
            }

            // Enviar pacote CONNECT do protocolo MQTT
            $connectPacket = $this->buildConnectPacket();
            fwrite($this->socket, $connectPacket);

            // Ler resposta CONNACK
            $response = fread($this->socket, 4);
            if (strlen($response) >= 4 && ord($response[0]) === 0x20 && ord($response[3]) === 0x00) {
                $this->connected = true;
                Yii::info("MQTT: Conectado ao broker {$this->host}:{$this->port}", 'mqtt');
                return true;
            }

            Yii::warning("MQTT: Falha na autenticação com o broker", 'mqtt');
            $this->disconnect();
            return false;

        } catch (\Exception $e) {
            Yii::error("MQTT: Erro de conexão - " . $e->getMessage(), 'mqtt');
            return false;
        }
    }

    /**
     * Desconectar do broker MQTT
     */
    public function disconnect()
    {
        if ($this->socket) {
            // Enviar pacote DISCONNECT
            fwrite($this->socket, chr(0xE0) . chr(0x00));
            fclose($this->socket);
            $this->socket = null;
        }
        $this->connected = false;
    }

    /**
     * Publicar mensagem num tópico MQTT
     * 
     * @param string $topic Tópico onde publicar
     * @param array|string $message Mensagem a publicar (será convertida para JSON se array)
     * @param int $qos Quality of Service (0, 1 ou 2) - default 0
     * @return bool True se publicado com sucesso
     */
    public function publish($topic, $message, $qos = 0)
    {
        if (!$this->connect()) {
            // Se não conseguir conectar, registar mas não bloquear a aplicação
            Yii::warning("MQTT: Mensagem não publicada (broker indisponível) - Topic: $topic", 'mqtt');
            return false;
        }

        try {
            // Converter array para JSON
            if (is_array($message)) {
                $message = json_encode($message, JSON_UNESCAPED_UNICODE);
            }

            // Construir pacote PUBLISH
            $publishPacket = $this->buildPublishPacket($topic, $message, $qos);
            $result = fwrite($this->socket, $publishPacket);

            if ($result) {
                Yii::info("MQTT: Mensagem publicada - Topic: $topic", 'mqtt');
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Yii::error("MQTT: Erro ao publicar - " . $e->getMessage(), 'mqtt');
            return false;
        }
    }

    /**
     * Publicar alerta num canal específico da empresa
     * 
     * @param int $companyId ID da empresa
     * @param array $alertData Dados do alerta
     * @param string $event Tipo de evento (new, resolved, ignored, updated)
     * @return bool
     */
    public function publishAlert($companyId, $alertData, $event = self::EVENT_NEW)
    {
        // Tópico principal da empresa
        $baseTopic = self::TOPIC_PREFIX . "/{$companyId}";
        
        // Preparar payload
        $payload = [
            'event' => $event,
            'timestamp' => date('c'),
            'data' => $alertData,
        ];

        $results = [];

        // Publicar no canal principal
        $results[] = $this->publish($baseTopic, $payload);

        // Publicar no canal do evento específico
        $results[] = $this->publish("{$baseTopic}/{$event}", $payload);

        // Se for alerta crítico ou high, publicar também no canal de prioridade
        if (isset($alertData['priority'])) {
            $priority = $alertData['priority'];
            if (in_array($priority, ['critical', 'high'])) {
                $results[] = $this->publish("{$baseTopic}/{$priority}", $payload);
            }
        }

        return !in_array(false, $results, true);
    }

    /**
     * Publicar notificação de novo alerta
     * 
     * @param int $companyId ID da empresa
     * @param array $alertData Dados do alerta
     * @return bool
     */
    public function publishNewAlert($companyId, $alertData)
    {
        return $this->publishAlert($companyId, $alertData, self::EVENT_NEW);
    }

    /**
     * Publicar notificação de alerta resolvido
     * 
     * @param int $companyId ID da empresa
     * @param array $alertData Dados do alerta
     * @return bool
     */
    public function publishResolvedAlert($companyId, $alertData)
    {
        return $this->publishAlert($companyId, $alertData, self::EVENT_RESOLVED);
    }

    /**
     * Construir pacote CONNECT MQTT
     * 
     * @return string Pacote binário
     */
    private function buildConnectPacket()
    {
        $clientId = $this->clientId . '-' . uniqid();
        
        // Variable header
        $buffer = '';
        $buffer .= chr(0x00) . chr(0x04) . 'MQTT';  // Protocol Name
        $buffer .= chr(0x04);                        // Protocol Level (MQTT 3.1.1)
        $buffer .= chr(0x02);                        // Connect Flags (Clean Session)
        $buffer .= chr(0x00) . chr(0x3C);           // Keep Alive (60 seconds)
        
        // Payload - Client ID
        $buffer .= chr(0x00) . chr(strlen($clientId)) . $clientId;
        
        // Fixed header
        $header = chr(0x10);  // CONNECT packet type
        $header .= $this->encodeLength(strlen($buffer));
        
        return $header . $buffer;
    }

    /**
     * Construir pacote PUBLISH MQTT
     * 
     * @param string $topic Tópico
     * @param string $message Mensagem
     * @param int $qos QoS level
     * @return string Pacote binário
     */
    private function buildPublishPacket($topic, $message, $qos = 0)
    {
        // Variable header
        $buffer = '';
        $buffer .= chr(0x00) . chr(strlen($topic)) . $topic;  // Topic
        
        // Payload
        $buffer .= $message;
        
        // Fixed header
        $cmd = 0x30;  // PUBLISH packet type
        if ($qos > 0) {
            $cmd |= ($qos << 1);
        }
        
        $header = chr($cmd);
        $header .= $this->encodeLength(strlen($buffer));
        
        return $header . $buffer;
    }

    /**
     * Codificar comprimento no formato MQTT
     * 
     * @param int $length Comprimento a codificar
     * @return string Bytes codificados
     */
    private function encodeLength($length)
    {
        $string = '';
        do {
            $digit = $length % 128;
            $length = $length >> 7;
            if ($length > 0) {
                $digit |= 0x80;
            }
            $string .= chr($digit);
        } while ($length > 0);
        
        return $string;
    }

    /**
     * Destrutor - garantir desconexão
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
