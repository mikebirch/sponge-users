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
?>

<?php if($userData['is_superuser']) : ?>
<p><?= $this->Html->link('New user', ['action' => 'add'],['class' => 'btn button']) ?></p>
<?php endif ?>

<table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
        <th class="actions">&nbsp;</th>
        <th><?= $this->Paginator->sort('username', __d('CakeDC/Users', 'Username')) ?></th>
        <th><?= $this->Paginator->sort('email', __d('CakeDC/Users', 'Email')) ?></th>
        <th><?= $this->Paginator->sort('first_name', __d('CakeDC/Users', 'First name')) ?></th>
        <th><?= $this->Paginator->sort('last_name', __d('CakeDC/Users', 'Last name')) ?></th>
        <th><?= $this->Paginator->sort('role') ?></th>
        <th><?= $this->Paginator->sort('active') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (${$tableAlias} as $user) : ?>
        <tr>
            <td class="actions">
                <?php if($userData['is_superuser']) : ?>
                <?= $this->Delete->createForm(['action' => 'delete', $user->id]) ?>
                <?php endif ?>
                <?= $this->Html->link('View', ['action' => 'view', $user->id]) ?>
                <?php if($userData['is_superuser']) : ?>
                <?= $this->Html->link('Change password', ['action' => 'changePassword', $user->id]) ?>
                <?= $this->Html->link('Edit', ['action' => 'edit', $user->id]) ?>
                <?php endif ?>
            </td>
            <td><?= h($user->username) ?></td>
            <td><?= h($user->email) ?></td>
            <td><?= h($user->first_name) ?></td>
            <td><?= h($user->last_name) ?></td>
            <td><?= h($user->role) ?></td>
            <?php
                $yes = '<svg class="icon icon-checkmark-circle"><use xlink:href="/img/admin/icons.svg#icon-checkmark-circle"></use></svg><span>Yes</span>';
                $no = '<svg class="icon icon-cancel-circle"><use xlink:href="/img/admin/icons.svg#icon-cancel-circle"></use></svg><span>No</span>';
            ?>
            <td class=" hide-text"><?= h($user->active) == 1 ? $yes : $no ?></td>
        </tr>

    <?php endforeach; ?>
    </tbody>
</table>
<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
    </ul>
    <p><?= $this->Paginator->counter() ?></p>
</div>

