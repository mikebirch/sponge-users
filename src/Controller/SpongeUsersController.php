<?php
declare(strict_types=1);

namespace SpongeUsers\Controller;

use SpongeUsers\Controller\AppController;
use Cake\Controller\Component\AuthComponent;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\PasswordManagementTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Exception\UserNotFoundException;
use CakeDC\Users\Exception\UserNotActiveException;
use CakeDC\Users\Exception\WrongPasswordException;
use FFI\Exception;
use Cake\Utility\Inflector;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Datasource\Exception\InvalidPrimaryKeyException;


/**
 * SpongeUsers Controller
 *
 *
 * @method \SpongeUsers\Model\Entity\SpongeUser[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SpongeUsersController extends AppController
{

    use LoginTrait;
    use ProfileTrait;
    use PasswordManagementTrait;
    use RegisterTrait;

    /**
     * Profile action
     *
     * @param mixed $id Profile id object.
     * @return mixed
     */
    public function profile($id = null)
    {
        $identity = $this->getRequest()->getAttribute('identity');
        $identity = $identity ?? [];
        $loggedUserId = $identity['id'] ?? null;
        $isCurrentUser = false;
        if (!Configure::read('Users.Profile.viewOthers') || empty($id)) {
            $id = $loggedUserId;
        }
        try {
            $appContain = (array)Configure::read('Auth.authenticate.' . AuthComponent::ALL . '.contain');
            $socialContain = Configure::read('Users.Social.login') ? ['SocialAccounts'] : [];
            $user = $this->getUsersTable()->get($id, [
                    'contain' => array_merge((array)$appContain, (array)$socialContain),
                ]);
            $this->set('avatarPlaceholder', Configure::read('Users.Avatar.placeholder'));
            if ($user->id === $loggedUserId) {
                $isCurrentUser = true;
            }
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'User was not found'));

            return $this->redirect($this->getRequest()->referer());
        } catch (InvalidPrimaryKeyException $ex) {
            $this->Flash->error(__d('cake_d_c/users', 'Not authorized, please login first'));

            return $this->redirect($this->getRequest()->referer());
        }
        $this->set(['user' => $user, 'isCurrentUser' => $isCurrentUser]);
        $this->set('_serialize', ['user', 'isCurrentUser']);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $table = $this->loadModel();
        $tableAlias = $table->getAlias();
        $this->set($tableAlias, $this->paginate($table));
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        $this->viewBuilder()->setLayout('SpongeAdmin.admin');
    }

    /**
     * View method
     * Override view method in CakeDC Users, SimpleCrudTrait.php
     *
     * @param string|null $id User id.
     * @return void
     * @throws NotFoundException When record not found.
     */
    public function view($id = null)
    {
        $table = $this->loadModel();
        $tableAlias = $table->getAlias();
        $entity = $table->get($id, [
            'contain' => []
        ]);
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * Add method
     * Override add method in CakeDC Users, SimpleCrudTrait.php
     *
     * @return mixed Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $table = $this->loadModel();
        $tableAlias = $table->getAlias();
        $entity = $table->newEmptyEntity();
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        $this->viewBuilder()->setLayout('admin');
        if (!$this->getRequest()->is('post')) {
            return;
        }
        $entity = $table->patchEntity($entity, $this->getRequest()->getData());
        // add the next line to allow role to be edited
        // see https://stackoverflow.com/questions/44912295/cakedc-users-new-role
        // and https://github.com/CakeDC/users/issues/513
        $entity->role = $this->request->getData('role');
        $singular = Inflector::singularize(Inflector::humanize($tableAlias));
        if ($table->save($entity)) {
            $this->Flash->success(__d('cake_d_c/users', 'The {0} has been saved', $singular));

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__d('cake_d_c/users', 'The {0} could not be saved', $singular));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return mixed Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $table = $this->loadModel();
        $tableAlias = $table->getAlias();
        $entity = $table->get($id, [
            'contain' => [],
        ]);
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        $this->viewBuilder()->setLayout('admin');
        if (!$this->getRequest()->is(['patch', 'post', 'put'])) {
            return;
        }
        $entity = $table->patchEntity($entity, $this->getRequest()->getData());
        // add the next line to allow role to be edited
        // see https://stackoverflow.com/questions/44912295/cakedc-users-new-role
        // and https://github.com/CakeDC/users/issues/513
        $entity->role = $this->request->getData('role');
        $singular = Inflector::singularize(Inflector::humanize($tableAlias));
        if ($table->save($entity)) {
            $this->Flash->success(__d('cake_d_c/users', 'The {0} has been saved', $singular));

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__d('cake_d_c/users', 'The {0} could not be saved', $singular));
    }

    /**
     * Change password
     * Can be used while logged in for own password, as a superuser on any user, or while not logged in for reset
     * reset password with session key (email token has already been validated)
     *
     * @param int|string|null $id user_id, null for logged in user id
     * @return mixed
     */
    public function changePassword($id = null)
    {
        $user = $this->getUsersTable()->newEntity([], ['validate' => false]);
        $user->setNew(false);

        $identity = $this->getRequest()->getAttribute('identity');
        $identity = $identity ?? [];
        $userId = $identity['id'] ?? null;

        if ($userId) {
            if ($id && $identity['is_superuser'] && Configure::read('Users.Superuser.allowedToChangePasswords')) {
                // superuser editing any account's password
                $user->id = $id;
                $validatePassword = false;
                $redirect = ['action' => 'index'];
            } elseif (!$id || $id === $userId) {
                // normal user editing own password
                $user->id = $userId;
                $validatePassword = true;
                $redirect = Configure::read('Users.Profile.route');
            } else {
                $this->Flash->error(
                    __d('cake_d_c/users', 'Changing another user\'s password is not allowed')
                );
                $this->redirect(Configure::read('Users.Profile.route'));

                return;
            }
        } else {
            // password reset
            $user->id = $this->getRequest()->getSession()->read(
                Configure::read('Users.Key.Session.resetPasswordUserId')
            );
            $validatePassword = false;
            $redirect = $this->Authentication->getConfig('loginAction');
            if (!$user->id) {
                $this->Flash->error(__d('cake_d_c/users', 'User was not found'));
                $this->redirect($redirect);

                return;
            }
        }
        $this->set('validatePassword', $validatePassword);
        $this->viewBuilder()->setLayout('admin');
        if ($this->getRequest()->is(['post', 'put'])) {
            try {
                $validator = $this->getUsersTable()->validationPasswordConfirm(new Validator());
                if ($validatePassword) {
                    $validator = $this->getUsersTable()->validationCurrentPassword($validator);
                }
                $this->getUsersTable()->setValidator('current', $validator);
                $user = $this->getUsersTable()->patchEntity(
                    $user,
                    $this->getRequest()->getData(),
                    [
                        'validate' => 'current',
                        'accessibleFields' => [
                            'current_password' => true,
                            'password' => true,
                            'password_confirm' => true,
                        ],
                    ]
                );

                if ($user->getErrors()) {
                    $this->Flash->error(__d('cake_d_c/users', 'Password could not be changed'));
                } else {
                    $result = $this->getUsersTable()->changePassword($user);
                    if ($result) {
                        $event = $this->dispatchEvent(Plugin::EVENT_AFTER_CHANGE_PASSWORD, ['user' => $result]);
                        if (!empty($event) && is_array($event->getResult())) {
                            return $this->redirect($event->getResult());
                        }
                        $this->Flash->success(__d('cake_d_c/users', 'Password has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('cake_d_c/users', 'Password could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('cake_d_c/users', 'User was not found'));
            } catch (WrongPasswordException $wpe) {
                $this->Flash->error($wpe->getMessage());
            } catch (Exception $exception) {
                $this->Flash->error(__d('cake_d_c/users', 'Password could not be changed'));
                $this->log($exception->getMessage());
            }
        }
        $this->set(['user' => $user]);
        $this->set('_serialize', ['user']);
    }

    /**
     * Reset password
     *
     * @return void|\Cake\Http\Response
     */
    public function requestResetPassword()
    {
        $this->set('user', $this->getUsersTable()->newEntity([], ['validate' => false]));
        $this->set('_serialize', ['user']);
        if (!$this->getRequest()->is('post')) {
            return;
        }

        $reference = $this->getRequest()->getData('reference');
        try {
            $resetUser = $this->getUsersTable()->resetToken($reference, [
                'expiration' => Configure::read('Users.Token.expiration'),
                'checkActive' => false,
                'sendEmail' => true,
                'ensureActive' => Configure::read('Users.Registration.ensureActive'),
                'type' => 'password',
            ]);
            if ($resetUser) {
                $msg = __d('cake_d_c/users', 'Please check your email to continue with password reset process');
                $this->Flash->success($msg);
            } else {
                $msg = __d('cake_d_c/users', 'The password token could not be generated. Please try again');
                $this->Flash->error($msg);
            }

            return $this->redirect(['action' => 'login']);
        } catch (UserNotFoundException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'User {0} was not found', $reference));
        } catch (UserNotActiveException $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'The user is not active'));
        } catch (Exception $exception) {
            $this->Flash->error(__d('cake_d_c/users', 'Token could not be reset'));
            $this->log($exception->getMessage());
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod(['post', 'delete']);
        $table = $this->loadModel();
        $tableAlias = $table->getAlias();
        $entity = $table->get($id, [
            'contain' => [],
        ]);
        $singular = Inflector::singularize(Inflector::humanize($tableAlias));
        if ($table->delete($entity)) {
            $this->Flash->success(__d('cake_d_c/users', 'The {0} has been deleted', $singular));
        } else {
            $this->Flash->error(__d('cake_d_c/users', 'The {0} could not be deleted', $singular));
        }

        return $this->redirect(['action' => 'index']);
    }
}
