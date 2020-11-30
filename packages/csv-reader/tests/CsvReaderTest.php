<?php

// Copyright (c) Fusonic GmbH. All rights reserved.
// Licensed under the MIT License. See LICENSE file in the project root for license information.

namespace Fusonic\CsvReader\Tests;

use Fusonic\CsvReader\Attributes\TitleMapping;
use Fusonic\CsvReader\CsvReader;
use Fusonic\CsvReader\CsvReaderOptions;
use Fusonic\CsvReader\Tests\data\WithHeadersModel;
use Fusonic\CsvReader\Tests\data\WithoutHeadersModel;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    public function testReadWithHeadersAndTitleMapping()
    {
        $reader = new CsvReader(__DIR__.'/data/with_headers.csv');

        $item = iterator_to_array($reader->readObjects(WithHeadersModel::class))[0];

        $this->assertEquals(1, $item->field);
        $this->assertEquals(1.11, $item->methodBackingField);
    }

    public function testReadWithHeadersAndIndexMapping()
    {
        $reader = new CsvReader(__DIR__.'/data/with_headers.csv');

        $item = iterator_to_array($reader->readObjects(WithoutHeadersModel::class))[0];

        $this->assertEquals(1, $item->field);
        $this->assertEquals(1.11, $item->methodBackingField);
    }

    public function testReadWithoutHeadersAndIndexMapping()
    {
        $options = new CsvReaderOptions();
        $options->hasHeaderRow = false;

        $reader = new CsvReader(__DIR__.'/data/without_headers.csv', $options);

        $item = iterator_to_array($reader->readObjects(WithoutHeadersModel::class))[0];

        $this->assertEquals(1, $item->field);
        $this->assertEquals(1.11, $item->methodBackingField);
    }

    public function testCsvSettingsChangedDelimiterAndEnclosure()
    {
        $options = new CsvReaderOptions();
        $options->delimiter = ';';
        $options->enclosure = '#';

        $reader = new CsvReader(__DIR__.'/data/csv_settings.csv', $options);
        $class = new class() {
            #[TitleMapping('field1')] public int $field1;
            #[TitleMapping('field2')] public string $field2;
        };

        foreach ($reader->readObjects($class::class) as $item) {
            $this->assertEquals(1, $item->field1);
            $this->assertEquals(';', $item->field2);
        }
    }
}
