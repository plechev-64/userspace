<?php

namespace UserSpace\Common\Module\User\App\UseCase\Registration;

use UserSpace\Common\Module\Settings\Src\Domain\OptionManagerInterface;
use UserSpace\Common\Module\User\Src\Domain\UserApiInterface;
use UserSpace\Core\Exception\UspException;
use UserSpace\Core\SecurityHelperInterface;

class ConfirmRegistrationUseCase
{
    public function __construct(
        private readonly UserApiInterface       $userApi,
        private readonly SecurityHelperInterface         $securityHelper,
        private readonly OptionManagerInterface $optionManager
    )
    {
    }

    /**
     * @throws UspException
     */
    public function execute(ConfirmRegistrationCommand $command): ConfirmRegistrationResult
    {
        $data = json_decode(base64_decode($command->token), true);

        if (empty($data) || !is_array($data) || count($data) !== 3) {
            throw new UspException('Invalid token format.');
        }

        [$userLogin, $userIdHash, $securityHash] = $data;

        $user = $this->userApi->getUserBy('login', $userLogin);

        if (!$user || md5($user->ID) !== $userIdHash || md5($this->securityHelper->getSecurityKey() . $user->ID) !== $securityHash) {
            throw new UspException('Invalid token data.');
        }

        // Активируем пользователя, устанавливая ему роль по умолчанию
        $this->userApi->updateUser(['ID' => $user->ID, 'role' => $this->optionManager->get('default_role')]);

        // Определяем URL для перенаправления
        $settings = $this->optionManager->get('usp_settings', []);
        $loginPageUrl = !empty($settings['login_page_id']) ? get_permalink($settings['login_page_id']) : wp_login_url();
        $redirectUrl = add_query_arg('reg-success', 'confirmed', $loginPageUrl);

        return new ConfirmRegistrationResult($redirectUrl);
    }
}