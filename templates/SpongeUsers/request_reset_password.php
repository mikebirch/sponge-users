<?php
$this->Form->setTemplates([
    'inputContainer' => '<div>{{content}}<p>Instructions will be emailed to this address.</p></div>'
]);
?>
<div id="main">
<h1>Reset your password</h1>
<?= $this->Form->create('User') ?>
<fieldset>
    <?= $this->Form->control('reference', ['label' => 'Your email address']) ?>
</fieldset>
<?= $this->Form->button('Submit', ['class' => 'btn']); ?>
<?= $this->Form->end() ?>
</div>
