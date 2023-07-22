<?php
declare(strict_types=1);

namespace SpongeUsers\Model\Table;

use CakeDC\Users\Model\Table\UsersTable;
use Cake\ORM\RulesChecker;

class SpongeUsersTable extends UsersTable
{
    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['username']), '_isUnique', [
            'errorField' => 'username',
            'message' => __d('cake_d_c/users', 'Username already exists'),
        ]);

        // check for unique email in add method
        $rules->add($rules->isUnique(['email']), '_isUnique', [
            'errorField' => 'email',
            'message' => __d('cake_d_c/users', 'Email already exists'),
        ]);

        return $rules;
    }
}
