<?php

namespace UserSpace;

use UserSpace\Admin\Controller\FieldSettingsController;
use UserSpace\Admin\Controller\ProfileAdminController;
use UserSpace\Admin\Controller\RegistrationAdminController;
use UserSpace\Admin\Controller\SettingsAdminController;
use UserSpace\Admin\SetupWizard\SetupWizardController;
use UserSpace\Common\Controller\FileUploaderController;
use UserSpace\Common\Controller\LoginController;
use UserSpace\Common\Controller\ModalFormController;
use UserSpace\Common\Controller\PasswordResetController;
use UserSpace\Common\Controller\UserController;
use UserSpace\Common\Module\Form\App\Controller\ProfileFormController;
use UserSpace\Common\Module\Form\App\Controller\RegistrationController;
use UserSpace\Common\Module\Form\Src\Domain\Repository\FormRepositoryInterface;
use UserSpace\Common\Module\Form\Src\Infrastructure\Repository\FormRepository;
use UserSpace\Common\Module\Grid\App\GridController;
use UserSpace\Common\Module\Queue\App\QueueActionsController;
use UserSpace\Common\Module\Queue\App\Task\Message\PingMessage;
use UserSpace\Common\Module\Queue\App\Task\Message\SendWelcomeEmailMessage;
use UserSpace\Common\Module\Queue\App\Task\PingHandler;
use UserSpace\Common\Module\Queue\App\Task\SendWelcomeEmailHandler;
use UserSpace\Common\Module\Queue\Src\Domain\JobRepositoryInterface;
use UserSpace\Common\Module\Queue\Src\Infrastructure\JobRepository;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueManager;
use UserSpace\Common\Module\Queue\Src\Infrastructure\QueueStatus;
use UserSpace\Common\Module\SSE\App\SseController;
use UserSpace\Common\Module\SSE\Src\Domain\Repository\SseEventRepositoryInterface;
use UserSpace\Common\Module\SSE\Src\Domain\SseEventDispatcherInterface;
use UserSpace\Common\Module\SSE\Src\Infrastructure\Repository\SseEventRepository;
use UserSpace\Common\Module\SSE\Src\Infrastructure\SseEventDispatcher;
use UserSpace\Common\Module\Tabs\App\Controller\TabContentController;
use UserSpace\Common\Module\Tabs\App\Controller\TabsConfigAdminController;
use UserSpace\Common\Module\Tabs\App\Controller\TabSettingsController;
use UserSpace\Common\Module\Tabs\App\Tabs\ActivityTab;
use UserSpace\Common\Module\Tabs\App\Tabs\EditProfileTab;
use UserSpace\Common\Module\Tabs\App\Tabs\ProfileTab;
use UserSpace\Common\Module\Tabs\App\Tabs\SecurityTab;
use UserSpace\Common\Module\Tabs\App\Tabs\UserListTab;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabConfigManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabManager;
use UserSpace\Common\Module\Tabs\Src\Infrastructure\TabProvider;
use UserSpace\Common\Service\TemplateManager;
use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Common\Service\CronManager;
use UserSpace\Core\Database\TransactionService;
use UserSpace\Core\Database\QueryBuilderInterface;
use UserSpace\Core\Database\TransactionServiceInterface;
use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Database\QueryBuilder;
use UserSpace\Core\Helper\StringFilter;
use UserSpace\Core\Helper\StringFilterInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Process\BackgroundProcessManager;
use UserSpace\Core\Rest\Helper\RestHelper;
use UserSpace\Core\Rest\RestApi;
use UserSpace\Core\Rest\Route\RouteCollector;
use UserSpace\Core\Rest\Route\RouteParser;

/**
 * Регистрирует сервисы плагина в DI-контейнере.
 * Все сервисы собирать не нужно, работает autowiring
 */
class ServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        // Основной класс плагина
        $container->set(Plugin::class, fn() => Plugin::getInstance());

        // Регистрируем сам контейнер, чтобы его можно было внедрять по интерфейсу
        $container->set(ContainerInterface::class, fn() => $container);

        // HTTP
        $container->set(Request::class, fn() => Request::createFromGlobals());

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
            fn(ContainerInterface $c) => new RestHelper(
                $c->get(Request::class),
                $c->get('rest.prefix'),
                $c->get('rest.namespace')
            )
        );

        // --- Шаблоны ---
        $container->set('app.templates', fn() => [
            'modal_container' => USERSPACE_PLUGIN_DIR . 'views/modal-container.php',
            'user_bar' => USERSPACE_PLUGIN_DIR . 'views/user-bar-template.php',
            'login_form' => USERSPACE_PLUGIN_DIR . 'views/login-form-template.php',
            'registration_form' => USERSPACE_PLUGIN_DIR . 'views/registration-form-template.php',
            'forgot_password_form' => USERSPACE_PLUGIN_DIR . 'views/forgot-password-form-template.php',
            'grid_user_item' => USERSPACE_PLUGIN_DIR . 'views/grid/user-item.php',
            'admin_form_builder_templates' => USERSPACE_PLUGIN_DIR . 'views/admin/form-builder-templates.php',
        ]);

        $container->set(TemplateManagerInterface::class, fn(ContainerInterface $c) => new TemplateManager(
            $c->get('app.templates'),
            $c->get(StringFilterInterface::class)
        ));

        // Создаем алиас, чтобы при запросе конкретного класса контейнер использовал фабрику интерфейса
        $container->set(TemplateManager::class, fn(ContainerInterface $c) => $c->get(TemplateManagerInterface::class));

        $container->set('app.tabs', fn() => [
            ProfileTab::class,
            EditProfileTab::class,
            SecurityTab::class,
            ActivityTab::class,
            UserListTab::class,
        ]);

        $container->set(TabProvider::class, fn(ContainerInterface $c) => new TabProvider(
            $c->get(TabManager::class),
            $c->get(TabConfigManager::class),
            $c->get('app.tabs')
        ));

        // Контроллеры

        $container->set('app.controllers', fn() => [
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
        ]);

        $container->set(RestApi::class, function (ContainerInterface $c) {
            return new RestApi(
                $c->get('app.controllers'),
                $c->get('rest.namespace'),
                $c->get(RouteParser::class),
                $c
            );
        });

        // База данных
        $container->set(
            QueryBuilderInterface::class,
            function () {
                global $wpdb;
                return new QueryBuilder($wpdb);
            }
        );
        $container->set(QueryBuilder::class, fn(ContainerInterface $c) => $c->get(QueryBuilderInterface::class));

        $container->set(
            TransactionServiceInterface::class,
            fn(ContainerInterface $c) => new TransactionService($c->get(QueryBuilderInterface::class))
        );

        // --- Фоновые процессы ---
        $container->set(BackgroundProcessManager::class, fn() => new BackgroundProcessManager());

        // --- Строки ---
        $container->set(StringFilterInterface::class, fn() => new StringFilter());

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
        $container->set(FormRepositoryInterface::class, fn(ContainerInterface $c) => $c->get(FormRepository::class));

        // --- SSE ---
        $container->set(SseEventRepositoryInterface::class, fn(ContainerInterface $c) => $c->get(SseEventRepository::class));
        $container->set(SseEventDispatcherInterface::class, fn(ContainerInterface $c) => $c->get(SseEventDispatcher::class));

        // --- Очередь ---

        // Репозиторий для работы с задачами
        $container->set(JobRepositoryInterface::class, fn(ContainerInterface $c) => $c->get(JobRepository::class));

        // Карта "Сообщение -> Обработчик"
        $container->set('queue.message_handler_map', fn() => [
            SendWelcomeEmailMessage::class => SendWelcomeEmailHandler::class,
            PingMessage::class => PingHandler::class,
        ]);

        $container->set(QueueManager::class, fn(ContainerInterface $c) => new QueueManager(
            $c,
            $c->get(QueueStatus::class),
            $c->get(SseEventDispatcher::class),
            $c->get(JobRepository::class),
            $c->get(SseEventRepository::class),
            $c->get('queue.message_handler_map')
        ));
    }
}