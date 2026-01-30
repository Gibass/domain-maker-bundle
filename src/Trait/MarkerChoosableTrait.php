<?php

namespace Gibass\DomainMakerBundle\Trait;

use Gibass\DomainMakerBundle\Exception\NoItemToChooseException;

trait MarkerChoosableTrait
{
    private bool $isChosen = false;

    public function isChosen(): bool
    {
        return $this->isChosen;
    }

    public function setChosen(bool $chosen): self
    {
        $this->isChosen = $chosen;
        return $this;
    }

    public function needToCreate(): bool
    {
        return !$this->isChosen();
    }

    /**
     * @throws NoItemToChooseException
     */
    public function loadExistingItems(string $path): array
    {
        $items = $this->manager->listFiles($path);

        if (empty($items)) {
            throw new NoItemToChooseException('There is no item available in:' . $path);
        }

        return $items;
    }
}
