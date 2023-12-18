<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\Account;
use TestMonitor\DevOps\Resources\Profile;

trait TransformsAccounts
{
    /**
     * @param array $accounts
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Account[]
     */
    protected function fromDevOpsAccounts($accounts): array
    {
        Validator::isArray($accounts);

        return array_map(function ($account) {
            return $this->fromDevOpsAccount($account);
        }, $accounts);
    }

    /**
     * @param array $account
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Account
     */
    protected function fromDevOpsAccount($account): Account
    {
        Validator::keysExists($account, ['AccountId', 'AccountName']);

        return new Account([
            'id' => $account['AccountId'],
            'name' => $account['AccountName'],
        ]);
    }

    /**
     * @param array $profile
     *
     * @return \TestMonitor\DevOps\Resources\Profile
     */
    protected function fromDevOpsProfile($profile): Profile
    {
        Validator::keysExists($profile, ['id', 'displayName', 'emailAddress']);

        return new Profile([
            'id' => $profile['id'],
            'name' => $profile['displayName'],
            'email' => $profile['emailAddress'],
        ]);
    }

}
