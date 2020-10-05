<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetUpdatedProductIdsQueryInterface;

final class CreateMissingCriteriaEvaluations implements CreateMissingCriteriaEvaluationsInterface
{
    /** @var GetUpdatedProductIdsQueryInterface */
    private $getUpdatedProductIdsQuery;

    /** @var CreateCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    public function __construct(
        GetUpdatedProductIdsQueryInterface $getUpdatedProductIdsQuery,
        CreateCriteriaEvaluations $createProductsCriteriaEvaluations
    ) {
        $this->getUpdatedProductIdsQuery = $getUpdatedProductIdsQuery;
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
    }

    public function forUpdatesSince(\DateTimeImmutable $since, int $batchSize): void
    {
        $this->createForProductsUpdatedSince($since, $batchSize);
        $this->createForProductsImpactedByAttributeGroupActivationUpdatedSince($since, $batchSize);
    }

    private function createForProductsUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {
        foreach ($this->getUpdatedProductIdsQuery->since($updatedSince, $batchSize) as $productIds) {
            $this->createProductsCriteriaEvaluations->createAll($productIds);
        }
    }

    private function createForProductsImpactedByAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince, int $batchSize): void
    {

    }
}
