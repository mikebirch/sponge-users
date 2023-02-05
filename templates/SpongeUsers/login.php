<?php
/**
 * Copyright 2010 - 2019, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2018, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Core\Configure;
$this->assign('title', 'Login | ' . $settings['Site']['title']);
?>
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create() ?>
    <fieldset>
        <?= $this->Form->control('email', ['label' => 'Email', 'required' => true]) ?>
        <?= $this->Form->control('password', ['label' => 'Password', 'required' => true]) ?>
        <?php
        if (Configure::read('Users.RememberMe.active')) {
            echo $this->Form->control(Configure::read('Users.Key.Data.rememberMe'), [
                'type' => 'checkbox',
                'label' => __d('cake_d_c/users', 'Remember me'),
                'checked' => Configure::read('Users.RememberMe.checked')
            ]);
        }
        ?>
        <?php
        if (Configure::read('Users.Email.required')) {
            echo $this->Html->link('Forgotten your password?', ['action' => 'requestResetPassword']);
        }
        ?>
    </fieldset>
    <?= $this->Form->button('Login'); ?>
    <?= $this->Form->end() ?>
</div>


