<?php
namespace SpongeUsers\Controller;

use SpongeUsers\Controller\AppController;
use CakeDC\Users\Model\Table\UsersTable;
use CakeDC\Users\Controller\Component\UsersAuthComponent;
use CakeDC\Users\Controller\Traits\LinkSocialTrait;
use CakeDC\Users\Controller\Traits\LoginTrait;
use CakeDC\Users\Controller\Traits\ProfileTrait;
use CakeDC\Users\Controller\Traits\ReCaptchaTrait;
use CakeDC\Users\Controller\Traits\RegisterTrait;
use CakeDC\Users\Controller\Traits\SimpleCrudTrait;
use CakeDC\Users\Controller\Traits\SocialTrait;
use Cake\Utility\Inflector;
use Cake\Event\Event;
use Cake\Core\Configure;

/**
 * SpongeUsers Controller
 *
 *
 * @method \SpongeUsers\Model\Entity\SpongeUser[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SpongeUsersController extends AppController
{

    use LinkSocialTrait;
    use LoginTrait;
    use ProfileTrait;
    use ReCaptchaTrait;
    use RegisterTrait;
    use SimpleCrudTrait;
    use SocialTrait;

    /**
     * Index method
     * Override index method in CakeDC Users, SimpleCrudTrait.php
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
        $this->viewBuilder()->layout('admin');
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
        $this->viewBuilder()->layout('admin');
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
        $entity = $table->newEntity();
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        $this->viewBuilder()->layout('admin');
        if (!$this->request->is('post')) {
            return;
        }
        $entity = $table->patchEntity($entity, $this->request->getData());
        // add the next line to allow role to be edited
        // see https://stackoverflow.com/questions/44912295/cakedc-users-new-role
        // and https://github.com/CakeDC/users/issues/513
        $entity->role = $this->request->data('role');
        $singular = Inflector::singularize(Inflector::humanize($tableAlias));
        if ($table->save($entity)) {
            $this->Flash->success(__d('CakeDC/Users', 'The {0} has been saved', $singular));

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error(__d('CakeDC/Users', 'The {0} could not be saved', $singular));
    }

    /**
     * Edit method
     * Override edit method in CakeDC Users, SimpleCrudTrait.php
     *
     * @param string|null $id User id.
     * @return mixed Redirects on successful edit, renders view otherwise.
     * @throws NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $table = $this->loadModel();
        $tableAlias = $table->getAlias();
        $entity = $table->get($id, [
            'contain' => []
        ]);
        $this->set($tableAlias, $entity);
        $this->set('tableAlias', $tableAlias);
        $this->set('_serialize', [$tableAlias, 'tableAlias']);
        $this->viewBuilder()->layout('admin');
        if (!$this->request->is(['patch', 'post', 'put'])) {
            return;
        }
        $entity = $table->patchEntity($entity, $this->request->getData());
        // add the next line to allow role to be edited
        // see https://stackoverflow.com/questions/44912295/cakedc-users-new-role
        // and https://github.com/CakeDC/users/issues/513
        $entity->role = $this->request->data('role');
        $singular = Inflector::singularize(Inflector::humanize($tableAlias));
        if ($table->save($entity)) {
            $this->Flash->success('The user has been saved');

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error('The user could not be saved');
    }

    /**
     * Change password
     *
     * @return mixed
     */
    public function changePassword()
    {
        $user = $this->getUsersTable()->newEntity();
        $id = $this->Auth->user('id');
        if (!empty($id)) {
            $user->id = $this->Auth->user('id');
            $validatePassword = true;
            //@todo add to the documentation: list of routes used
            $redirect = Configure::read('Users.Profile.route');
        } else {
            $user->id = $this->request->getSession()->read(Configure::read('Users.Key.Session.resetPasswordUserId'));
            $validatePassword = false;
            if (!$user->id) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
                $this->redirect($this->Auth->getConfig('loginAction'));

                return;
            }
            //@todo add to the documentation: list of routes used
            $redirect = $this->Auth->getConfig('loginAction');
        }
        $this->set('validatePassword', $validatePassword);
        $this->viewBuilder()->layout('admin');
        if ($this->request->is(['post', 'put'])) {
            try {
                $validator = $this->getUsersTable()->validationPasswordConfirm(new Validator());
                if (!empty($id)) {
                    $validator = $this->getUsersTable()->validationCurrentPassword($validator);
                }
                $user = $this->getUsersTable()->patchEntity(
                    $user,
                    $this->request->getData(),
                    ['validate' => $validator]
                );
                if ($user->getErrors()) {
                    $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                } else {
                    $result = $this->getUsersTable()->changePassword($user);
                    if ($result) {
                        $event = $this->dispatchEvent(UsersAuthComponent::EVENT_AFTER_CHANGE_PASSWORD, ['user' => $result]);
                        if (!empty($event) && is_array($event->result)) {
                            return $this->redirect($event->result);
                        }
                        $this->Flash->success(__d('CakeDC/Users', 'Password has been changed successfully'));

                        return $this->redirect($redirect);
                    } else {
                        $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                    }
                }
            } catch (UserNotFoundException $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'User was not found'));
            } catch (WrongPasswordException $wpe) {
                $this->Flash->error($wpe->getMessage());
            } catch (Exception $exception) {
                $this->Flash->error(__d('CakeDC/Users', 'Password could not be changed'));
                $this->log($exception->getMessage());
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }
}
