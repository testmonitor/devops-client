<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Transforms\TransformsAccounts;

trait ManagesAccounts
{
    use TransformsAccounts;

    /**
     * Get a list of of accounts.
     *
     * @return \TestMonitor\DevOps\Resources\Account[]
     */
    public function accounts()
    {
        $connection = $this->request(
            "GET",
            "https://app.vssps.visualstudio.com/_apis/ConnectionData",
            ['query' => ['api-version' => '5.0-preview'], 'debug' => true]
        );

        // Second, retrieve for the accounts for this member
        $accounts = $this->request(
            "GET",
            "https://app.vssps.visualstudio.com/_apis/accounts",
            ['query' => ['memberId' => $connection['authenticatedUser']['id']]]
        );

        return array_map(function ($account) {
            return $this->fromDevOpsAccount($account);
        }, $accounts);
    }
}
