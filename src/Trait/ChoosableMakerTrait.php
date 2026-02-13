<?php

namespace Gibass\DomainMakerBundle\Trait;

use Gibass\DomainMakerBundle\Exception\NoItemToChooseException;

trait ChoosableMakerTrait
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

    /**
     * @throws NoItemToChooseException
     */
    public function loadExistingItems(): array
    {
        $items = $this->docManager->listFiles($this->getChosenDirectory());

        if (empty($items)) {
            throw new NoItemToChooseException('There is no item available in: ' . $this->getChosenDirectory());
        }

        return $items;
    }
}
