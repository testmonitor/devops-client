<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
use TestMonitor\DevOps\Resources\Webhook;

trait TransformsWebhooks
{
    /**
     * @param \TestMonitor\DevOps\Resources\Webhook $webhook
     *
     * @return array
     */
    protected function toDevOpsWebhook(Webhook $webhook): array
    {
        return [
            "publisherId" => "tfs",
            "eventType" => $webhook->eventType,
            "resourceVersion" => "1.0-preview.1",
            "consumerId" => "webHooks",
            "consumerActionId" => "httpRequest",
            "actionDescription" => $webhook->description,
            "publisherInputs" => [
                "projectId" => $webhook->projectId,
            ],
            "consumerInputs" => [
                'basicAuthUsername' => $webhook->username,
                'basicAuthPassword' => $webhook->password,
                "url" => $webhook->url,
            ],
        ];
    }

    /**
     * @param array $webhooks
     *
     * @return \TestMonitor\DevOps\Resources\Webhook[]
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     */
    protected function fromDevOpsWebhooks($webhooks): array
    {
        Validator::isArray($webhooks);

        return array_map(function ($webhook) {
            return $this->fromDevOpsWebhook($webhook);
        }, $webhooks);
    }

    /**
     * @param array $webhook
     *
     * @return \TestMonitor\DevOps\Resources\Webhook
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     */
    protected function fromDevOpsWebhook($webhook): Webhook
    {
        Validator::keysExists($webhook, ['id', 'eventType']);

        return new Webhook([
            'id' => $webhook['id'],
            'url' => $webhook['consumerInputs']['url'],
            'description' => $webhook['actionDescription'],
            'eventType' => $webhook['eventType'],
            'projectId' => $webhook['publisherInputs']['projectId'] ?? '',
            'username' => $webhook['consumerInputs']['basicAuthUsername'] ?? '',
            'password' => $webhook['consumerInputs']['basicAuthPassword'] ?? '',
        ]);
    }
}
