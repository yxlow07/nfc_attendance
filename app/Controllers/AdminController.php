<?php

namespace app\Controllers;

use app\Exceptions\UserNotFoundException;
use app\Models\LoginModel;
use app\Models\RegisterModel;
use app\Models\UserModel;
use core\App;
use core\Controller;
use core\Models\BaseModel;
use core\Models\ValidationModel;
use core\View;

class AdminController extends Controller
{
    public function createUsersRules():array
    {
        return [
            'idMurid' => [ValidationModel::RULE_REQUIRED, [ValidationModel::RULE_MIN, 'min' => 5]],
            'noTel' => [ValidationModel::RULE_REQUIRED, [ValidationModel::RULE_MIN, 'min' => 10], [ValidationModel::RULE_MAX, 'max' => 11]],
            'namaM' => [ValidationModel::RULE_REQUIRED],
            'kLMurid' => [ValidationModel::RULE_REQUIRED],
        ];
    }

    public function list_users(): void
    {
        $users = (array) App::$app->database->findAll('murid');
        echo View::make(['/views/admin/'])->renderView('users', ['users' => $users]);
    }

    public function createUsers(): void
    {
        $model = new RegisterModel(App::$app->request->data());

        if (App::$app->request->isMethod('post')) {
            if ($model->validate($this->createUsersRules()) && $model->verifyNoDuplicate() && $model->registerUser() && $model->registerName()) {
                App::$app->session->setFlashMessage('success', 'Successfully registered user.');
                header('Location: /crud_users');
            }
        }

        echo View::make(['/views/admin'])->renderView('create_users', ['model' => $model]);
    }

    public function crud_users($idMurid, $action)
    {
        $data = match ($action) {
            BaseModel::READ => LoginModel::getUserFromDB($idMurid, true),
            BaseModel::DELETE => UserModel::deleteUserFromDB($idMurid),
            default => BaseModel::UNDEFINED,
        };

        if ($data === false || $data === BaseModel::UNDEFINED) {
            throw new UserNotFoundException();
        }

        echo View::make(['/views/admin'])->renderView('user_profile', ['data' => (array)$data, 'action' => $action]);
    }
}