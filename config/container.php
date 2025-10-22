<?php

use UserSpace\Adapters\AdminApi;
use UserSpace\Adapters\AssetRegistry;
use UserSpace\Adapters\AuthApi;
use UserSpace\Adapters\DatabaseConnection;
use UserSpace\Adapters\HookManager;
use UserSpace\Adapters\LocalizationApi;
use UserSpace\Adapters\MediaApi;
use UserSpace\Adapters\OptionManager;
use UserSpace\Adapters\QueryApi;
use UserSpace\Adapters\StringFilter;
use UserSpace\Adapters\TransientApi;
use UserSpace\Adapters\UserApi;
use UserSpace\Adapters\WpApi;
use UserSpace\Admin\Controller\FieldSettingsController;
use UserSpace\Admin\Controller\ProfileAdminController;
use UserSpace\Admin\Controller\RegistrationAdminController;
use UserSpace\Admin\Controller\SettingsAdminController;
use UserSpace\Admin\SetupWizard\SetupWizardController;
use UserSpace\Common\Controller\LoginController;
use UserSpace\Common\Controller\MediaController;
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
use UserSpace\Common\Module\Queue\App\Task\Message\SendConfirmationEmailMessage;
use UserSpace\Common\Module\Queue\App\Task\Message\SendWelcomeEmailMessage;
use UserSpace\Common\Module\Queue\App\Task\PingHandler;
use UserSpace\Common\Module\Queue\App\Task\SendConfirmationEmailHandler;
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
use UserSpace\Common\Service\CronManager;
use UserSpace\Common\Service\TemplateManager;
use UserSpace\Common\Service\TemplateManagerInterface;
use UserSpace\Core\Admin\AdminApiInterface;
use UserSpace\Core\Asset\AssetRegistryInterface;
use UserSpace\Core\Auth\AuthApiInterface;
use UserSpace\Core\ContainerInterface;
use UserSpace\Core\Database\DatabaseConnectionInterface;
use UserSpace\Core\Hooks\HookManagerInterface;
use UserSpace\Core\Http\Request;
use UserSpace\Core\Localization\LocalizationApiInterface;
use UserSpace\Core\Media\MediaApiInterface;
use UserSpace\Core\Option\OptionManagerInterface;
use UserSpace\Core\Params;
use UserSpace\Core\Profile\ProfileService;
use UserSpace\Core\Profile\ProfileServiceApiInterface;
use UserSpace\Core\Query\QueryApiInterface;
use UserSpace\Core\Rest\Helper\RestHelper;
use UserSpace\Core\Rest\RestApi;
use UserSpace\Core\Rest\Route\RouteParser;
use UserSpace\Core\String\StringFilterInterface;
use UserSpace\Core\TransientApiInterface;
use UserSpace\Core\User\UserApiInterface;
use UserSpace\Core\WpApiInterface;
use UserSpace\Plugin;

