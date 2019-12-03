<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Resources\Attachment;

trait TransformsAttachments
{
    /**
     * @param string $attachmentUrl
     * @return array
     */
    protected function toDevOpsAttachment(string $attachmentUrl): array
    {
        return [
            [
                'op' => 'add',
                'path'=> '/relations/-',
                'value'=> [
                    'rel' => 'AttachedFile',
                    'url' => $attachmentUrl,
                    'attributes' => [
                        'comment' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $attachment
     * @return \TestMonitor\DevOps\Resources\Attachment
     */
    protected function fromDevOpsAttachment(array $attachment): Attachment
    {
        return new Attachment([
            'id' => $attachment['id'],
            'url' => $attachment['url'],
        ]);
    }
}
