<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \frontend\models\TicketForm $model */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Sistema de Tickets';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="bg-gray-50">

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-primary to-blue-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-black">Tickets de Suporte</h1>
            <p class="text-lg text-opacity-90 text-black">Crie um ticket para receber assistência personalizada da nossa equipa</p>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Introduction Description -->
            <div class="text-center mb-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Como Podemos Ajudar?</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    O nosso sistema de tickets permite que receba suporte personalizado e eficiente.
                    Selecione o tipo de assistência que precisa ou crie um novo ticket descrevendo o seu problema detalhadamente.
                    A nossa equipa especializada está pronta para ajudar!
                </p>
            </div>

            <!-- Ticket Form -->
            <div class="bg-white rounded-lg shadow-md p-8 max-w-2xl mx-auto mb-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Criar Novo Ticket</h2>
                <?php $form = ActiveForm::begin(['id' => 'ticket-form']); ?>
                <div class="space-y-4">
                    <?= $form->field($model, 'name')->textInput([
                        'autofocus' => true,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'O seu nome completo'
                    ])->label('Nome', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'email')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'seu.email@empresa.com'
                    ])->label('Email', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <div class="grid md:grid-cols-2 gap-4">
                        <?= $form->field($model, 'priority')->dropDownList([
                            'low' => 'Baixa',
                            'medium' => 'Média',
                            'high' => 'Alta',
                            'urgent' => 'Urgente'
                        ], [
                            'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                            'prompt' => 'Selecione a prioridade'
                        ])->label('Prioridade', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                        <?= $form->field($model, 'category')->dropDownList([
                            'technical' => 'Problema Técnico',
                            'billing' => 'Faturação',
                            'account' => 'Conta/Utilizador',
                            'feature' => 'Solicitação de Funcionalidade',
                            'training' => 'Formação',
                            'other' => 'Outro'
                        ], [
                            'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                            'prompt' => 'Selecione a categoria'
                        ])->label('Categoria', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>
                    </div>

                    <?= $form->field($model, 'subject')->textInput([
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Título breve do problema'
                    ])->label('Assunto', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?= $form->field($model, 'body')->textarea([
                        'rows' => 6,
                        'class' => 'w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary',
                        'placeholder' => 'Descreva detalhadamente o seu problema ou solicitação'
                    ])->label('Descrição', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>

                    <?php if (Yii::$app->user->isGuest): ?>
                    <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                        'template' => '<div class="mb-4"><div class="flex items-center space-x-4">{image}{input}</div></div>',
                        'options' => ['class' => 'px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:border-primary']
                    ])->label('Código de Verificação', ['class' => 'block text-sm font-medium text-gray-700 mb-2']) ?>
                    <?php endif; ?>

                    <div class="form-group">
                        <?= Html::submitButton('Criar Ticket', [
                            'class' => 'w-full bg-primary text-white py-3 rounded-lg font-bold hover:bg-opacity-90 transition',
                            'name' => 'ticket-button'
                        ]) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-ticket-alt text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Criar Ticket</h3>
                    <p class="text-gray-600 mb-4">Abra um ticket detalhado para suporte técnico</p>
                    <span class="text-primary font-medium">Está aqui</span>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-clock text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Acompanhamento</h3>
                    <p class="text-gray-600 mb-4">Acompanhe o estado do seu ticket em tempo real</p>
                    <?php if (!Yii::$app->user->isGuest): ?>
                    <a href="<?= \yii\helpers\Url::to(['site/my-tickets']) ?>" class="text-primary hover:underline font-medium">Ver Meus Tickets</a>
                    <?php else: ?>
                    <a href="<?= \yii\helpers\Url::to(['site/login']) ?>" class="text-primary hover:underline font-medium">Iniciar Sessão</a>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-lg shadow-md p-8 text-center hover:shadow-lg transition">
                    <i class="fas fa-question-circle text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">FAQ</h3>
                    <p class="text-gray-600 mb-4">Consulte as nossas perguntas frequentes</p>
                    <a href="#faq" class="text-primary hover:underline font-medium">Ver FAQ</a>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-16 pt-16 border-t border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Perguntas Frequentes sobre Tickets</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Quanto tempo leva para responder?</h3>
                            <p class="text-gray-600">Tickets urgentes são respondidos em até 2 horas. Tickets de alta prioridade em até 4 horas. Outros tickets são respondidos em até 24 horas úteis.</p>
                        </div>
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Posso acompanhar o status do meu ticket?</h3>
                            <p class="text-gray-600">Sim! Receberá atualizações por email sobre o progresso do seu ticket e pode verificar o estado a qualquer momento.</p>
                        </div>
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Como escolher a prioridade correta?</h3>
                            <p class="text-gray-600">Urgente: Sistema indisponível. Alta: Funcionalidade crítica afetada. Média: Problema não crítico. Baixa: Sugestão ou dúvida geral.</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Posso anexar ficheiros?</h3>
                            <p class="text-gray-600">Atualmente, os tickets são baseados em texto. Para partilhar ficheiros, mencione no ticket e a nossa equipa solicitará os ficheiros necessários.</p>
                        </div>
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">O suporte é 24/7?</h3>
                            <p class="text-gray-600">O suporte técnico funciona de segunda a sexta, das 9h às 18h. Para emergências fora do horário comercial, use a prioridade "Urgente".</p>
                        </div>
                        <div class="bg-white rounded-lg p-6 shadow-md">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Posso reabrir um ticket fechado?</h3>
                            <p class="text-gray-600">Sim, pode criar um novo ticket referenciando o número do ticket anterior se o problema persistir.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Status Guide -->
            <div class="mt-16 pt-16 border-t border-gray-200 bg-gray-50 rounded-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Status dos Tickets</h2>
                <div class="grid md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-blue-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Aberto</h3>
                        <p class="text-gray-600 text-sm">Ticket criado e aguardando análise inicial</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-yellow-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-cog text-yellow-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Em Análise</h3>
                        <p class="text-gray-600 text-sm">A nossa equipa está a investigar o problema</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-orange-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-tools text-orange-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Em Andamento</h3>
                        <p class="text-gray-600 text-sm">A solução está a ser implementada</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Resolvido</h3>
                        <p class="text-gray-600 text-sm">Problema foi solucionado com sucesso</p>
                    </div>
                </div>
            </div>

            <!-- Tips for Better Tickets -->
            <div class="mt-16 pt-16 border-t border-gray-200">
                <div class="grid md:grid-cols-2 gap-12">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">Dicas para Tickets Eficazes</h2>
                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-lightbulb text-primary text-xl mt-1"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Seja específico</h3>
                                    <p class="text-gray-600">Descreva exatamente o que está a acontecer, incluindo mensagens de erro e passos para reproduzir o problema.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-list text-primary text-xl mt-1"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Forneça contexto</h3>
                                    <p class="text-gray-600">Inclua informações sobre o seu navegador, dispositivo e quando o problema começou a ocorrer.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-images text-primary text-xl mt-1"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Capturas de tela</h3>
                                    <p class="text-gray-600">Quando possível, inclua capturas de tela que mostrem o problema ou comportamento inesperado.</p>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-search text-primary text-xl mt-1"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">Verifique a documentação</h3>
                                    <p class="text-gray-600">Consulte a nossa base de conhecimento antes de criar um ticket - muitas dúvidas já estão respondidas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8">Canais de Suporte Alternativos</h2>
                        <div class="space-y-6">
                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <div class="flex items-center gap-4 mb-3">
                                    <i class="fas fa-book text-primary text-2xl"></i>
                                    <h3 class="text-xl font-bold text-gray-900">Base de Conhecimento</h3>
                                </div>
                                <p class="text-gray-600 mb-4">Aceda à nossa documentação completa com guias, tutoriais e soluções para problemas comuns.</p>
                                <a href="#" class="text-primary hover:underline font-medium">Aceder à Documentação</a>
                            </div>

                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <div class="flex items-center gap-4 mb-3">
                                    <i class="fas fa-users text-primary text-2xl"></i>
                                    <h3 class="text-xl font-bold text-gray-900">Comunidade</h3>
                                </div>
                                <p class="text-gray-600 mb-4">Participe do nosso fórum da comunidade para trocar experiências com outros utilizadores.</p>
                                <a href="#" class="text-primary hover:underline font-medium">Aceder à Comunidade</a>
                            </div>

                            <div class="bg-white rounded-lg p-6 shadow-md">
                                <div class="flex items-center gap-4 mb-3">
                                    <i class="fas fa-graduation-cap text-primary text-2xl"></i>
                                    <h3 class="text-xl font-bold text-gray-900">Formações</h3>
                                </div>
                                <p class="text-gray-600 mb-4">Participe dos nossos webinars e cursos online para maximizar o uso da plataforma.</p>
                                <a href="#" class="text-primary hover:underline font-medium">Ver Formações</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Support Stats -->
            <div class="mt-16 pt-16 border-t border-gray-200 bg-gradient-to-r from-primary to-blue-500 text-white rounded-lg p-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold mb-8">O Nosso Compromisso com o Suporte</h2>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div>
                            <div class="text-4xl font-bold mb-2">98%</div>
                            <p class="text-lg opacity-90">Taxa de Satisfação</p>
                        </div>
                        <div>
                            <div class="text-4xl font-bold mb-2">2h</div>
                            <p class="text-lg opacity-90">Tempo Médio de Resposta</p>
                        </div>
                        <div>
                            <div class="text-4xl font-bold mb-2">24/7</div>
                            <p class="text-lg opacity-90">Monitorização do Sistema</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>