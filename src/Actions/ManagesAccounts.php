<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Transforms\TransformsAccounts;

trait ManagesAccounts
{
    use TransformsAccounts;

    /**
     * Get a list of of accounts.
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Resources\Account[]
     */
    public function accounts()
    {
        // First, establish your member ID
        $connection = $this->get('https://app.vssps.visualstudio.com/_apis/profile/profiles/me');

        Validator::keyExists($connection, 'id');

        // Second, retrieve for the accounts for this member
        $accounts = $this->request(
            'GET',
            'https://app.vssps.visualstudio.com/_apis/accounts',
            ['query' => ['memberId' => $connection['id']]]
        );

        return $this->fromDevOpsAccounts($accounts);
    }
}
