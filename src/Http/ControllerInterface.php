<?php

namespace Integrity\Trolley\Http;

use Integrity\Trolley\Collections\Collection;
use Integrity\Trolley\Entities\EntityInterface;


interface ControllerInterface
{
    // GET
    public function _index(): Collection;
    // POST
    public function _store(EntityInterface $entity): bool;
    // GET
    public function _show($id): EntityInterface;
    // PUT/PATCH
    public function _update($id, EntityInterface $entity): bool;
    // DELETE
    public function _destroy($id): bool;
}