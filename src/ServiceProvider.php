<?php

namespace UserSpace;

use UserSpace\Controller\Admin\FieldSettingsController;
use UserSpace\Controller\Admin\ProfileAdminController;
use UserSpace\Controller\Admin\RegistrationAdminController;
use UserSpace\Controller\Admin\TabsConfigAdminController;
use UserSpace\Controller\Admin\SettingsAdminController;
use UserSpace\Controller\Admin\TabSettingsController;
use UserSpace\Controller\FileUploaderController;
use UserSpace\Controller\GridController;
use UserSpace\Controller\LoginController;
use UserSpace\Controller\ModalFormController;
use UserSpace\Controller\PasswordResetController;
use UserSpace\Controller\QueueActionsController;
use UserSpace\Controller\ProfileFormController;
use UserSpace\Controller\RegistrationController;
use UserSpace\Controller\TabContentController;
use UserSpace\Controller\UserController;
use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Cron\CronManager;
use UserSpace\Core\Database\QueryBuilder;
use UserSpace\Core\Process\BackgroundProcessManager;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Queue\QueueManager;
use UserSpace\Core\Queue\QueueStatus;
use UserSpace\Core\Queue\Repository\JobRepository;
use UserSpace\Core\Queue\Repository\JobRepositoryInterface;
use UserSpace\Core\Rest\Helper\RestHelper;
use UserSpace\Core\Rest\RestApi;
use UserSpace\Core\Rest\Route\RouteCollector;
use UserSpace\Core\Rest\Route\RouteParser;
use UserSpace\Core\SetupWizard\SetupWizardController;
use UserSpace\Core\SSE\Repository\SseEventRepository;
use UserSpace\Core\SSE\Repository\SseEventRepositoryInterface;
use UserSpace\Core\SSE\SseController;
use UserSpace\Core\SSE\SseManager;
use UserSpace\Core\SSE\SseManagerInterface;
use UserSpace\Form\Repository\FormRepository;
use UserSpace\Form\Repository\FormRepositoryInterface;
use UserSpace\JobHandler\Message\PingMessage;
use UserSpace\JobHandler\Message\SendWelcomeEmailMessage;
use UserSpace\JobHandler\PingHandler;
use UserSpace\JobHandler\SendWelcomeEmailHandler;

/**
 * Регистрирует сервисы плагина в DI-контейнере.
 * Все сервисы собирать не нужно, работает autowiring
 */
class ServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        // Основной класс плагина
        $container->set(Plugin::class, fn () => Plugin::getInstance());

        // Регистрируем сам контейнер, чтобы его можно было внедрять по интерфейсу
        $container->set(ContainerInterface::class, fn () => $container);

        // HTTP
        $container->set(Request::class, fn () => Request::createFromGlobals());

        // REST API Params
        $container->set('rest.prefix', fn() => 'wp-json');
        $container->set('rest.namespace', fn() => USERSPACE_REST_NAMESPACE);

        // REST Core
        // Эти сервисы необходимы для RestApi, поэтому их нужно зарегистрировать явно.
        $container->set(RouteCollector::class, fn() => new RouteCollector());
        $container->set(RouteParser::class, fn(ContainerInterface $c) => new RouteParser($c->get(RouteCollector::class)));
        // RestHelper требует скалярные параметры, поэтому регистрируем его явно.
        $container->set(
            RestHelper::class,
            fn(ContainerInterface $c) => new RestHelper($c->get(Request::class), $c->get('rest.prefix'), $c->get('rest.namespace'))
        );

        $container->set(RestApi::class, function (ContainerInterface $c) {
            $controllers = [
                // Здесь мы вручную собираем все контроллеры, которые должны быть доступны через REST
                FieldSettingsController::class,
                ProfileAdminController::class,
                ProfileFormController::class,
                RegistrationAdminController::class,
                SettingsAdminController::class,
                TabSettingsController::class,
                TabsConfigAdminController::class,
                TabContentController::class,
                LoginController::class,
                RegistrationController::class,
                PasswordResetController::class,
                ModalFormController::class,
                FileUploaderController::class,
                UserController::class,
                GridController::class,
                SetupWizardController::class,
                QueueActionsController::class,
                SseController::class
            ];
            return new RestApi($controllers, $c->get('rest.namespace'), $c->get(RouteParser::class), $c);
        });

        // База данных
        $container->set(
            QueryBuilder::class,
            function () {
                global $wpdb;
                return new QueryBuilder($wpdb);
            }
        );

        // --- Фоновые процессы ---
        $container->set(BackgroundProcessManager::class, fn() => new BackgroundProcessManager());

        // --- Cron ---
        $container->set(CronManager::class, function (ContainerInterface $c) {
            // Сначала создаем оба менеджера
            $queueManager = $c->get(QueueManager::class);
            $cronManager = new CronManager($queueManager);
            // Затем "связываем" их, чтобы избежать циклической зависимости
            $queueManager->setCronManager($cronManager);
            return $cronManager;
        });

        // --- Формы ---
        $container->set(FormRepositoryInterface::class, fn() => new FormRepository());

        // --- SSE ---
        $container->set(SseEventRepositoryInterface::class, fn() => new SseEventRepository());
        $container->set(SseManagerInterface::class, fn(ContainerInterface $c) => $c->get(SseManager::class));

        // --- Очередь ---

        // Репозиторий для работы с задачами
        $container->set(JobRepositoryInterface::class, fn() => new JobRepository());

        // Карта "Сообщение -> Обработчик"
        $container->set('queue.message_handler_map', fn() => [
            SendWelcomeEmailMessage::class => SendWelcomeEmailHandler::class,
            PingMessage::class => PingHandler::class,
        ]);

        $container->set(QueueManager::class, fn(ContainerInterface $c) => new QueueManager(
            $c,
            $c->get(QueueStatus::class),
            $c->get(SseManager::class),
            $c->get(JobRepository::class),
            $c->get(SseEventRepository::class),
            $c->get('queue.message_handler_map')
        ));
    }
}