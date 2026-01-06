<?php

/** @var yii\web\View $this */

$this->title = 'Gestão Documental';
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
                            <h1 class="m-0" style="color: var(--dark-color); font-weight: 700;">Gestão Documental</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <button class="btn btn-primary">
                                <i class="fas fa-upload mr-2"></i>Upload Documento
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="small-box" style="background-color: var(--primary-color); color: white;">
                                <div class="inner">
                                    <h3>523</h3>
                                    <p>Total de Documentos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>487</h3>
                                    <p>Válidos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>28</h3>
                                    <p>Próximos do Vencimento</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>8</h3>
                                    <p>Expirados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" placeholder="Procurar documentos...">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control">
                                        <option>Todos os Tipos</option>
                                        <option>Seguro</option>
                                        <option>Inspeção</option>
                                        <option>DUA</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control">
                                        <option>Todos os Estados</option>
                                        <option>Válido</option>
                                        <option>Próximo Vencimento</option>
                                        <option>Expirado</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-secondary btn-block"><i class="fas fa-filter mr-2"></i>Filtrar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Documentos do Sistema</h3>
                            <div class="card-tools">
                                <span class="badge badge-primary">523 Documentos</span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Tipo</th>
                                        <th>Veículo/Pessoa</th>
                                        <th>Válido até</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Seguro ABC-1234</td>
                                        <td>Seguro Automóvel</td>
                                        <td>ABC-1234</td>
                                        <td>15/10/2026</td>
                                        <td><span class="badge badge-success">Válido</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="Descarregar"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Inspeção DEF-5678</td>
                                        <td>Inspeção Técnica</td>
                                        <td>DEF-5678</td>
                                        <td>20/11/2025</td>
                                        <td><span class="badge badge-warning">Próximo Vencimento</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="Descarregar"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>DUA GHI-9012</td>
                                        <td>Declaração de Uso</td>
                                        <td>GHI-9012</td>
                                        <td>05/09/2025</td>
                                        <td><span class="badge badge-danger">Expirado</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="Descarregar"><i class="fas fa-download"></i></button>
                                            <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>