return [
    'parameters' => [
        'rest.prefix' => 'wp-json',
        'rest.namespace' => USERSPACE_REST_NAMESPACE,
        'app.templates' => new Params([
            'modal_container' => USERSPACE_PLUGIN_DIR . 'views/modal-container.php',
            'user_bar' => USERSPACE_PLUGIN_DIR . 'views/user-bar-template.php',
            'login_form' => USERSPACE_PLUGIN_DIR . 'views/login-form-template.php',
            'registration_form' => USERSPACE_PLUGIN_DIR . 'views/registration-form-template.php',
            'forgot_password_form' => USERSPACE_PLUGIN_DIR . 'views/forgot-password-form-template.php',
            'grid_user_item' => USERSPACE_PLUGIN_DIR . 'views/grid/user-item.php',
            'admin_form_builder_templates' => USERSPACE_PLUGIN_DIR . 'views/admin/form-builder-templates.php',
        ]),
        'app.tabs' => [
            ProfileTab::class,
            EditProfileTab::class,
            SecurityTab::class,
            ActivityTab::class,
            UserListTab::class,
        ],
        'app.controllers' => [
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
            UserController::class,
            GridController::class,
            SetupWizardController::class,
            QueueActionsController::class,
            SseController::class,
            MediaController::class
        ],
        'queue.message_handler_map' => [
            SendWelcomeEmailMessage::class => SendWelcomeEmailHandler::class,
            PingMessage::class => PingHandler::class,
            SendConfirmationEmailMessage::class => SendConfirmationEmailHandler::class, // Новое сообщение и его обработчик
        ],
    ],
    'definitions' => [
        // Core & UserSpace\Adapters
        Plugin::class => fn() => Plugin::getInstance(),
        ContainerInterface::class => fn(ContainerInterface $c) => $c,
        Request::class => fn() => Request::createFromGlobals(),
        DatabaseConnectionInterface::class => function () {
            global $wpdb;
            return new DatabaseConnection($wpdb);
        },
        ProfileServiceApiInterface::class => fn(ContainerInterface $c) => $c->get(ProfileService::class),
        WpApiInterface::class => fn() => new WpApi(),
        StringFilterInterface::class => fn() => new StringFilter(),
        AssetRegistryInterface::class => fn() => new AssetRegistry(),
        AdminApiInterface::class => fn() => new AdminApi(),
        HookManagerInterface::class => fn() => new HookManager(),
        UserApiInterface::class => fn(ContainerInterface $c) => new UserApi($c->get(AuthApiInterface::class)),
        AuthApiInterface::class => fn() => new AuthApi(),
        MediaApiInterface::class => fn() => new MediaApi(),
        QueryApiInterface::class => fn() => new QueryApi(),
        LocalizationApiInterface::class => fn() => new LocalizationApi(),
        TransientApiInterface::class => fn() => new TransientApi(),
        OptionManagerInterface::class => fn(ContainerInterface $c) => new OptionManager($c->get(TransientApiInterface::class)),

        // REST
        RestHelper::class => fn(ContainerInterface $c) => new RestHelper($c->get(Request::class), $c->get('rest.prefix'), $c->get('rest.namespace')),
        RestApi::class => fn(ContainerInterface $c) => new RestApi($c->get('app.controllers'), $c->get('rest.namespace'), $c->get(RouteParser::class), $c, $c->get(UserApiInterface::class)),

        // App Services
        TemplateManagerInterface::class => fn(ContainerInterface $c) => new TemplateManager($c->get('app.templates'), $c->get(StringFilterInterface::class)),
        TabProvider::class => fn(ContainerInterface $c) => new TabProvider($c->get(TabManager::class), $c->get(TabConfigManager::class), $c->get('app.tabs')),
        CronManager::class => function (ContainerInterface $c) {
            $queueManager = $c->get(QueueManager::class);
            $cronManager = new CronManager($queueManager);
            $queueManager->setCronManager($cronManager);
            return $cronManager;
        },
        QueueManager::class => fn(ContainerInterface $c) => new QueueManager($c, $c->get(QueueStatus::class), $c->get(SseEventDispatcher::class), $c->get(JobRepository::class), $c->get(SseEventRepository::class), $c->get('queue.message_handler_map')),

        // Repositories
        FormRepositoryInterface::class => fn(ContainerInterface $c) => $c->get(FormRepository::class),
        SseEventRepositoryInterface::class => fn(ContainerInterface $c) => $c->get(SseEventRepository::class),
        JobRepositoryInterface::class => fn(ContainerInterface $c) => $c->get(JobRepository::class),
        SseEventDispatcherInterface::class => fn(ContainerInterface $c) => $c->get(SseEventDispatcher::class),

        // Aliases for concrete classes
        TemplateManager::class => fn(ContainerInterface $c) => $c->get(TemplateManagerInterface::class),
        DatabaseConnection::class => fn(ContainerInterface $c) => $c->get(DatabaseConnectionInterface::class),
        OptionManager::class => fn(ContainerInterface $c) => $c->get(OptionManagerInterface::class),
    ],
];