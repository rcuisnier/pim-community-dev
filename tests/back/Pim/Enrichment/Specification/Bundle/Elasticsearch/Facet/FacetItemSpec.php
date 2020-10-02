<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Facet\FacetItem;
use PhpSpec\ObjectBehavior;

class FacetItemSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromArray', [['key' => 'key1', 'doc_count' => 12]]);
    }

    function it_is_a_facet_item()
    {
        $this->beAnInstanceOf(FacetItem::class);
    }

    function it_cannot_be_created_from_array_without_key()
    {
        $this->beConstructedThrough('fromArray', [['doc_count' => 12]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_created_from_array_without_count()
    {
        $this->beConstructedThrough('fromArray', [['key' => 'key1']]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_created_from_array_with_negative_count()
    {
        $this->beConstructedThrough('fromArray', [['key' => 'key1', 'doc_count' => -5]]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}