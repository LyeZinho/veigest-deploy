<?php

namespace backend\modules\api;

use yii\base\Module as BaseModule;
use yii\web\Response;

/**
 * VeiGest API Main Module
 * 
 * Módulo principal da API RESTful para gestão de veículos
 * Fornece versionamento e configurações globais
 * 
 * .
 * @version 1.0
 */
class Module extends BaseModule
{
    public $controllerNamespace = 'backend\modules\api\controllers';
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // Configurações globais da API
        \Yii::$app->response->format = Response::FORMAT_JSON;
        \Yii::$app->user->enableSession = false;
        \Yii::$app->user->loginUrl = null;
        
        // Configurar CORS globalmente
        \Yii::$app->response->on(\yii\web\Response::EVENT_BEFORE_SEND, function ($event) {
            $response = $event->sender;
            $response->headers->add('Access-Control-Allow-Origin', '*');
            $response->headers->add('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->add('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With');
            $response->headers->add('Access-Control-Max-Age', '3600');
        });
    }
}
