<?php

/** @var yii\web\View $this */

$this->title = 'Alertas do Sistema';
?>
<body class="hold-transition sidebar-mini layout-fixed">
<div>

    
    <!-- Content -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Alertas do Sistema</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.html">Início</a></li>
                            <li class="breadcrumb-item active">Alertas</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="mb-3">
                    <button class="btn btn-danger">Críticos (3)</button>
                    <button class="btn btn-warning">Altos (4)</button>
                    <button class="btn btn-info">Médios (5)</button>
                    <button class="btn btn-success">Resolvidos (45)</button>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Alertas Ativos</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert-item alert-critica">
                            <h5><i class="fas fa-exclamation-circle mr-2"></i>Documentação Expirada</h5>
                            <p>Seguro do veículo ABC-1234 expirou a 15/10/2025</p>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-check mr-1"></i>Resolver</button>
                        </div>
                        
                        <div class="alert-item alert-critica">
                            <h5><i class="fas fa-exclamation-triangle mr-2"></i>Inspeção Periódica Vencida</h5>
                            <p>Inspeção do veículo DEF-5678 venceu a 30/09/2025</p>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-check mr-1"></i>Resolver</button>
                        </div>
                        
                        <div class="alert-item alert-alta">
                            <h5><i class="fas fa-calendar mr-2"></i>Manutenção Programada Próxima</h5>
                            <p>Revisão do veículo XYZ-9012 vence em 3 dias (10/11/2025)</p>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-calendar mr-1"></i>Agendar</button>
                        </div>
                        
                        <div class="alert-item alert-media">
                            <h5><i class="fas fa-info-circle mr-2"></i>Consumo de Combustível Anormal</h5>
                            <p>Veículo GHI-3456 apresenta consumo 18% acima da média esperada</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>