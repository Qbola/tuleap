<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\Snapshot;

use DateTimeImmutable;
use Mockery;
use PFUser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\Attachment\Attachment;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\Attachment\AttachmentCollection;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\Changelog\ChangelogEntryValueRepresentation;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\Changelog\CreationStateListValueFormatter;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\FieldMapping;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\FieldMappingCollection;

class ChangelogSnapshotBuilderTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testItBuildsASnapshotFromChangelogEntry(): void
    {
        $logger  = Mockery::mock(LoggerInterface::class);
        $builder = new ChangelogSnapshotBuilder(
            new CreationStateListValueFormatter(),
            $logger
        );

        $logger->shouldReceive('debug');

        $user                          = Mockery::mock(PFUser::class);
        $changelog_entry               = $this->buildChangelogEntry();
        $jira_field_mapping_collection = $this->buildFieldMappingCollection();
        $current_snapshot              = $this->buildCurrentSnapshot($user);
        $attachment_collection         = new AttachmentCollection(
            [
                new Attachment(
                    10007,
                    "file01.png",
                    "image/png",
                    "URL",
                    30
                ),
                new Attachment(
                    10008,
                    "file02.gif",
                    "image/gif",
                    "URL2",
                    3056
                )
            ]
        );

        $snapshot = $builder->buildSnapshotFromChangelogEntry(
            $user,
            $current_snapshot,
            $changelog_entry,
            $attachment_collection,
            $jira_field_mapping_collection
        );

        $this->assertSame($user, $snapshot->getUser());
        $this->assertSame(1585141810, $snapshot->getDate()->getTimestamp());
        $this->assertCount(5, $snapshot->getAllFieldsSnapshot());

        $this->assertNull($snapshot->getFieldInSnapshot('environment'));
        $this->assertSame("9", $snapshot->getFieldInSnapshot('customfield_10036')->getValue());
        $this->assertSame(
            [
                ['id' => '10009'],
                ['id' => '10010'],
            ],
            $snapshot->getFieldInSnapshot('customfield_10040')->getValue()
        );

        $this->assertSame(
            "*aaaaaaaaa*",
            $snapshot->getFieldInSnapshot('description')->getValue()
        );

        $this->assertSame(
            "<p>aaaaaaaaa</p>",
            $snapshot->getFieldInSnapshot('description')->getRenderedValue()
        );

        $this->assertSame(
            "*def*",
            $snapshot->getFieldInSnapshot('textfield')->getValue()
        );

        $this->assertNull($snapshot->getFieldInSnapshot('textfield')->getRenderedValue());

        $this->assertSame(
            [10008],
            $snapshot->getFieldInSnapshot('attachment')->getValue()
        );
    }

    private function buildCurrentSnapshot(PFUser $user): Snapshot
    {
        return new Snapshot(
            $user,
            new DateTimeImmutable("2020-03-25T14:10:10.823+0100"),
            [
                new FieldSnapshot(
                    new FieldMapping(
                        "description",
                        "Fdescription",
                        "Description",
                        "text"
                    ),
                    "*aaaaaaaaa*",
                    "<p>aaaaaaaaa</p>"
                ),
                new FieldSnapshot(
                    new FieldMapping(
                        "textfield",
                        "Ftextfield",
                        "Text Field",
                        "text"
                    ),
                    "*text area v2*",
                    "<p>text area v2</p>"
                ),
                new FieldSnapshot(
                    new FieldMapping(
                        "attachment",
                        "Fattachment",
                        "Attachments",
                        "file"
                    ),
                    [
                        [
                            'id' => "10007"
                        ]
                    ],
                    null
                )
            ],
            null
        );
    }

    private function buildChangelogEntry(): ChangelogEntryValueRepresentation
    {
        return ChangelogEntryValueRepresentation::buildFromAPIResponse(
            [
                "id" => "100",
                "created" => "2020-03-25T14:10:10.823+0100",
                "items" => [
                    0 => [
                        "fieldId"    => "customfield_10036",
                        "from"       => null,
                        "fromString" => null,
                        "to"         => null,
                        "toString"   => "9"
                    ],
                    1 => [
                        "fieldId"    => "customfield_10040",
                        "from"       => "[10009]",
                        "fromString" => "mulit1",
                        "to"         => "[10009, 10010]",
                        "toString"   => "mulit1,multi2"
                    ],
                    2 => [
                        "fieldId"    => "environment",
                        "from"       => null,
                        "fromString" => "\r\n----\r\n",
                        "to"         => null,
                        "toString"   => "----\r\n"
                    ],
                    3 => [
                        "fieldId"    => "description",
                        "from"       => null,
                        "fromString" => "aaaaaaaaaaa",
                        "to"         => null,
                        "toString"   => "*aaaaaaaaa*"
                    ],
                    4 => [
                        "fieldId"    => "textfield",
                        "from"       => null,
                        "fromString" => "abc",
                        "to"         => null,
                        "toString"   => "*def*"
                    ],
                    5 => [
                        "fieldId"    => "attachment",
                        "from"       => null,
                        "fromString" => null,
                        "to"         => "10008",
                        "toString"   => "file02.gif"
                    ]
                ]
            ]
        );
    }

    private function buildFieldMappingCollection(): FieldMappingCollection
    {
        $collection = new FieldMappingCollection();
        $collection->addMapping(
            new FieldMapping(
                "customfield_10036",
                "Fcustomfield_10036",
                "Field 01",
                "float"
            )
        );
        $collection->addMapping(
            new FieldMapping(
                "status",
                "Fstatus",
                "status",
                "sb"
            )
        );
        $collection->addMapping(
            new FieldMapping(
                "customfield_10040",
                "Fcustomfield_10040",
                "Field 02",
                "msb"
            ),
        );
        $collection->addMapping(
            new FieldMapping(
                "description",
                "Fdescription",
                "Description",
                "text"
            )
        );
        $collection->addMapping(
            new FieldMapping(
                "textfield",
                "Ftextfield",
                "Text Field",
                "text"
            )
        );
        $collection->addMapping(
            new FieldMapping(
                "attachment",
                "Fattachment",
                "Attachments",
                "file"
            )
        );

        return $collection;
    }
}
