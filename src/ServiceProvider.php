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
use UserSpace\Core\Database\QueryBuilder;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Queue\QueueManager;
use UserSpace\Core\Queue\QueueStatus;
use UserSpace\Core\Rest\Helper\RestHelper;
use UserSpace\Core\Rest\RestApi;
use UserSpace\Core\Rest\Route\RouteCollector;
use UserSpace\Core\Rest\Route\RouteParser;
use UserSpace\Core\SetupWizard\SetupWizardController;
use UserSpace\JobHandler\Message\PingMessage;
use UserSpace\JobHandler\Message\SendWelcomeEmailMessage;
use UserSpace\JobHandler\PingHandler;
use UserSpace\JobHandler\SendWelcomeEmailHandler;

/**
 * Регистрирует все сервисы плагина в DI-контейнере.
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
        $container->set('rest.namespace', fn() => 'userspace/v1');

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

        // --- Очередь ---

        // Карта "Сообщение -> Обработчик"
        $container->set('queue.message_handler_map', fn() => [
            SendWelcomeEmailMessage::class => SendWelcomeEmailHandler::class,
            PingMessage::class => PingHandler::class,
        ]);

        $container->set(QueueManager::class, function (ContainerInterface $c) {
            return new QueueManager($c, $c->get(QueueStatus::class), $c->get('queue.message_handler_map'));
        });
    }
}