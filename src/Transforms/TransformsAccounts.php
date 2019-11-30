<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Resources\Account;

trait TransformsAccounts
{
    /**
     * @param array $account
     * @return \TestMonitor\DevOps\Resources\Account
     */
    protected function fromDevOpsAccount(array $account): Account
    {
        return new Account(
            $account['AccountId'],
            $account['AccountName']
        );
    }
}
