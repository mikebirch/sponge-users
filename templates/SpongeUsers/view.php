<?php
/**
 * Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2017, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$Users = ${$tableAlias};
?>
<div class="page-top">
    <?php if($user->is_superuser) : ?>
    <div class="actions-menu">
        <ul class="menu">
            <li><?= $this->Delete->createForm(['action' => 'delete', $Users->id]) ?></li>
            <li><?= $this->Html->link('Change password', ['action' => 'changePassword', $Users->id]) ?></li>
            <li><?= $this->Html->link('Edit', ['action' => 'edit', $Users->id]) ?></li>
        </ul>
    </div>
    <?php endif ?>

    <h2><?= h($Users->username) ?></h2>
</div>
<h3><?= __d('CakeDC/Users', 'Email') ?></h3>
<p><?= h($Users->email) ?></p>
<h3><?= __d('CakeDC/Users', 'First Name') ?></h3>
<p><?= h($Users->first_name) ?></p>
<h3><?= __d('CakeDC/Users', 'Last Name') ?></h3>
<p><?= h($Users->last_name) ?></p>
<h3><?= __d('CakeDC/Users', 'Role') ?></h3>
<p><?= h($Users->role) ?></p>
<h3><?= __d('CakeDC/Users', 'Token') ?></h3>
<p><?= h($Users->token) ?></p>
<h3><?= __d('CakeDC/Users', 'Api Token') ?></h3>
<p><?= h($Users->api_token) ?></p>
<h3><?= __d('CakeDC/Users', 'Active') ?></h3>
<p><?= $this->Number->format($Users->active) ?></p>
<h3><?= __d('CakeDC/Users', 'Token Expires') ?></h3>
<p><?= h($Users->token_expires) ?></p>
<h3><?= __d('CakeDC/Users', 'Activation Date') ?></h3>
<p><?= h($Users->activation_date) ?></p>
<h3><?= __d('CakeDC/Users', 'Tos Date') ?></h3>
<p><?= h($Users->tos_date) ?></p>
<h3><?= __d('CakeDC/Users', 'Created') ?></h3>
<p><?= h($Users->created) ?></p>
<h3><?= __d('CakeDC/Users', 'Modified') ?></h3>
<p><?= h($Users->modified) ?></p>
<?php if (!empty($Users->social_accounts)) : ?>
<h2 class="subheader"><?= __d('CakeDC/Users', 'Social Accounts') ?></h2>

<table cellpadding="0" cellspacing="0">
    <tr>
        <th><?= __d('CakeDC/Users', 'Provider') ?></th>
        <th><?= __d('CakeDC/Users', 'Avatar') ?></th>
        <th><?= __d('CakeDC/Users', 'Active') ?></th>
        <th><?= __d('CakeDC/Users', 'Created') ?></th>
        <th><?= __d('CakeDC/Users', 'Modified') ?></th>
    </tr>
    <?php foreach ($Users->social_accounts as $socialAccount) : ?>
        <tr>
            <td><?= h($socialAccount->provider) ?></td>
            <td><?= h($socialAccount->avatar) ?></td>
            <td><?= h($socialAccount->active) ?></td>
            <td><?= h($socialAccount->created) ?></td>
            <td><?= h($socialAccount->modified) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
