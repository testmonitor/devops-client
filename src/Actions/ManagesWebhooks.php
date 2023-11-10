<?php

namespace TestMonitor\DevOps\Actions;

use TestMonitor\DevOps\Resources\Webhook;
use TestMonitor\DevOps\Transforms\TransformsWebhooks;

trait ManagesWebhooks
{
    use TransformsWebhooks;

    /**
     * Get a list of webhooks.
     *
     * @param string $eventType
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Webhook[]
     */
    public function webhooks($eventType = '')
    {
        $response = $this->get("_apis/hooks/subscriptions", [
            'query' => array_filter([
                'eventType' => $eventType,
                'consumerId' => 'webHooks',
            ]),
        ]);

        return $this->fromDevOpsWebhooks($response['value']);
    }

    /**
     * Create a new webhook.
     *
     * @param \TestMonitor\DevOps\Resources\Webhook $webhook
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return \TestMonitor\DevOps\Resources\Webhook
     */
    public function createWebhook(Webhook $webhook): Webhook
    {
        $response = $this->post(
            '_apis/hooks/subscriptions',
            [
                'json' => $this->toDevOpsWebhook($webhook),
            ]
        );

        return $this->fromDevOpsWebhook($response);
    }

    /**
     * Deletes a webhook.
     *
     * @param int $id
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     *
     * @return bool
     */
    public function deleteWebhook($id): bool
    {
        $response = $this->delete("_apis/hooks/subscriptions/{$id}");

        return empty($response);
    }
}
