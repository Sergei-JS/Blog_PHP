<?php

namespace MyProject\Controllers;
use MyProject\Controllers\AbstractController;
use MyProject\Exceptions\InvalidArgumentException;
use MyProject\Models\Users\User;
use MyProject\Models\Users\UserActivationService;
use MyProject\Models\Users\UsersAuthService;
use MyProject\Services\EmailSender;
use MyProject\View\View;
class UsersController extends AbstractController{

    public function signUp()
    {
        if (!empty($_POST)) {
            try {
                $user = User::signUp($_POST);
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('users/signUp.php', ['error' => $e->getMessage()]);
                return;
            }

            if ($user instanceof User) {
                $code = UserActivationService::createActivationCode($user);

                EmailSender::send($user, 'Активация', 'userActivation.php', [
                    'userId' => $user->getId(),
                    'code' => $code
                ]);
                $this->view->renderHtml('users/signUpSuccessful.php');
                return;
            }

        }

        $this->view->renderHtml('users/signUp.php');
    }
    public function activate(int $userId, string $activationCode)
    {
        try {
            $user = User::getById($userId);
            if ($user === null) {
                throw new ActivationException('Пользователь не найден.');
            }
            if ($user->getIsConfirmed() === 1) {
                throw new InvalidActivationException('Пользователь уже авторизован');
            }

            $isCodeValid = UserActivationService::checkActivationCode($user, $activationCode);
            if (!$isCodeValid) {
                throw new ActivationException('Неверный код активации');
            }

            if ($isCodeValid) {
                $user->activate();
                $this->view->renderHtml('users/successfulActivation.php');
                UserActivationService::deleteCode($userId);
                    return;
            }
        }catch (ActivationException $e){
            $this->view->renderHtml('users/nonexistensCode.php');
        }

    }


    public function login()
    {
        if (!empty($_POST)) {
            try {
                $user = User::login($_POST);
                UsersAuthService::createToken($user);
                header('Location: /');
                exit();
            } catch (InvalidArgumentException $e) {
                $this->view->renderHtml('users/login.php', ['error' => $e->getMessage()]);
                return;
            }
        }

        $this->view->renderHtml('users/login.php');
    }
    public function logOut()
    {
        setcookie('token', null, -1, '/','', false, true);
        header('Location: /');
    }
}