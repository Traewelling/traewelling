<?php

declare(strict_types=1);

namespace App\Services\PersonalDataSelection\Exporters\Base;

use App\Models\User;
use InvalidArgumentException;
use Spatie\PersonalDataExport\PersonalDataSelection;

class Exporter
{
    private PersonalDataSelection $personalDataSelection;

    private array $exporters = [];

    private User $user;

    public function __construct(
        PersonalDataSelection $personalDataSelection,
        User $user
    ) {
        $this->personalDataSelection = $personalDataSelection;
        $this->user = $user;
    }

    public function export(array $exporters, bool $export = true): void
    {
        $this->exporters = $exporters;
        $this->checkClasses();

        if (!$export) {
            return;
        }

        /** @var AbstractExporter $exporter */
        foreach ($this->exporters as $exporter) {
            $exporter = new $exporter($this->user);
            $this->personalDataSelection->add($exporter->getFileName(), $exporter->getData());
        }
    }

    private function checkClasses(): void
    {
        foreach ($this->exporters as $exporter) {
            if (!class_exists($exporter) || !is_subclass_of($exporter, AbstractExporter::class)) {
                throw new InvalidArgumentException(sprintf('%s is not of type %s', $exporter, AbstractExporter::class));
            }
        }
    }
}
