<?php

namespace TestMonitor\DevOps\Transforms;

use TestMonitor\DevOps\Validator;
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
     *
     * @throws \TestMonitor\DevOps\Exceptions\InvalidDataException
     * @return \TestMonitor\DevOps\Resources\Attachment
     */
    protected function fromDevOpsAttachment(array $attachment): Attachment
    {
        Validator::keysExists($attachment, ['id', 'url']);

        return new Attachment([
            'id' => $attachment['id'],
            'url' => $attachment['url'],
        ]);
    }
}
