<?php
/**
 * Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010 - 2020, Cake Development Corporation (https://www.cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * @var \CakeDC\Users\Model\Entity\User $user
 */
$this->Form->setTemplates([
    'inputContainer' => '<div>{{content}}<p>Instructions will be emailed to this address.</p></div>'
]);
?>
<div class="users form">
    <?= $this->Flash->render('auth') ?>
    <?= $this->Form->create($user) ?>
    <fieldset>
        <?= $this->Form->control('reference', ['label' => 'Enter your email address']) ?>
    </fieldset>
    <?= $this->Form->button(__d('cake_d_c/users', 'Submit'), ['class' => 'btn']); ?>
</div>

